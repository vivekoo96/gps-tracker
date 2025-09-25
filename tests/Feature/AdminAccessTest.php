<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('prevents non-admin from accessing admin panel', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('allows admin to access admin panel', function () {
    Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});


