<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaaSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Subscription Plans
        if (\App\Models\SubscriptionPlan::count() === 0) {
            \App\Models\SubscriptionPlan::create([
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Entry level plan',
                'price' => 29.00,
                'max_devices' => 10,
                'max_users' => 2,
            ]);
            \App\Models\SubscriptionPlan::create([
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Professional fleet management',
                'price' => 99.00,
                'max_devices' => 50,
                'max_users' => 10,
            ]);
        }

        // 2. Ensure Super Admin Exists
        $superAdmin = \App\Models\User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // 3. Assign Role (using Spatie Permissions)
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->assignRole($role);
        
        // Also update the column for easy reference/redundancy if needed
        $superAdmin->role = 'super_admin';
        $superAdmin->save();

        // 4. Create Vendor Admin Role
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'vendor_admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user']);

        // 5. Assign Permissions to Vendor Admin
        $permissions = [
            'view_devices', 'create_devices', 'edit_devices', 'delete_devices',
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'manage_sensors', 'manage_dashcams'
        ];
        
        $adminRole->syncPermissions($permissions);
    }
}
