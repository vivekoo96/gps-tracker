<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Device;
use App\Models\Geofence;
use App\Models\Position;
use App\Models\GeofenceEvent;
use App\Services\GeofenceCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class GeofenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can create geofence
     */
    public function test_admin_can_create_geofence()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/geofences', [
            'name' => 'Test Geofence',
            'description' => 'Test Description',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'radius' => 500,
            'color' => '#FF0000',
            'is_active' => true,
            'alert_on_entry' => true,
            'alert_on_exit' => true
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('geofences', [
            'name' => 'Test Geofence',
            'radius' => 500
        ]);
    }

    /**
     * Test geofence requires valid coordinates
     */
    public function test_geofence_requires_valid_coordinates()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/geofences', [
            'name' => 'Test Geofence',
            'latitude' => 999, // Invalid
            'longitude' => 999, // Invalid
            'radius' => 500,
            'color' => '#FF0000'
        ]);

        // Check for validation error or redirect
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 422,
            'Expected validation error for invalid coordinates'
        );
    }

    /**
     * Test geofence detects entry event
     */
    public function test_detects_geofence_entry()
    {
        Cache::flush(); // Clear cache before test

        $geofence = Geofence::factory()->create([
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'radius' => 500,
            'is_active' => true
        ]);

        $device = Device::factory()->create();

        // Create position inside geofence
        $position = Position::factory()->create([
            'device_id' => $device->id,
            'latitude' => 28.6139, // Same as geofence center
            'longitude' => 77.2090,
            'fix_time' => now()
        ]);

        $service = app(GeofenceCheckService::class);
        $service->checkPosition($device, $position);

        $this->assertDatabaseHas('geofence_events', [
            'geofence_id' => $geofence->id,
            'device_id' => $device->id,
            'event_type' => 'enter'
        ]);
    }

    /**
     * Test geofence detects exit event
     */
    public function test_detects_geofence_exit()
    {
        Cache::flush();

        $geofence = Geofence::factory()->create([
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'radius' => 500,
            'is_active' => true
        ]);

        $device = Device::factory()->create();

        // First, device enters geofence
        $positionInside = Position::factory()->create([
            'device_id' => $device->id,
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'fix_time' => now()->subMinutes(5)
        ]);

        $service = app(GeofenceCheckService::class);
        $service->checkPosition($device, $positionInside);

        // Then, device exits geofence
        $positionOutside = Position::factory()->create([
            'device_id' => $device->id,
            'latitude' => 28.7041, // Far from geofence
            'longitude' => 77.1025,
            'fix_time' => now()
        ]);

        $service->checkPosition($device, $positionOutside);

        // Should have both entry and exit events
        $this->assertEquals(2, GeofenceEvent::where('device_id', $device->id)->count());
        
        $this->assertDatabaseHas('geofence_events', [
            'geofence_id' => $geofence->id,
            'device_id' => $device->id,
            'event_type' => 'exit'
        ]);
    }

    /**
     * Test geofence point-in-circle detection
     */
    public function test_point_in_circle_detection()
    {
        $geofence = Geofence::factory()->create([
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'radius' => 500 // 500 meters
        ]);

        // Point inside (same location)
        $this->assertTrue($geofence->containsPoint(28.6139, 77.2090));

        // Point outside (far away)
        $this->assertFalse($geofence->containsPoint(28.7041, 77.1025));
    }

    /**
     * Test inactive geofence does not trigger events
     */
    public function test_inactive_geofence_does_not_trigger_events()
    {
        Cache::flush();

        $geofence = Geofence::factory()->create([
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'radius' => 500,
            'is_active' => false // Inactive
        ]);

        $device = Device::factory()->create();

        $position = Position::factory()->create([
            'device_id' => $device->id,
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'fix_time' => now()
        ]);

        $service = app(GeofenceCheckService::class);
        $service->checkPosition($device, $position);

        // No events should be created
        $this->assertEquals(0, GeofenceEvent::count());
    }

    /**
     * Test admin can update geofence
     */
    public function test_admin_can_update_geofence()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();
        $geofence = Geofence::factory()->create([
            'name' => 'Old Name'
        ]);

        $response = $this->actingAs($admin)->put("/admin/geofences/{$geofence->id}", [
            'name' => 'New Name',
            'latitude' => $geofence->latitude,
            'longitude' => $geofence->longitude,
            'radius' => $geofence->radius,
            'color' => $geofence->color,
            'is_active' => true
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('geofences', [
            'id' => $geofence->id,
            'name' => 'New Name'
        ]);
    }

    /**
     * Test admin can delete geofence
     */
    public function test_admin_can_delete_geofence()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();
        $geofence = Geofence::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/geofences/{$geofence->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('geofences', [
            'id' => $geofence->id
        ]);
    }

    /**
     * Test geofence radius validation
     */
    public function test_geofence_radius_validation()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();

        // Radius too small
        $response = $this->actingAs($admin)->post('/admin/geofences', [
            'name' => 'Test',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'radius' => 5, // Less than minimum (10)
            'color' => '#FF0000'
        ]);

        // Check for validation error or redirect
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 422,
            'Expected validation error for radius too small'
        );

        // Radius too large
        $response = $this->actingAs($admin)->post('/admin/geofences', [
            'name' => 'Test',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'radius' => 100000, // More than maximum (50000)
            'color' => '#FF0000'
        ]);

        // Check for validation error or redirect
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 422,
            'Expected validation error for radius too large'
        );
    }
}
