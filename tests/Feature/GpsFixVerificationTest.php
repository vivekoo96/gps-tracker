<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\User;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class GpsFixVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_gps_data_flow_is_consistent()
    {
        // 1. Setup Admin User
        $adminRole = Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole($adminRole);

        // 2. Create Device
        $device = Device::create([
            'name' => 'Test Device',
            'unique_id' => '1234567890',
            'imei' => '1234567890',
            'status' => 'active',
            'device_type' => 'gps',
            'creator' => 'test',
            'unit_type' => 'test',
            'model' => 'Test Model',
            'server_address' => '127.0.0.1'
        ]);

        // 3. Simulate Device Sending Data (Ingestion)
        $response = $this->postJson('/gps/data', [
            'device_id' => '1234567890',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'speed' => 50.5,
            'timestamp' => now()->toIso8601String()
        ]);

        $response->assertStatus(200);

        // 4. Verify Data is in Positions Table
        $this->assertDatabaseHas('positions', [
            'device_id' => $device->id,
            'latitude' => 40.7128,
            'longitude' => -74.0060
        ]);

        // 5. Verify Device Model Relationship (The Fix)
        $device->refresh();
        $this->assertNotNull($device->latestPosition);
        $this->assertEquals(40.7128, $device->latestPosition->latitude);
        
        // 6. Verify Admin Dashboard Query Logic (The Fix)
        // Simulate what Admin/GpsTrackingController does
        $adminDashboardData = Position::with('device')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest('fix_time')
            ->first();

        $this->assertNotNull($adminDashboardData);
        $this->assertEquals($device->id, $adminDashboardData->device->id);
    }
}
