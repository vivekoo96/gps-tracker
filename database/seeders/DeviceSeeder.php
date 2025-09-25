<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            [
                'name' => 'GPS-001',
                'model' => 'GT800',
                'imei' => '123456789012345',
                'sim_number' => '+91-9876543210',
                'status' => 'active',
                'latitude' => 23.0225,
                'longitude' => 72.5714,
                'speed' => 45,
                'battery_level' => 85,
                'location_address' => 'Ahmedabad, Gujarat, India',
                'is_moving' => true,
                'heading' => 135.50,
                'altitude' => 54,
                'satellites' => 8,
                'last_location_update' => now()->subMinutes(2),
                'last_seen_at' => now()->subMinutes(2),
            ],
            [
                'name' => 'GPS-002',
                'model' => 'MT100',
                'imei' => '123456789012346',
                'sim_number' => '+91-9876543211',
                'status' => 'active',
                'latitude' => 23.0330,
                'longitude' => 72.5800,
                'speed' => 0,
                'battery_level' => 92,
                'location_address' => 'SG Highway, Ahmedabad, Gujarat',
                'is_moving' => false,
                'heading' => 0.00,
                'altitude' => 52,
                'satellites' => 6,
                'last_location_update' => now()->subMinutes(1),
                'last_seen_at' => now()->subMinutes(1),
            ],
            [
                'name' => 'GPS-003',
                'model' => 'TK103',
                'imei' => '123456789012347',
                'sim_number' => '+91-9876543212',
                'status' => 'inactive',
                'latitude' => 23.0180,
                'longitude' => 72.5650,
                'speed' => 0,
                'battery_level' => 15,
                'location_address' => 'Satellite, Ahmedabad, Gujarat',
                'is_moving' => false,
                'heading' => 270.00,
                'altitude' => 48,
                'satellites' => 3,
                'last_location_update' => now()->subHours(2),
                'last_seen_at' => now()->subHours(2),
            ],
            [
                'name' => 'GPS-004',
                'model' => 'GT06N',
                'imei' => '123456789012348',
                'sim_number' => '+91-9876543213',
                'status' => 'active',
                'latitude' => 23.0450,
                'longitude' => 72.5900,
                'speed' => 65,
                'battery_level' => 78,
                'location_address' => 'Bopal, Ahmedabad, Gujarat',
                'is_moving' => true,
                'heading' => 45.25,
                'altitude' => 58,
                'satellites' => 9,
                'last_location_update' => now()->subMinutes(3),
                'last_seen_at' => now()->subMinutes(3),
            ],
            [
                'name' => 'GPS-005',
                'model' => 'GT800',
                'imei' => '123456789012349',
                'sim_number' => '+91-9876543214',
                'status' => 'active',
                'latitude' => 23.0100,
                'longitude' => 72.5500,
                'speed' => 30,
                'battery_level' => 95,
                'location_address' => 'Vastrapur, Ahmedabad, Gujarat',
                'is_moving' => true,
                'heading' => 180.00,
                'altitude' => 50,
                'satellites' => 7,
                'last_location_update' => now()->subMinutes(4),
                'last_seen_at' => now()->subMinutes(4),
            ],
        ];

        foreach ($devices as $deviceData) {
            Device::create($deviceData);
        }
    }
}
