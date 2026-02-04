<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\Vendor;
use App\Models\User;

class G07DeviceSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have a vendor
        $vendor = Vendor::first();
        if (!$vendor) {
            $vendor = Vendor::create([
                'name' => 'GHMC',
                'primary_color' => '#E91E63'
            ]);
        }

        // Ensure we have a user
        $user = User::first();

        $device = Device::create([
            'name' => 'G07 Device',
            'imei' => '869727072514837',
            'unique_id' => '0869727072514837', // G07/GT06 often needs 0 padded? Or just IMEI. Using user's IMEI.
            'device_type' => 'G07',
            'model' => 'G07',
            'status' => 'active',
            'server_address' => request()->getHost() . ':5023',
            'vendor_id' => $vendor->id,
            'creator' => $user ? $user->name : 'System',
            'unit_type' => 'vehicle',
            'device_category' => 'gps',
            'sim_number' => '+91-9876543210'
        ]);

        $this->command->info("G07 Device (IMEI: {$device->imei}) created successfully!");
    }
}
