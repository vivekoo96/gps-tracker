<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class FixAdminRole extends Command
{
    protected $signature = 'auth:fix-admin {email=admin@example.com}';
    protected $description = 'Ensure a user has the super_admin role';

    public function handle()
    {
        $email = $this->argument('email');
        
        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole($role);
            $user->role = 'super_admin'; // Also update the field for redundancy
            $user->save();
            $this->info("Successfully assigned 'super_admin' role to {$email}");
        } else {
            $this->error("User with email {$email} not found.");
        }
    }
}
