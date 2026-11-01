<?php

namespace App\Http\Services\V1;

use App\Models\Graduate;
use App\Models\Palmares;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PalmaresService
{
    private const MAX_IMPORT_ROWS = 500;

    private const MAX_XLSX_XML_SIZE = 2_097_152;

    private const XLSX_MAIN_NAMESPACE = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const XLSX_RELATIONSHIP_NAMESPACE = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    private const XLSX_PACKAGE_NAMESPACE = 'http://schemas.openxmlformats.org/package/2006/relationships';

    public function getAllPalmares()
    {
        return Palmares::all();
    }

    public function getPalmaresById(int $id): Palmares
    {
        $palmares = Palmares::find($id);

        if (! $palmares) {
            throw new RuntimeException('Palmares not found');
        }

        return $palmares;
    }

    public function createPalmares(array $data): Palmares
    {
        $palmares = Palmares::create($data);

        if (! $palmares) {
            throw new RuntimeException('Failed to create Palmares');
        }

        return $palmares;
    }

    public function updatePalmares(int $id, array $data): Palmares
    {
        $palmares = $this->getPalmaresById($id);
        $palmares->update($data);

        return $palmares->fresh();
    }

    public function deletePalmares(int $id): Palmares
    {
        $palmares = $this->getPalmaresById($id);
        $palmares->delete();

        return $palmares;
    }

    public function createPendingImport(
        UploadedFile $file,
        int $universityId,
        int $uploadedBy,
        string $universityName,
    ): Palmares {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        $rows = $this->parseRows($file->getPathname(), $extension);

        if ($rows === []) {
            throw new RuntimeException('Le fichier doit contenir au moins une ligne de diplômé.');
        }

        if (count($rows) > self::MAX_IMPORT_ROWS) {
            throw new RuntimeException('Un palmarès ne peut pas contenir plus de 500 lignes de diplômés.');
        }

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $values = array_map('trim', array_values($row));

            if (in_array('', $values, true)) {
                throw new RuntimeException("Ligne {$line} incomplète : matricule, nom complet, faculté et date de diplôme sont obligatoires.");
            }

            if ($this->normalizeDate($row['diploma_date']) === null) {
                throw new RuntimeException("Ligne {$line} : date de diplôme invalide (format attendu : YYYY-MM-DD).");
            }
        }

        $directory = 'universities/'.Str::slug($universityName);
        $filename = now()->format('YmdHis').'_'.Str::uuid().'.'.$extension;
        $path = $file->storeAs($directory, $filename, 'local');

        if (! is_string($path)) {
            throw new RuntimeException('Impossible d’enregistrer le fichier du palmarès.');
        }

        try {
            return DB::transaction(fn (): Palmares => $this->createPalmares([
                'university_id' => $universityId,
                'file' => $path,
                'uploaded_by' => $uploadedBy,
                'status' => 'pending',
                'uploaded_at' => now(),
            ]));
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }

    /**
     * @return array{palmares: Palmares, imported: int, skipped: int, errors: list<string>}
     */
    public function processPendingImport(Palmares $palmares): array
    {
        $palmares->refresh();

        if ($palmares->status === 'approved') {
            return [
                'palmares' => $palmares->load('graduates'),
                'imported' => 0,
                'skipped' => 0,
                'errors' => [],
            ];
        }

        $fullPath = Storage::disk('local')->path($palmares->file);
        $extension = strtolower(pathinfo($palmares->file, PATHINFO_EXTENSION));
        $rows = $this->parseRows($fullPath, $extension);

        if ($rows === [] || count($rows) > self::MAX_IMPORT_ROWS) {
            throw new RuntimeException('Le palmarès ne respecte pas la limite de 500 lignes de diplômés.');
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        $palmares = DB::transaction(function () use ($palmares, $rows, &$imported, &$skipped, &$errors): Palmares {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $matricule = trim((string) ($row['matricule'] ?? ''));
                $fullName = trim((string) ($row['full_name'] ?? ''));
                $faculty = trim((string) ($row['faculty'] ?? ''));
                $diplomaDate = trim((string) ($row['diploma_date'] ?? ''));

                if ($matricule === '' && $fullName === '' && $faculty === '' && $diplomaDate === '') {
                    continue;
                }

                if ($matricule === '' || $fullName === '' || $faculty === '' || $diplomaDate === '') {
                    $skipped++;
                    $errors[] = "Ligne {$line} : champs requis manquants (matricule, full_name, faculty, diploma_date).";

                    continue;
                }

                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $diplomaDate)) {
                    $normalized = $this->normalizeDate($diplomaDate);
                    if ($normalized === null) {
                        $skipped++;
                        $errors[] = "Ligne {$line} : date invalide « {$diplomaDate} » (attendu YYYY-MM-DD).";

                        continue;
                    }
                    $diplomaDate = $normalized;
                }

                if (Graduate::where('matricule', $matricule)->exists()) {
                    $skipped++;
                    $errors[] = "Ligne {$line} : matricule {$matricule} déjà présent.";

                    continue;
                }

                Graduate::create([
                    'university_id' => $palmares->university_id,
                    'palmares_id' => $palmares->id,
                    'matricule' => $matricule,
                    'name' => $fullName,
                    'faculty' => $faculty,
                    'diploma_date' => $diplomaDate,
                ]);
                $imported++;
            }

            $palmares->update([
                'status' => 'approved',
                'verified_at' => now(),
                'verified_by' => $palmares->uploaded_by,
            ]);

            return $palmares;
        });

        return [
            'palmares' => $palmares->fresh('graduates'),
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * @return list<array{matricule: string, full_name: string, faculty: string, diploma_date: string}>
     */
    public function parseRows(string $fullPath, string $extension): array
    {
        return match ($extension) {
            'csv', 'txt' => $this->parseCsv($fullPath),
            'xlsx' => $this->parseXlsx($fullPath),
            'xls' => throw new RuntimeException('Le format .xls (Excel 97-2003) n’est pas supporté. Exportez en .xlsx ou .csv.'),
            default => throw new RuntimeException("Extension non supportée : .{$extension}. Utilisez .csv ou .xlsx."),
        };
    }

    /**
     * @return list<array{matricule: string, full_name: string, faculty: string, diploma_date: string}>
     */
    private function parseCsv(string $fullPath): array
    {
        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Impossible de lire le fichier CSV.');
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        try {
            $header = fgetcsv($handle, 0, ',');
            if ($header === false || $header === [null] || $header === []) {
                rewind($handle);
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                $header = fgetcsv($handle, 0, ';');
                $delimiter = ';';
            } else {
                $delimiter = ',';
                if (count($header) === 1 && str_contains((string) $header[0], ';')) {
                    rewind($handle);
                    $bom = fread($handle, 3);
                    if ($bom !== "\xEF\xBB\xBF") {
                        rewind($handle);
                    }
                    $header = fgetcsv($handle, 0, ';');
                    $delimiter = ';';
                }
            }

            if ($header === false) {
                throw new RuntimeException('En-tête CSV introuvable.');
            }

            $map = $this->mapHeader($header);
            $rows = [];

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }
                if (count($rows) >= self::MAX_IMPORT_ROWS) {
                    throw new RuntimeException('Un palmarès ne peut pas contenir plus de 500 lignes de diplômés.');
                }
                $rows[] = $this->rowFromMap($row, $map);
            }

            return $rows;
        } finally {
            fclose($handle);
        }

    }

    /**
     * Lecture basique d’un .xlsx (1ère feuille) sans dépendance externe.
     *
     * @return list<array{matricule: string, full_name: string, faculty: string, diploma_date: string}>
     */
    private function parseXlsx(string $fullPath): array
    {
        $zip = new \ZipArchive;
        if ($zip->open($fullPath) !== true) {
            throw new RuntimeException('Fichier Excel .xlsx illisible.');
        }

        $workbookStat = $zip->statName('xl/workbook.xml');
        $relationshipsStat = $zip->statName('xl/_rels/workbook.xml.rels');
        $sharedStringsStat = $zip->statName('xl/sharedStrings.xml');
        if ($workbookStat === false || $relationshipsStat === false ||
            $workbookStat['size'] > self::MAX_XLSX_XML_SIZE ||
            $relationshipsStat['size'] > self::MAX_XLSX_XML_SIZE ||
            ($sharedStringsStat !== false && $sharedStringsStat['size'] > self::MAX_XLSX_XML_SIZE)) {
            $zip->close();
            throw new RuntimeException('Le fichier Excel est incomplet ou son contenu est trop volumineux.');
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationshipsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($workbookXml === false || $relationshipsXml === false) {
            $zip->close();
            throw new RuntimeException('Structure du classeur Excel invalide.');
        }

        $workbook = $this->parseXlsxXml($workbookXml);
        $relationships = $this->parseXlsxXml($relationshipsXml);
        if ($workbook === false || $relationships === false) {
            $zip->close();
            throw new RuntimeException('Impossible de lire la structure du classeur Excel.');
        }

        $sheetNodes = $workbook->children(self::XLSX_MAIN_NAMESPACE)->sheets
            ->children(self::XLSX_MAIN_NAMESPACE)->sheet;
        $firstSheet = $sheetNodes[0] ?? null;
        $relationshipId = $firstSheet === null
            ? ''
            : (string) $firstSheet->attributes(self::XLSX_RELATIONSHIP_NAMESPACE)['id'];

        $sheetTarget = null;
        foreach ($relationships->children(self::XLSX_PACKAGE_NAMESPACE)->Relationship as $relationship) {
            $relationshipAttributes = $relationship->attributes();
            if ((string) $relationshipAttributes['Id'] === $relationshipId) {
                $sheetTarget = (string) $relationshipAttributes['Target'];
                break;
            }
        }

        if ($sheetTarget === null || str_contains($sheetTarget, '..')) {
            $zip->close();
            throw new RuntimeException('Aucune feuille de calcul valide n’a été trouvée dans le fichier Excel.');
        }

        $sheetPath = str_starts_with($sheetTarget, '/')
            ? ltrim($sheetTarget, '/')
            : 'xl/'.ltrim($sheetTarget, '/');
        $sheetStat = $zip->statName($sheetPath);
        if ($sheetStat === false || $sheetStat['size'] > self::MAX_XLSX_XML_SIZE) {
            $zip->close();
            throw new RuntimeException('Feuille Excel introuvable ou trop volumineuse.');
        }

        if ($sharedXml !== false) {
            $sharedStringXml = $this->parseXlsxXml($sharedXml);
            if ($sharedStringXml === false) {
                $zip->close();
                throw new RuntimeException('Les chaînes de caractères du fichier Excel sont illisibles.');
            }

            foreach ($sharedStringXml->children(self::XLSX_MAIN_NAMESPACE)->si as $sharedString) {
                $sharedStrings[] = $this->xlsxText($sharedString);
            }
        }

        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Feuille Excel introuvable.');
        }

        $sheet = $this->parseXlsxXml($sheetXml);
        if ($sheet === false) {
            throw new RuntimeException('Impossible de parser la feuille Excel.');
        }

        $matrix = [];
        $sheetData = $sheet->children(self::XLSX_MAIN_NAMESPACE)->sheetData;
        if ($sheetData === null) {
            throw new RuntimeException('La feuille Excel ne contient aucune table de données lisible.');
        }

        foreach ($sheetData->children(self::XLSX_MAIN_NAMESPACE)->row as $row) {
            $rowAttributes = $row->attributes();
            $rIndex = (int) $rowAttributes['r'] - 1;
            foreach ($row->children(self::XLSX_MAIN_NAMESPACE)->c as $cell) {
                $cellAttributes = $cell->attributes();
                $ref = (string) $cellAttributes['r'];
                if (! preg_match('/^([A-Z]+)(\d+)$/', $ref, $m)) {
                    continue;
                }
                $cIndex = $this->columnIndex($m[1]);
                $type = (string) ($cellAttributes['t'] ?? '');
                $cellData = $cell->children(self::XLSX_MAIN_NAMESPACE);
                $value = $type === 'inlineStr' && isset($cellData->is)
                    ? $this->xlsxText($cellData->is)
                    : (isset($cellData->v) ? (string) $cellData->v : '');
                if ($type === 's' && $value !== '') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }
                $matrix[$rIndex][$cIndex] = $value;
            }
        }

        if ($matrix === []) {
            return [];
        }

        ksort($matrix);
        $firstKey = array_key_first($matrix);
        $headerRow = $matrix[$firstKey] ?? [];
        ksort($headerRow);
        $map = $this->mapHeader($headerRow);

        $rows = [];
        foreach ($matrix as $rIndex => $cols) {
            if ($rIndex === $firstKey) {
                continue;
            }
            ksort($cols);
            if ($this->isEmptyRow($cols)) {
                continue;
            }
            if (count($rows) >= self::MAX_IMPORT_ROWS) {
                throw new RuntimeException('Un palmarès ne peut pas contenir plus de 500 lignes de diplômés.');
            }
            $rows[] = $this->rowFromMap($cols, $map);
        }

        return $rows;
    }

    private function xlsxText(\SimpleXMLElement $element): string
    {
        $mainChildren = $element->children(self::XLSX_MAIN_NAMESPACE);
        if (isset($mainChildren->t)) {
            return (string) $mainChildren->t;
        }

        $text = '';
        foreach ($mainChildren->r as $run) {
            $text .= (string) $run->children(self::XLSX_MAIN_NAMESPACE)->t;
        }

        return $text;
    }

    private function parseXlsxXml(string $xml): \SimpleXMLElement|false
    {
        if (stripos($xml, '<!DOCTYPE') !== false) {
            return false;
        }

        $previousErrorSetting = libxml_use_internal_errors(true);

        try {
            return simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorSetting);
        }
    }

    /**
     * @param  array<int, string|null>  $header
     * @return array{matricule: int, full_name: int, faculty: int, diploma_date: int}
     */
    private function mapHeader(array $header): array
    {
        $normalized = [];
        foreach ($header as $i => $col) {
            $key = Str::ascii(strtolower(trim((string) $col)));
            $key = str_replace([' ', '-'], '_', $key);
            $normalized[$key] = $i;
        }

        $aliases = [
            'matricule' => ['matricule', 'matrikule', 'student_id', 'id'],
            'full_name' => ['full_name', 'fullname', 'fullname_complet', 'student_name', 'fullname_etudiant', 'nom_complet', 'nom_etudiant', 'nom_complet_etudiant'],
            'faculty' => ['faculty', 'faculte', 'faculté', 'filiere', 'filière'],
            'diploma_date' => ['diploma_date', 'date_diplome', 'date_obtention', 'date_de_diplome', 'date'],
        ];

        $map = [];
        foreach ($aliases as $field => $names) {
            $found = null;
            foreach ($names as $name) {
                if (array_key_exists($name, $normalized)) {
                    $found = $normalized[$name];
                    break;
                }
            }
            if ($found === null) {
                throw new RuntimeException('Format invalide. Les colonnes requises sont : matricule, full_name, faculty, diploma_date.');
            }

            $map[$field] = $found;
        }

        return $map;
    }

    /**
     * @param  array<int, string|null>  $row
     * @param  array{matricule: int, full_name: int, faculty: int, diploma_date: int}  $map
     * @return array{matricule: string, full_name: string, faculty: string, diploma_date: string}
     */
    private function rowFromMap(array $row, array $map): array
    {
        return [
            'matricule' => (string) ($row[$map['matricule']] ?? ''),
            'full_name' => (string) ($row[$map['full_name']] ?? ''),
            'faculty' => (string) ($row[$map['faculty']] ?? ''),
            'diploma_date' => (string) ($row[$map['diploma_date']] ?? ''),
        ];
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function columnIndex(string $letters): int
    {
        $index = 0;
        $letters = strtoupper($letters);
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);

        // Serial Excel (jours depuis 1899-12-30)
        if (is_numeric($value)) {
            $serial = (int) $value;
            if ($serial > 20000 && $serial < 80000) {
                $timestamp = ($serial - 25569) * 86400;

                return gmdate('Y-m-d', $timestamp);
            }
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y/m/d', 'd.m.Y'] as $format) {
            $dt = \DateTime::createFromFormat($format, $value);
            $errors = \DateTime::getLastErrors();
            if ($dt instanceof \DateTime && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $dt->format('Y-m-d');
            }
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
            return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]) ? $value : null;
        }

        return null;
    }
}
