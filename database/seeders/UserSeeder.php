<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Désactiver temporairement l'Observer
        User::unsetEventDispatcher();

        // Create supervisor
        $supervisor = User::create([
            'name' => 'Supervisor User',
            'email' => 'dupas.kevin@gmail.com',
            'password' => Hash::make('password'),
            'university' => 'Supervisory University',
            'registration_reason' => 'Overseeing research operations',
            'orcid' => null,
            'status' => 'approved',
            'created_by' => null
        ]);
        $supervisor->assignRole('supervisor');

        // $principals = [];
        // for ($i = 1; $i <= 1; $i++) {
        //     $principal = User::create([
        //         'name' => "Dupas Dev",
        //         'email' => "dupas.dev@gmail.com",
        //         'password' => Hash::make('password'),
        //         'university' => "Principal University $i",
        //         'registration_reason' => "Conducting primary research in field $i",
        //         'orcid' => null,
        //         'status' => 'approved',
        //         'created_by' => $supervisor->id
        //     ]);
        //     $principal->assignRole('principal_experimenter');
        //     $principals[] = $principal;
        // }

        // Create secondary experimenters
        // for ($i = 1; $i <= 10; $i++) {
        //     // Skip if no approved principals
        //     if (empty($principals)) break;

        //     // Assign secondary experimenters to random approved principal experimenter
        //     $principal = $principals[array_rand($principals)];

        //     $secondary = User::create([
        //         'name' => "Secondary Experimenter $i",
        //         'email' => "secondary$i@example.com",
        //         'password' => Hash::make('password'),
        //         'university' => "Secondary University $i",
        //         'registration_reason' => "Assisting in research project $i",
        //         'orcid' => null,
        //         'status' => 'approved',
        //         'created_by' => $principal->id
        //     ]);
        //     $secondary->assignRole('secondary_experimenter');
        // }
    }
}
