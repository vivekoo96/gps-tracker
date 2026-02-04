<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Device;
use App\Models\User;
use App\Models\Vendor;
use App\Models\FuelSensor;
use App\Models\Dashcam;
use Spatie\Permission\Models\Role;

class PeripheralIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup initial data
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'vendor_admin']);
    }

    public function test_gps_data_updates_fuel_level_correctly()
    {
        // 1. Create Device
        $device = Device::factory()->create([
            'unique_id' => 'TEST_DEVICE_001',
            'device_type' => 'gps',
        ]);

        // 2. Attach Fuel Sensor with Calibration
        // Calibration: 0 raw = 0 liters, 4000 raw = 100 liters
        $calibration = [
            "0" => "0",
            "4000" => "100"
        ];
        
        $fuelSensor = FuelSensor::create([
            'device_id' => $device->id,
            'tank_capacity' => 100,
            'calibration_data' => $calibration,
            'data_source' => 'adc1',
            'status' => 'active'
        ]);

        // 3. Send GPS Data with ADC1 value
        $response = $this->postJson(route('gps.receive'), [
            'device_id' => 'TEST_DEVICE_001',
            'latitude' => 10.0,
            'longitude' => 20.0,
            'adc1' => 2000, // Should interpolate to 50 Liters
        ]);

        $response->assertStatus(200);

        // 4. Verify Database Update
        $this->assertDatabaseHas('fuel_sensors', [
            'id' => $fuelSensor->id,
            'current_level' => 50.00, // 2000 is exactly half of 4000
        ]);
    }

    public function test_gps_data_updates_dashcam_status()
    {
        // 1. Create Device
        $device = Device::factory()->create([
            'unique_id' => 'TEST_DASHCAM_001',
            'device_type' => 'gps',
        ]);

        // 2. Attach Dashcam
        $dashcam = Dashcam::create([
            'device_id' => $device->id,
            'resolution' => '1080p',
            'status' => 'offline'
        ]);

        // 3. Send GPS Data with dashcam status
        $response = $this->postJson(route('gps.receive'), [
            'device_id' => 'TEST_DASHCAM_001',
            'latitude' => 10.0,
            'longitude' => 20.0,
            'dashcam_status' => 'rec', // Should map to 'recording'
        ]);

        $response->assertStatus(200);

        // 4. Verify Database Update
        $this->assertDatabaseHas('dashcams', [
            'id' => $dashcam->id,
            'status' => 'recording',
        ]);
    }

    public function test_it_supports_custom_data_sources_for_fuel()
    {
        // 1. Create Device
        $device = Device::factory()->create([
            'unique_id' => 'TEST_CUSTOM_SOURCE',
        ]);

        // 2. Attach Fuel Sensor with CUSTOM source
        $fuelSensor = FuelSensor::create([
            'device_id' => $device->id,
            'tank_capacity' => 100,
            'data_source' => 'can_fuel_level', // Custom key
            'calibration_data' => ["0" => "0", "100" => "100"], // Direct percentage
        ]);

        // 3. Send GPS Data with CUSTOM key
        $this->postJson(route('gps.receive'), [
            'device_id' => 'TEST_CUSTOM_SOURCE',
            'latitude' => 10.0,
            'longitude' => 20.0,
            'can_fuel_level' => 75, 
            'adc1' => 10, // Should be ignored
        ]);

        // 4. Verify it used the custom key
        $this->assertDatabaseHas('fuel_sensors', [
            'id' => $fuelSensor->id,
            'current_level' => 75.00, 
        ]);
    }
}
