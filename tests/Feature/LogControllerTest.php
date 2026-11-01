<?php

use App\Models\Log;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirige les visiteurs vers la connexion', function () {
    $this->get(route('logs.index'))
        ->assertRedirect(route('auth.login'));
});

it('affiche uniquement les journaux du compte connecté', function () {
    $role = Role::create(['name' => 'rh']);
    $user = User::factory()->create(['role_id' => $role->id, 'status' => true]);
    $otherUser = User::factory()->create(['role_id' => $role->id, 'status' => true]);

    Log::create([
        'user_id' => $user->id,
        'action' => 'UPLOAD',
        'description' => 'Mon import de palmarès',
    ]);
    Log::create([
        'user_id' => $otherUser->id,
        'action' => 'SEARCH',
        'description' => 'Activité confidentielle d’un autre compte',
    ]);

    $this->actingAs($user)
        ->get(route('logs.index'))
        ->assertOk()
        ->assertSee('Mon import de palmarès')
        ->assertDontSee('Activité confidentielle d’un autre compte');
});

it('échappe la description des journaux', function () {
    $role = Role::create(['name' => 'rh']);
    $user = User::factory()->create(['role_id' => $role->id, 'status' => true]);

    Log::create([
        'user_id' => $user->id,
        'action' => 'TEST',
        'description' => '<script>alert(1)</script>',
    ]);

    $this->actingAs($user)
        ->get(route('logs.index'))
        ->assertOk()
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSee('<script>alert(1)</script>', false);
});
