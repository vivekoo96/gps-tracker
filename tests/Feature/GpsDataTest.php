<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GpsDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test can store GPS data with valid device
     */
    public function test_can_store_gps_data()
    {
        $device = Device::factory()->create([
            'imei' => '123456789012345'
        ]);

        $response = $this->postJson('/api/gps/store', [
            'imei' => '123456789012345',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'speed' => 45,
            'altitude' => 100,
            'fix_time' => now()->toDateTimeString()
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('positions', [
            'device_id' => $device->id,
            'latitude' => 28.6139,
            'longitude' => 77.2090
        ]);
    }

    /**
     * Test rejects invalid coordinates
     */
    public function test_rejects_invalid_coordinates()
    {
        $device = Device::factory()->create();

        $response = $this->postJson('/api/gps/store', [
            'imei' => $device->imei,
            'latitude' => 999, // Invalid latitude
            'longitude' => 999, // Invalid longitude
            'speed' => 45,
            'fix_time' => now()->toDateTimeString()
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test rejects GPS data from unknown device
     */
    public function test_rejects_gps_data_from_unknown_device()
    {
        $response = $this->postJson('/api/gps/store', [
            'imei' => '999999999999999', // Non-existent device
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'speed' => 45,
            'fix_time' => now()->toDateTimeString()
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test GPS data requires all mandatory fields
     */
    public function test_gps_data_requires_mandatory_fields()
    {
        $device = Device::factory()->create();

        $response = $this->postJson('/api/gps/store', [
            'imei' => $device->imei,
            // Missing latitude, longitude, etc.
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    /**
     * Test can retrieve device latest position
     */
    public function test_can_retrieve_device_latest_position()
    {
        $device = Device::factory()->create();
        
        Position::factory()->create([
            'device_id' => $device->id,
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'fix_time' => now()->subHours(2)
        ]);

        $latestPosition = Position::factory()->create([
            'device_id' => $device->id,
            'latitude' => 28.7041,
            'longitude' => 77.1025,
            'fix_time' => now()
        ]);

        $device->refresh();
        
        $this->assertEquals($latestPosition->id, $device->latestPosition->id);
    }

    /**
     * Test speed validation
     */
    public function test_speed_validation()
    {
        $device = Device::factory()->create();

        // Negative speed should be rejected
        $response = $this->postJson('/api/gps/store', [
            'imei' => $device->imei,
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'speed' => -10, // Invalid negative speed
            'fix_time' => now()->toDateTimeString()
        ]);

        $response->assertStatus(422);
    }
}
