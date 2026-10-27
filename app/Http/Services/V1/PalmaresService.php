<?php

namespace App\Http\Services\V1;

use App\Models\Graduate;
use App\Models\Palmares;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PalmaresService
{
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

    /**
     * Stocke le fichier, crée l'enregistrement palmarès, puis importe les diplômés.
     *
     * @return array{palmares: Palmares, imported: int, skipped: int, errors: list<string>}
     */
    public function importUploadedFile(
        UploadedFile $file,
        int $universityId,
        int $uploadedBy,
        string $status = 'pending',
    ): array {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        $path = $file->storeAs(
            'palmares',
            now()->format('YmdHis').'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName()),
            'public'
        );

        $fullPath = Storage::disk('public')->path($path);
        $rows = $this->parseRows($fullPath, $extension);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        $palmares = DB::transaction(function () use ($universityId, $uploadedBy, $path, $status, $rows, &$imported, &$skipped, &$errors) {
            $palmares = $this->createPalmares([
                'university_id' => $universityId,
                'file' => $path,
                'uploaded_by' => $uploadedBy,
                'status' => $status,
                'uploaded_at' => now(),
                'verified_at' => $status === 'approved' ? now() : null,
                'verified_by' => $status === 'approved' ? $uploadedBy : null,
            ]);

            foreach ($rows as $index => $row) {
                $line = $index + 2; // +1 header, +1 index 0-based
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
                    'university_id' => $universityId,
                    'palmares_id' => $palmares->id,
                    'matricule' => $matricule,
                    'name' => $fullName,
                    'faculty' => $faculty,
                    'diploma_date' => $diplomaDate,
                ]);
                $imported++;
            }

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

        $header = fgetcsv($handle, 0, ',');
        if ($header === false || $header === [null] || $header === []) {
            // Essai séparateur point-virgule
            rewind($handle);
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            $header = fgetcsv($handle, 0, ';');
            $delimiter = ';';
        } else {
            $delimiter = ',';
            // Si une seule colonne contenant des ;, reparser
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
            fclose($handle);
            throw new RuntimeException('En-tête CSV introuvable.');
        }

        $map = $this->mapHeader($header);
        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $rows[] = $this->rowFromMap($row, $map);
        }

        fclose($handle);

        return $rows;
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

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sx = @simplexml_load_string($sharedXml);
            if ($sx !== false) {
                foreach ($sx->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } else {
                        $text = '';
                        foreach ($si->r as $run) {
                            $text .= (string) $run->t;
                        }
                        $sharedStrings[] = $text;
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Feuille Excel introuvable (sheet1).');
        }

        $sheet = @simplexml_load_string($sheetXml);
        if ($sheet === false) {
            throw new RuntimeException('Impossible de parser la feuille Excel.');
        }

        $matrix = [];
        foreach ($sheet->sheetData->row as $row) {
            $rIndex = (int) $row['r'] - 1;
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                if (! preg_match('/^([A-Z]+)(\d+)$/', $ref, $m)) {
                    continue;
                }
                $cIndex = $this->columnIndex($m[1]);
                $type = (string) ($cell['t'] ?? '');
                $value = isset($cell->v) ? (string) $cell->v : '';
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
        $header = [];
        $maxCol = $headerRow === [] ? 3 : max(array_keys($headerRow));
        for ($i = 0; $i <= max($maxCol, 3); $i++) {
            $header[] = $headerRow[$i] ?? '';
        }
        $map = $this->mapHeader($header);

        $rows = [];
        foreach ($matrix as $rIndex => $cols) {
            if ($rIndex === $firstKey) {
                continue;
            }
            ksort($cols);
            $row = [];
            for ($i = 0; $i <= max($maxCol, 3); $i++) {
                $row[] = $cols[$i] ?? '';
            }
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $rows[] = $this->rowFromMap($row, $map);
        }

        return $rows;
    }

    /**
     * @param  list<string|null>  $header
     * @return array{matricule: int, full_name: int, faculty: int, diploma_date: int}
     */
    private function mapHeader(array $header): array
    {
        $normalized = [];
        foreach ($header as $i => $col) {
            $key = strtolower(trim((string) $col));
            $key = str_replace([' ', '-'], '_', $key);
            $normalized[$key] = $i;
        }

        $aliases = [
            'matricule' => ['matricule', 'matrikule', 'student_id', 'id'],
            'full_name' => ['full_name', 'fullname', 'fullname_complet', 'student_name', 'fullname_etudiant'],
            'faculty' => ['faculty', 'faculte', 'faculté', 'filiere', 'filière'],
            'diploma_date' => ['diploma_date', 'date_diplome', 'date_obtention', 'date'],
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
            $map[$field] = $found ?? match ($field) {
                'matricule' => 0,
                'full_name' => 1,
                'faculty' => 2,
                'diploma_date' => 3,
            };
        }

        return $map;
    }

    /**
     * @param  list<string|null>  $row
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
     * @param  list<string|null>  $row
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
            if ($dt instanceof \DateTime) {
                return $dt->format('Y-m-d');
            }
        }

        $ts = strtotime($value);

        return $ts ? date('Y-m-d', $ts) : null;
    }
}
