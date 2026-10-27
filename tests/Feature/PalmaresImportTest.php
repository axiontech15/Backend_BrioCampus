<?php

use App\Models\Graduate;
use App\Models\Palmares;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

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

it('importe un CSV, enregistre le fichier et crée les diplômés', function () {
    $csv = "matricule,full_name,faculty,diploma_date\n".
        "TST-01-0001,Alice Test,Informatique,2020-07-15\n".
        "TST-01-0002,Bob Test,Droit,2021-07-20\n";

    $file = UploadedFile::fake()->createWithContent('palmares.csv', $csv);

    $this->actingAs($this->agent)
        ->post(route('university.palmares.import'), ['file' => $file])
        ->assertRedirect();

    expect(Palmares::count())->toBe(1)
        ->and(Graduate::count())->toBe(2);

    $palmares = Palmares::first();
    Storage::disk('public')->assertExists($palmares->file);

    expect($palmares->university_id)->toBe($this->university->id)
        ->and($palmares->uploaded_by)->toBe($this->agent->id)
        ->and(Graduate::where('matricule', 'TST-01-0001')->exists())->toBeTrue();
});

it('ignore les matricules déjà présents sans casser l import', function () {
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

    expect(Graduate::where('matricule', 'TST-NEW-1')->exists())->toBeTrue()
        ->and(Graduate::count())->toBe(2);
});
