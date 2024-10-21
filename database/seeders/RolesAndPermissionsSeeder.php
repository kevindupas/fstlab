<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'create experiments']);
        Permission::create(['name' => 'delegate experiments']);
        Permission::create(['name' => 'conduct experiments']);
        Permission::create(['name' => 'edit_experiment_with_user']);
        Permission::create(['name' => 'view data']);
        Permission::create(['name' => 'export data']);

        // Create roles and assign created permissions

        // Supervisor role
        $supervisor = Role::create(['name' => 'supervisor']);
        $supervisor->givePermissionTo('manage users');

        // Principal Experimenter role
        $principalExperimenter = Role::create(['name' => 'principal_experimenter']);
        $principalExperimenter->givePermissionTo(['create experiments', 'delegate experiments', 'edit_experiment_with_user', 'view data', 'export data']);

        // Secondary Experimenter role
        $secondaryExperimenter = Role::create(['name' => 'secondary_experimenter']);
        $secondaryExperimenter->givePermissionTo(['conduct experiments', 'edit_experiment_with_user']);
    }
}
