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
            ['matricule' => 'UNK-22-0045', 'name' => 'Jean-Pierre Tshibanda', 'faculty' => 'Sciences', 'diploma_date' => '2022-07-19'],
            ['matricule' => 'UNK-16-0299', 'name' => 'Alice Mbuyi', 'faculty' => 'Lettres', 'diploma_date' => '2016-07-14'],
            ['matricule' => 'UNK-23-0011', 'name' => 'Emmanuel Kabasele', 'faculty' => 'Philosophie', 'diploma_date' => '2023-07-21'],
            ['matricule' => 'UNK-15-0123', 'name' => 'Chantal Nsimba', 'faculty' => 'Sciences sociales', 'diploma_date' => '2015-07-17'],
            ['matricule' => 'UNK-14-0456', 'name' => 'Fabrice Kabongo', 'faculty' => 'Sciences politiques', 'diploma_date' => '2014-07-16'],
            ['matricule' => 'UNK-13-0789', 'name' => 'Joséphine Mbuyi', 'faculty' => 'Sciences de l’éducation', 'diploma_date' => '2013-07-15'],
            ['matricule' => 'UNK-12-0345', 'name' => 'Michel Tshibanda', 'faculty' => 'Sciences de la communication', 'diploma_date' => '2012-07-14'],
            ['matricule' => 'UNK-11-0567', 'name' => 'Aline Kabongo', 'faculty' => 'Sciences de l’information', 'diploma_date' => '2011-07-13'],
            ['matricule' => 'UNK-10-0890', 'name' => 'Jean-Claude Mbuyi', 'faculty' => 'Sciences de la santé publique', 'diploma_date' => '2010-07-12'],
            ['matricule' => 'UNK-09-0123', 'name' => 'Marie-Louise Kabongo', 'faculty' => 'Sciences infirmières', 'diploma_date' => '2009-07-11'],
            ['matricule' => 'UNK-08-0456', 'name' => 'Pierre Mbuyi', 'faculty' => 'Sciences pharmaceutiques', 'diploma_date' => '2008-07-10'],
            ['matricule' => 'UNK-07-0789', 'name' => 'Sophie Kabongo', 'faculty' => 'Sciences vétérinaires', 'diploma_date' => '2007-07-09'],
            ['matricule' => 'UNK-06-0345', 'name' => 'Jean-Baptiste Mbuyi', 'faculty' => 'Sciences agronomiques', 'diploma_date' => '2006-07-08'],
            ['matricule' => 'UNK-05-0567', 'name' => 'Catherine Kabongo', 'faculty' => 'Sciences forestières', 'diploma_date' => '2005-07-07'],
            ['matricule' => 'UNK-04-0890', 'name' => 'Joseph Mbuyi', 'faculty' => 'Sciences géologiques', 'diploma_date' => '2004-07-06'],
            ['matricule' => 'UNK-03-0123', 'name' => 'Isabelle Kabongo', 'faculty' => 'Sciences géographiques', 'diploma_date' => '2003-07-05'],
            ['matricule' => 'UNK-02-0456', 'name' => 'David Mbuyi', 'faculty' => 'Sciences mathématiques', 'diploma_date' => '2002-07-04'],
            ['matricule' => 'UNK-01-0789', 'name' => 'Anne Kabongo', 'faculty' => 'Sciences physiques', 'diploma_date' => '2001-07-03']
        ];

        foreach ($graduates as $graduate) {
            Graduate::create([
                ...$graduate,
                'university_id' => $unikin->id,
                'palmares_id' => $palmares->id,
            ]);
        }

        $graduates = [
            ['matricule' => 'UPC-19-0412', 'name' => 'Jean-Pierre Mbuyi', 'faculty' => 'Informatique', 'diploma_date' => '2019-07-15'],
            ['matricule' => 'UPC-20-0238', 'name' => 'Marie-Louise Kabongo', 'faculty' => 'Économie', 'diploma_date' => '2020-07-20'],
            ['matricule' => 'UPC-21-0077', 'name' => 'David Mbuyi', 'faculty' => 'Droit', 'diploma_date' => '2021-07-18'],
            ['matricule' => 'UPC-17-0365', 'name' => 'Anne Kabongo', 'faculty' => 'Gestion', 'diploma_date' => '2017-07-12'],
            ['matricule' => 'UPC-18-0150', 'name' => 'Joseph Mbuyi', 'faculty' => 'Médecine', 'diploma_date' => '2018-07-22'],
            ['matricule' => 'UPC-22-0045', 'name' => 'Isabelle Kabongo', 'faculty' => 'Sciences', 'diploma_date' => '2022-07-19'],
            ['matricule' => 'UPC-16-0299', 'name' => 'Catherine Kabongo', 'faculty' => 'Lettres', 'diploma_date' => '2016-07-14'],
            ['matricule' => 'UPC-23-0011', 'name' => 'Jean-Baptiste Mbuyi', 'faculty' => 'Philosophie', 'diploma_date' => '2023-07-21'],
            ['matricule' => 'UPC-15-0123', 'name' => 'Sophie Kabongo', 'faculty' => 'Sciences sociales', 'diploma_date' => '2015-07-17'],
            ['matricule' => 'UPC-14-0456', 'name' => 'Pierre Mbuyi', 'faculty' => 'Sciences politiques', 'diploma_date' => '2014-07-16'],
            ['matricule' => 'UPC-13-0789', 'name' => 'Marie-Louise Kabongo', 'faculty' => 'Sciences de l’éducation', 'diploma_date' => '2013-07-15'],
            ['matricule' => 'UPC-12-0345', 'name' => 'Jean-Claude Mbuyi', 'faculty' => 'Sciences de la communication', 'diploma_date' => '2012-07-14'],
            ['matricule' => 'UPC-11-0567', 'name' => 'Aline Kabongo', 'faculty' => 'Sciences de l’information', 'diploma_date' => '2011-07-13'],
            ['matricule' => 'UPC-10-0890', 'name' => 'Michel Tshibanda', 'faculty' => 'Sciences de la santé publique', 'diploma_date' => '2010-07-12'],
            ['matricule' => 'UPC-09-0123', 'name' => 'Joséphine Mbuyi', 'faculty' => 'Sciences infirmières', 'diploma_date' => '2009-07-11']
        ];
        foreach ($graduates as $graduate) {
            Graduate::create([
                ...$graduate,
                'university_id' => $upc->id,
                'palmares_id' => null,
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
