<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        Role::factory()->createMany(
            [
                ['name' => 'diploma'],
                ['name' => 'moderator'],
                ['name' => 'rh'],
                ['name' => 'admin'],
              
            ]
        );

        User::factory()->create([
             'name' => 'John Doe',
             'email' => 'john.doe@example.com',
             'password' => bcrypt('password'),
             'role_id' => Role::where('name', 'admin')->first()->id,
         ]);

         User::factory(15)->create();
    }
}
