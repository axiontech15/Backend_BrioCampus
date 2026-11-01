<?php

use App\Http\Services\V1\LogService;
use App\Http\Services\V1\PalmaresService;
use App\Jobs\ProcessPalmaresImport;
use App\Models\Graduate;
use App\Models\Palmares;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');

    Role::create(['name' => 'admin']);
    $uniRole = Role::create(['name' => 'university']);
    Role::create(['name' => 'rh']);

    $this->agent = User::factory()->create([
        'role_id' => $uniRole->id,
        'status' => true,
        'password' => 'demo1234',
    ]);

    $this->university = University::create([
        'agent_id' => $this->agent->id,
        'name' => 'Université Test',
        'address' => 'Kinshasa',
        'status' => 'active',
    ]);
});

function createInlineStringXlsxUpload(): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'palmares-');
    if ($path === false) {
        throw new RuntimeException('Impossible de créer le fichier XLSX de test.');
    }

    $zip = new ZipArchive;
    if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
        unlink($path);
        throw new RuntimeException('Impossible de créer l’archive XLSX de test.');
    }

    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?>'.
        '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '.
        'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'.
        '<sheets><sheet name="Registre" sheetId="1" r:id="rId3"/></sheets></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?>'.
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '.
        'Target="worksheets/registre.xml"/></Relationships>');

    $headers = ['matricule', 'Nom complet', 'Faculté', 'Date diplôme'];
    $values = ['TST-XLSX-1', 'Alice XLSX', 'Informatique', '2020-07-15'];
    $sheetRows = '<row r="1">';
    foreach ($headers as $index => $header) {
        $column = chr(65 + $index);
        $sheetRows .= "<c r=\"{$column}1\" t=\"inlineStr\"><is><t>{$header}</t></is></c>";
    }
    $sheetRows .= '</row><row r="2">';
    foreach ($values as $index => $value) {
        $column = chr(65 + $index);
        $sheetRows .= "<c r=\"{$column}2\" t=\"inlineStr\"><is><t>{$value}</t></is></c>";
    }
    $sheetRows .= '</row>';

    $zip->addFromString(
        'xl/worksheets/registre.xml',
        '<?xml version="1.0" encoding="UTF-8"?>'.
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'.
        "<sheetData>{$sheetRows}</sheetData></worksheet>",
    );
    $zip->close();

    $contents = file_get_contents($path);
    unlink($path);
    if ($contents === false) {
        throw new RuntimeException('Impossible de lire le fichier XLSX de test.');
    }

    return UploadedFile::fake()->createWithContent('palmares.xlsx', $contents);
}

it('importe un CSV, enregistre le fichier et crée les diplômés', function () {
    Queue::fake([ProcessPalmaresImport::class]);

    $csv = "matricule,full_name,faculty,diploma_date\n".
        "TST-01-0001,Alice Test,Informatique,2020-07-15\n".
        "TST-01-0002,Bob Test,Droit,2021-07-20\n";

    $file = UploadedFile::fake()->createWithContent('palmares.csv', $csv);

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Palmares::count())->toBe(1)
        ->and(Graduate::count())->toBe(0);

    $palmares = Palmares::first();
    Storage::disk('local')->assertExists($palmares->file);

    expect($palmares->university_id)->toBe($this->university->id)
        ->and($palmares->uploaded_by)->toBe($this->agent->id)
        ->and($palmares->status)->toBe('pending')
        ->and($palmares->file)->toContain('universities/universite-test/');

    $this->get(route('university.index'))
        ->assertOk()
        ->assertSee('jusqu’à 500 diplômés')
        ->assertSee('En attente')
        ->assertSee('Ajouter un diplômé manuellement');

    $this->get(route('university.palmares.status'))
        ->assertOk()
        ->assertJson(['pending' => 1]);

    Queue::assertPushed(ProcessPalmaresImport::class, fn (ProcessPalmaresImport $job): bool => $job->palmaresId === $palmares->id);

    (new ProcessPalmaresImport($palmares->id))->handle(
        app(PalmaresService::class),
        app(LogService::class),
    );

    expect($palmares->fresh()->status)->toBe('approved')
        ->and(Graduate::count())->toBe(2)
        ->and(Graduate::where('matricule', 'TST-01-0001')->exists())->toBeTrue();

    $this->get(route('university.palmares.status'))
        ->assertOk()
        ->assertJson(['pending' => 0]);

    $this->assertDatabaseHas('logs', [
        'user_id' => $this->agent->id,
        'action' => 'IMPORT_COMPLETED',
    ]);
});

it('lit un XLSX avec des cellules inline et une feuille nommée autrement que sheet1', function () {
    Queue::fake([ProcessPalmaresImport::class]);

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => createInlineStringXlsxUpload()])
        ->assertRedirect()
        ->assertSessionHas('success');

    $palmares = Palmares::firstOrFail();
    (new ProcessPalmaresImport($palmares->id))->handle(
        app(PalmaresService::class),
        app(LogService::class),
    );

    expect(Graduate::where('matricule', 'TST-XLSX-1')->value('name'))->toBe('Alice XLSX')
        ->and(Graduate::where('matricule', 'TST-XLSX-1')->value('faculty'))->toBe('Informatique')
        ->and($palmares->fresh()->status)->toBe('approved');
});

it('ignore les matricules déjà présents sans casser l import', function () {
    Queue::fake([ProcessPalmaresImport::class]);

    Graduate::create([
        'university_id' => $this->university->id,
        'palmares_id' => Palmares::create([
            'university_id' => $this->university->id,
            'file' => 'palmares/old.csv',
            'uploaded_by' => $this->agent->id,
            'status' => 'approved',
            'uploaded_at' => now(),
        ])->id,
        'matricule' => 'TST-DUP-1',
        'name' => 'Existant',
        'faculty' => 'Gestion',
        'diploma_date' => '2019-01-01',
    ]);

    $csv = "matricule,full_name,faculty,diploma_date\n".
        "TST-DUP-1,Existant,Gestion,2019-01-01\n".
        "TST-NEW-1,Nouveau,Droit,2022-06-01\n";

    $file = UploadedFile::fake()->createWithContent('palmares.csv', $csv);

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Graduate::where('matricule', 'TST-NEW-1')->exists())->toBeFalse()
        ->and(Graduate::count())->toBe(1);

    $palmares = Palmares::where('status', 'pending')->firstOrFail();
    (new ProcessPalmaresImport($palmares->id))->handle(
        app(PalmaresService::class),
        app(LogService::class),
    );

    expect(Graduate::where('matricule', 'TST-NEW-1')->exists())->toBeTrue()
        ->and(Graduate::count())->toBe(2);
});

it('refuse un palmarès de plus de 500 diplômés avant de le mettre en file', function () {
    Queue::fake([ProcessPalmaresImport::class]);

    $rows = ['matricule,full_name,faculty,diploma_date'];
    for ($index = 1; $index <= 501; $index++) {
        $rows[] = 'TST-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT).',Test Student,Informatique,2020-07-15';
    }

    $file = UploadedFile::fake()->createWithContent('palmares.csv', implode("\n", $rows));

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertRedirect()
        ->assertSessionHasErrors(['file' => 'Un palmarès ne peut pas contenir plus de 500 lignes de diplômés.']);

    expect(Palmares::count())->toBe(0);
    Queue::assertNotPushed(ProcessPalmaresImport::class);
});

it('met en file un palmarès contenant exactement 500 diplômés', function () {
    Queue::fake([ProcessPalmaresImport::class]);

    $rows = ['matricule,full_name,faculty,diploma_date'];
    for ($index = 1; $index <= 500; $index++) {
        $rows[] = 'TST-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT).',Test Student,Informatique,2020-07-15';
    }

    $file = UploadedFile::fake()->createWithContent('palmares.csv', implode("\n", $rows));

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Palmares::count())->toBe(1);
    Queue::assertPushed(ProcessPalmaresImport::class);
});

it('refuse un fichier qui ne contient pas toutes les colonnes requises', function () {
    Queue::fake([ProcessPalmaresImport::class]);

    $file = UploadedFile::fake()->createWithContent(
        'palmares.csv',
        "matricule,full_name,faculty\nTST-01-0001,Alice Test,Informatique\n",
    );

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertRedirect()
        ->assertSessionHasErrors('file');

    expect(Palmares::count())->toBe(0);
    Queue::assertNotPushed(ProcessPalmaresImport::class);
});

it('refuse une ligne de diplômé incomplète avant de la mettre en file', function () {
    Queue::fake([ProcessPalmaresImport::class]);

    $file = UploadedFile::fake()->createWithContent(
        'palmares.csv',
        "matricule,full_name,faculty,diploma_date\nTST-01-0001,Alice Test,,2020-07-15\n",
    );

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertRedirect()
        ->assertSessionHasErrors('file');

    expect(Palmares::count())->toBe(0);
    Queue::assertNotPushed(ProcessPalmaresImport::class);
});

it('refuse une date de diplôme invalide avant de la mettre en file', function () {
    Queue::fake([ProcessPalmaresImport::class]);

    $file = UploadedFile::fake()->createWithContent(
        'palmares.csv',
        "matricule,full_name,faculty,diploma_date\nTST-01-0001,Alice Test,Informatique,2020-02-31\n",
    );

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertRedirect()
        ->assertSessionHasErrors('file');

    expect(Palmares::count())->toBe(0);
    Queue::assertNotPushed(ProcessPalmaresImport::class);
});

it('marque un import en échec et conserve l’événement dans les journaux du compte', function () {
    Queue::fake([ProcessPalmaresImport::class]);

    $file = UploadedFile::fake()->createWithContent(
        'palmares.csv',
        "matricule,full_name,faculty,diploma_date\nTST-01-0001,Alice Test,Informatique,2020-07-15\n",
    );

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertRedirect();

    $palmares = Palmares::firstOrFail();
    (new ProcessPalmaresImport($palmares->id))->failed(new RuntimeException('Test failure'));

    expect($palmares->fresh()->status)->toBe('rejected');
    $this->assertDatabaseHas('logs', [
        'user_id' => $this->agent->id,
        'action' => 'IMPORT_FAILED',
    ]);
});

it('interdit à un compte RH de téléverser un palmarès', function () {
    Queue::fake([ProcessPalmaresImport::class]);
    $rh = User::factory()->create([
        'role_id' => Role::where('name', 'rh')->value('id'),
        'status' => true,
    ]);

    $file = UploadedFile::fake()->createWithContent(
        'palmares.csv',
        "matricule,full_name,faculty,diploma_date\nTST-01-0001,Alice Test,Informatique,2020-07-15\n",
    );

    $this->actingAs($rh)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertForbidden();

    expect(Palmares::count())->toBe(0);
    Queue::assertNotPushed(ProcessPalmaresImport::class);
});

it('ajoute un diplômé manuellement et le rend disponible dans le registre RH', function () {
    $this->actingAs($this->agent)
        ->post(route('university.graduates.store'), [
            'matricule' => 'TST-MANUAL-1',
            'full_name' => 'Étudiant manuel',
            'faculty' => 'Gestion',
            'diploma_date' => '2021-06-30',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $graduate = Graduate::where('matricule', 'TST-MANUAL-1')->firstOrFail();
    $palmares = $graduate->palmares;

    expect($graduate->name)->toBe('Étudiant manuel')
        ->and($graduate->faculty)->toBe('Gestion')
        ->and($graduate->university_id)->toBe($this->university->id)
        ->and($palmares->file)->toBeNull()
        ->and($palmares->status)->toBe('approved');

    $this->assertDatabaseHas('logs', [
        'user_id' => $this->agent->id,
        'action' => 'MANUAL_GRADUATE_CREATE',
    ]);
});

it('interdit un ajout manuel à un compte sans rôle université', function () {
    $rh = User::factory()->create([
        'role_id' => Role::where('name', 'rh')->value('id'),
        'status' => true,
    ]);

    $this->actingAs($rh)
        ->post(route('university.graduates.store'), [
            'matricule' => 'TST-MANUAL-FORBIDDEN',
            'full_name' => 'Étudiant interdit',
            'faculty' => 'Gestion',
            'diploma_date' => '2021-06-30',
        ])
        ->assertForbidden();

    expect(Graduate::where('matricule', 'TST-MANUAL-FORBIDDEN')->exists())->toBeFalse();
});

it('refuse un ajout manuel si le matricule est déjà enregistré', function () {
    Graduate::create([
        'university_id' => $this->university->id,
        'palmares_id' => Palmares::create([
            'university_id' => $this->university->id,
            'file' => 'universities/universite-test/old.csv',
            'uploaded_by' => $this->agent->id,
            'status' => 'approved',
        ])->id,
        'matricule' => 'TST-MANUAL-DUP',
        'name' => 'Déjà présent',
        'faculty' => 'Gestion',
        'diploma_date' => '2020-01-01',
    ]);

    $this->actingAs($this->agent)
        ->post(route('university.graduates.store'), [
            'matricule' => 'TST-MANUAL-DUP',
            'full_name' => 'Étudiant doublon',
            'faculty' => 'Gestion',
            'diploma_date' => '2021-06-30',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('matricule');

    expect(Graduate::where('matricule', 'TST-MANUAL-DUP')->count())->toBe(1);
});
