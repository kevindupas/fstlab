<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create supervisor - TOUJOURS avec le statut 'approved'
        $supervisor = User::create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('password'),
            'university' => 'Supervisory University',
            'registration_reason' => 'Overseeing research operations',
            'orcid' => null,
            'status' => 'approved', // Explicitement 'approved'
            'created_by' => null // Le superviseur n'a pas de créateur
        ]);
        $supervisor->assignRole('supervisor');

        $principals = [];
        for ($i = 1; $i <= 4; $i++) {
            $status = $i % 2 == 0 ? 'pending' : 'approved';

            $principal = User::create([
                'name' => "Principal Experimenter $i",
                'email' => "principal$i@example.com",
                'password' => Hash::make('password'),
                'university' => "Principal University $i",
                'registration_reason' => "Conducting primary research in field $i",
                'orcid' => null,
                'status' => $status,
                'created_by' => $supervisor->id
            ]);
            $principal->assignRole('principal_experimenter');

            // Only add approved principals to the list for creating secondary experimenters
            if ($status === 'approved') {
                $principals[] = $principal;
            }
        }

        // Create secondary experimenters only for approved principal experimenters
        for ($i = 1; $i <= 10; $i++) {
            // Skip if no approved principals
            if (empty($principals)) break;

            // Assign secondary experimenters to random approved principal experimenter
            $principal = $principals[array_rand($principals)];

            $secondary = User::create([
                'name' => "Secondary Experimenter $i",
                'email' => "secondary$i@example.com",
                'password' => Hash::make('password'),
                'university' => "Secondary University $i",
                'registration_reason' => "Assisting in research project $i",
                'orcid' => null,
                'status' => 'approved',
                'created_by' => $principal->id
            ]);
            $secondary->assignRole('secondary_experimenter');
        }
    }
}
