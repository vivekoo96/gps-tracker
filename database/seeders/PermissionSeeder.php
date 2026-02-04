<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Vendor Management
            'view_vendors',
            'create_vendors',
            'edit_vendors',
            'delete_vendors',

            // Device Management
            'view_devices',
            'create_devices',
            'edit_devices',
            'delete_devices',

            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // Role Management
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            
            // Financials
            'view_subscriptions',
            'manage_plans',
            'view_payments',
            
            // Peripheral Configuration
            'manage_sensors',
            'manage_dashcams'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to super_admin (logic is in Gate::before, but explicit assignment is safe too)
        // $role = Role::firstOrCreate(['name' => 'super_admin']);
        // $role->givePermissionTo(Permission::all());
    }
}
