<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GeofenceValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup role and user
        Role::firstOrCreate(['name' => 'admin']);
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_geofence_creation_requires_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.geofences.store'), [
                // Empty payload to trigger required errors
            ]);

        $response->assertSessionHasErrors(['name', 'latitude', 'longitude', 'radius', 'color']);
    }

    public function test_geofence_coordinates_must_be_valid()
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.geofences.store'), [
                'name' => 'Test Geofence',
                'latitude' => 100, // Invalid > 90
                'longitude' => 200, // Invalid > 180
                'radius' => 100,
                'color' => '#FFFFFF',
            ]);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_geofence_can_be_created_with_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.geofences.store'), [
                'name' => 'Valid Geofence',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'radius' => 500,
                'color' => '#FF0000',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('admin.geofences.index'));
        $this->assertDatabaseHas('geofences', ['name' => 'Valid Geofence']);
    }
}
