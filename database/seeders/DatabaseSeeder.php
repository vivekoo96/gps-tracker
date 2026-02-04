<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run Core Seeders
        $this->call([
            PermissionSeeder::class,
            SaaSSeeder::class,
        ]);

        $adminRole = Role::where('name', 'super_admin')->first();
        $userRole = Role::where('name', 'user')->first();

        // 2. Main Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );
        $admin->assignRole($adminRole);
        $admin->role = 'super_admin';
        $admin->save();

        // 3. Demo User (Scoped to no vendor, will see nothing by default)
        $demo = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );
        $demo->assignRole($userRole);
        $demo->role = 'user';
        $demo->save();

        // 4. Seed devices and GPS data
        $this->call([
            DeviceSeeder::class,
            GpsDataSeeder::class,
        ]);
    }
}
