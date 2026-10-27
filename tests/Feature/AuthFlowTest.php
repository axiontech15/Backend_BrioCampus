<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'rh']);
    Role::create(['name' => 'university']);
});

it('redirige un invité vers la page de connexion', function () {
    $this->get(route('admin.index'))->assertRedirect(route('auth.login'));
});

it('connecte un administrateur et ouvre son espace', function () {
    $admin = User::factory()->create([
        'email' => 'admin@test.cd',
        'password' => 'Admin123!',
        'role_id' => Role::where('name', 'admin')->first()->id,
        'status' => true,
    ]);

    $this->post(route('auth.login.submit'), [
        'email' => $admin->email,
        'password' => 'Admin123!',
    ])->assertRedirect(route('admin.index'));

    $this->assertAuthenticatedAs($admin);
});

it('refuse un rôle RH sur les routes admin', function () {
    $rh = User::factory()->create([
        'email' => 'rh@test.cd',
        'password' => 'demo1234',
        'role_id' => Role::where('name', 'rh')->first()->id,
        'status' => true,
    ]);

    $this->actingAs($rh)
        ->get(route('admin.index'))
        ->assertForbidden();
});

it('autorise un RH sur son espace', function () {
    $rh = User::factory()->create([
        'email' => 'rh2@test.cd',
        'password' => 'demo1234',
        'role_id' => Role::where('name', 'rh')->first()->id,
        'status' => true,
    ]);

    $this->actingAs($rh)
        ->get(route('rh.index'))
        ->assertOk();
});
