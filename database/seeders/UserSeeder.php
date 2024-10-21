<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create supervisor
        $supervisor = User::create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('password')
        ]);
        $supervisor->assignRole('supervisor');

        // Create principal experimenters
        for ($i = 1; $i <= 4; $i++) {
            $principal = User::create([
                'name' => "Principal Experimenter $i",
                'email' => "principal$i@example.com",
                'password' => Hash::make('password')
            ]);
            $principal->assignRole('principal_experimenter');
        }

        // Get all principal experimenters
        $principals = User::role('principal_experimenter')->get();

        // Create secondary experimenters
        for ($i = 1; $i <= 10; $i++) {
            // Assign secondary experimenters to random principal experimenter
            $principal = $principals->random();

            $secondary = User::create([
                'name' => "Secondary Experimenter $i",
                'email' => "secondary$i@example.com",
                'password' => Hash::make('password'),
                'created_by' => $principal->id
            ]);
            $secondary->assignRole('secondary_experimenter');
        }
    }
}
