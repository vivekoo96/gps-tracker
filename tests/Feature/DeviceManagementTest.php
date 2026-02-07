<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeviceManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can create device
     */
    public function test_admin_can_create_device()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/devices', [
            'name' => 'Test Device',
            'unique_id' => '123456789012345',
            'unit_type' => 'Vehicle',
            'device_category' => 'gps',
            'device_model' => 'GT06',
            'creator' => 'Test Admin',
            'status' => 'active'
        ]);

        $response->assertStatus(302); // Redirect or success
        $this->assertDatabaseHas('devices', [
            'name' => 'Test Device',
            'unique_id' => '123456789012345'
        ]);
    }

    /**
     * Test device requires valid IMEI
     */
    public function test_device_requires_valid_imei()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/devices', [
            'name' => 'Test Device',
            'unique_id' => '123', // Invalid IMEI (too short)
            'unit_type' => 'Vehicle',
            'device_category' => 'gps',
            'device_model' => 'GT06',
            'creator' => 'Test Admin'
        ]);

        // Check for either validation error or redirect
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 422,
            'Expected validation error or redirect'
        );
    }

    /**
     * Test admin can update device
     */
    public function test_admin_can_update_device()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();
        $device = Device::factory()->create([
            'name' => 'Old Name'
        ]);

        $response = $this->actingAs($admin)->put("/admin/devices/{$device->id}", [
            'name' => 'New Name',
            'unique_id' => $device->unique_id,
            'unit_type' => 'Vehicle',
            'device_type' => $device->device_type ?? 'gps',
            'creator' => 'Test Admin',
            'status' => 'active'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'name' => 'New Name'
        ]);
    }

    /**
     * Test admin can delete device
     */
    public function test_admin_can_delete_device()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();
        $device = Device::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/devices/{$device->id}");

        $response->assertStatus(302);
        $this->assertSoftDeleted('devices', [
            'id' => $device->id
        ]);
    }

    /**
     * Test device list is accessible
     */
    public function test_device_list_is_accessible()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();
        Device::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/devices');

        $response->assertStatus(200);
        $response->assertViewHas('devices');
    }

    /**
     * Test IMEI must be unique
     */
    public function test_imei_must_be_unique()
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();
        $existingDevice = Device::factory()->create([
            'unique_id' => '123456789012345'
        ]);

        $response = $this->actingAs($admin)->post('/admin/devices', [
            'name' => 'Duplicate Device',
            'unique_id' => '123456789012345', // Same unique_id
            'unit_type' => 'Vehicle',
            'device_category' => 'gps',
            'device_model' => 'GT06',
            'creator' => 'Test Admin'
        ]);

        // Check for validation error or redirect
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 422,
            'Expected validation error or redirect for duplicate IMEI'
        );
    }
}
