<?php

use App\Models\Category;
use App\Models\Graduate;
use App\Models\Palmares;
use App\Models\ProfileSearch;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createRhControllerTestUser(string $email = 'rh@example.test', string $roleName = 'rh'): User
{
    $role = Role::firstOrCreate(['name' => $roleName]);

    return User::factory()->create([
        'email' => $email,
        'role_id' => $role->id,
        'status' => true,
    ]);
}

function createRhControllerTestSearch(User $rh, string $graduateName = 'Alice Test'): ProfileSearch
{
    $university = University::create([
        'name' => 'Université de test',
        'address' => 'Kinshasa',
        'status' => 'active',
    ]);

    $palmares = Palmares::create([
        'university_id' => $university->id,
        'file' => 'palmares/test.csv',
        'uploaded_by' => $rh->id,
        'status' => 'approved',
        'uploaded_at' => now(),
    ]);

    $graduate = Graduate::create([
        'university_id' => $university->id,
        'palmares_id' => $palmares->id,
        'matricule' => 'TST-22-0001',
        'name' => $graduateName,
        'faculty' => 'Informatique',
        'diploma_date' => '2022-07-15',
    ]);

    $category = Category::create([
        'name' => 'Pack test',
        'quantity' => 1,
        'status' => 'active',
    ]);

    $ticket = Ticket::create([
        'user_id' => $rh->id,
        'category_id' => $category->id,
        'quantity' => 1,
        'status' => 'approved',
    ]);

    return ProfileSearch::create([
        'rh_user_id' => $rh->id,
        'graduate_id' => $graduate->id,
        'university_id' => $university->id,
        'ticket_id' => $ticket->id,
    ]);
}

it('affiche un profil vérifié avec son bouton d’impression', function () {
    $rh = createRhControllerTestUser();
    $profileSearch = createRhControllerTestSearch($rh);

    $this->actingAs($rh)
        ->get(route('rh.searches.show', $profileSearch))
        ->assertOk()
        ->assertSee('Alice Test')
        ->assertSee('TST-22-0001')
        ->assertSee('Université de test')
        ->assertSee('Informatique')
        ->assertSee('Imprimer le profil')
        ->assertSee('window.print()');
});

it('ouvre directement le dialogue d’impression depuis le tableau RH', function () {
    $rh = createRhControllerTestUser();
    $profileSearch = createRhControllerTestSearch($rh);

    $this->actingAs($rh)
        ->get(route('rh.searches.show', ['profileSearch' => $profileSearch, 'print' => 1]))
        ->assertOk()
        ->assertSee("window.addEventListener('load', () => window.print());", false);
});

it('permet de retrouver les profils consultés depuis l’historique RH', function () {
    $rh = createRhControllerTestUser();
    $profileSearch = createRhControllerTestSearch($rh);

    $this->actingAs($rh)
        ->get(route('rh.index'))
        ->assertOk()
        ->assertSee('Profils consultés')
        ->assertSee('Voir')
        ->assertSee(route('rh.searches.show', $profileSearch))
        ->assertSee(route('rh.searches.show', ['profileSearch' => $profileSearch, 'print' => 1]));
});

it('échappe le nom du candidat affiché dans le profil', function () {
    $rh = createRhControllerTestUser();
    $profileSearch = createRhControllerTestSearch($rh, '<script>alert(1)</script>');

    $this->actingAs($rh)
        ->get(route('rh.searches.show', $profileSearch))
        ->assertOk()
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSee('<script>alert(1)</script>', false);
});

it('masque le profil d’un autre compte RH', function () {
    $owner = createRhControllerTestUser('owner@example.test');
    $otherRh = createRhControllerTestUser('other@example.test');
    $profileSearch = createRhControllerTestSearch($owner);

    $this->actingAs($otherRh)
        ->get(route('rh.searches.show', $profileSearch))
        ->assertNotFound();
});

it('refuse l’accès au profil aux rôles autres que RH', function () {
    $rh = createRhControllerTestUser();
    $admin = createRhControllerTestUser('admin@example.test', 'admin');
    $profileSearch = createRhControllerTestSearch($rh);

    $this->actingAs($admin)
        ->get(route('rh.searches.show', $profileSearch))
        ->assertForbidden();
});
