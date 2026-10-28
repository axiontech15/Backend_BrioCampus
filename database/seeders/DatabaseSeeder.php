<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Graduate;
use App\Models\Palmares;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $rhRole = Role::create(['name' => 'rh']);
        $universityRole = Role::create(['name' => 'university']);
        $moderatorRole = Role::create(['name' => 'moderator']);

        $admin = User::create([
            'name' => 'Admin BrioCampus',
            'email' => 'admin@briocampus.cd',
            'password' => Hash::make('@admin#2026$brio'),
            'role_id' => $adminRole->id,
            'status' => true,
            'phone' => '+243810000001',
        ]);

        $rh = User::create([
            'name' => 'Alexandre Dupont',
            'email' => 'a.dupont@kintalents.cd',
            'password' => Hash::make('demo1234'),
            'role_id' => $rhRole->id,
            'status' => true,
            'phone' => '+243810000002',
        ]);

        User::create([
            'name' => 'Marie Kabeya',
            'email' => 'm.kabeya@congotalent.cd',
            'password' => Hash::make('demo1234'),
            'role_id' => $rhRole->id,
            'status' => true,
        ]);

        User::create([
            'name' => 'Contact RH Plus',
            'email' => 'contact@rhplus.cd',
            'password' => Hash::make('demo1234'),
            'role_id' => $rhRole->id,
            'status' => true,
        ]);

        User::create([
            'name' => 'Recrutement AfriTalents',
            'email' => 'recrutement@afritalents.cd',
            'password' => Hash::make('demo1234'),
            'role_id' => $rhRole->id,
            'status' => false,
        ]);

        $unikinAgent = User::create([
            'name' => 'Registre UNIKIN',
            'email' => 'registre@unikin.cd',
            'password' => Hash::make('demo1234'),
            'role_id' => $universityRole->id,
            'status' => true,
            'phone' => '+243810000003',
        ]);

        $upcAgent = User::create([
            'name' => 'Samuel Mukendi',
            'email' => 's.mukendi@upc.ac.cd',
            'password' => Hash::make('demo1234'),
            'role_id' => $universityRole->id,
            'status' => true,
        ]);

        User::create([
            'name' => 'Registre UNILU',
            'email' => 'registre@unilu.cd',
            'password' => Hash::make('demo1234'),
            'role_id' => $universityRole->id,
            'status' => false,
        ]);

        $pack5 = Category::create([
            'name' => 'Pack 5 tickets',
            'description' => 'Cinq recherches de diplômés',
            'quantity' => 5,
            'status' => 'active',
        ]);

        Category::create([
            'name' => 'Pack 10 tickets',
            'description' => 'Dix recherches de diplômés',
            'quantity' => 10,
            'status' => 'active',
        ]);

        Category::create([
            'name' => 'Pack 20 tickets',
            'description' => 'Vingt recherches de diplômés',
            'quantity' => 20,
            'status' => 'active',
        ]);

        $unikin = University::create([
            'agent_id' => $unikinAgent->id,
            'name' => 'Université de Kinshasa',
            'address' => 'Avenue de l’Université, Kinshasa',
            'status' => 'active',
        ]);

        $upc = University::create([
            'agent_id' => $upcAgent->id,
            'name' => 'Université Protestante au Congo',
            'address' => 'Avenue de la Libération, Kinshasa',
            'status' => 'active',
        ]);

        University::create([
            'agent_id' => $admin->id,
            'name' => 'Université de Lubumbashi',
            'address' => 'Lubumbashi',
            'status' => 'active',
        ]);

        University::create([
            'agent_id' => $admin->id,
            'name' => 'Institut Supérieur de Commerce',
            'address' => 'Kinshasa',
            'status' => 'active',
        ]);

        University::create([
            'agent_id' => $admin->id,
            'name' => 'Université de Kisangani',
            'address' => 'Kisangani',
            'status' => 'pending',
        ]);

        $palmares = Palmares::create([
            'university_id' => $unikin->id,
            'file' => 'palmares/demo-unikin.csv',
            'uploaded_by' => $unikinAgent->id,
            'status' => 'approved',
            'uploaded_at' => now()->subDays(10),
            'verified_at' => now()->subDays(9),
            'verified_by' => $unikinAgent->id,
        ]);

        $graduates = [
            ['matricule' => 'UNK-19-0412', 'name' => 'Mwamba Kalala', 'faculty' => 'Informatique', 'diploma_date' => '2019-07-15'],
            ['matricule' => 'UNK-20-0238', 'name' => 'Patrick Lukusa', 'faculty' => 'Économie', 'diploma_date' => '2020-07-20'],
            ['matricule' => 'UNK-21-0077', 'name' => 'Esther Kabongo', 'faculty' => 'Droit', 'diploma_date' => '2021-07-18'],
            ['matricule' => 'UNK-17-0365', 'name' => 'Jonathan Mulumba', 'faculty' => 'Gestion', 'diploma_date' => '2017-07-12'],
            ['matricule' => 'UNK-18-0150', 'name' => 'Ruth Mbala', 'faculty' => 'Médecine', 'diploma_date' => '2018-07-22'],
        ];

        foreach ($graduates as $graduate) {
            Graduate::create([
                ...$graduate,
                'university_id' => $unikin->id,
                'palmares_id' => $palmares->id,
            ]);
        }

        Palmares::create([
            'university_id' => $upc->id,
            'file' => 'palmares/demo-upc.csv',
            'uploaded_by' => $upcAgent->id,
            'status' => 'approved',
            'uploaded_at' => now()->subDays(5),
            'verified_at' => now()->subDays(4),
            'verified_by' => $upcAgent->id,
        ]);

        Ticket::create([
            'user_id' => $rh->id,
            'category_id' => $pack5->id,
            'quantity' => 8,
            'status' => 'approved',
        ]);
    }
}
