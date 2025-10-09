<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\GpsData;
use Carbon\Carbon;

class GpsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing devices
        $devices = Device::all();
        
        if ($devices->isEmpty()) {
            $this->command->info('No devices found. Please run DeviceSeeder first.');
            return;
        }

        foreach ($devices as $device) {
            $this->createGpsDataForDevice($device);
        }

        $this->command->info('GPS data seeded successfully!');
    }

    private function createGpsDataForDevice(Device $device)
    {
        // Create GPS data for the last 7 days
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();
        
        // Simulate multiple trips per day
        for ($day = 0; $day < 7; $day++) {
            $currentDate = $startDate->copy()->addDays($day);
            
            // Create 2-3 trips per day
            $tripsPerDay = rand(2, 4);
            
            for ($trip = 0; $trip < $tripsPerDay; $trip++) {
                $this->createTripData($device, $currentDate, $trip);
            }
        }
    }

    private function createTripData(Device $device, Carbon $date, int $tripNumber)
    {
        // Define some realistic coordinates (you can change these to your area)
        $baseLocations = [
            ['lat' => 17.9689, 'lng' => 79.5957, 'name' => 'Warangal Railway Station'],
            ['lat' => 17.9784, 'lng' => 79.6010, 'name' => 'Hanamkonda'],
            ['lat' => 17.9621, 'lng' => 79.5893, 'name' => 'Kazipet Junction'],
            ['lat' => 17.9456, 'lng' => 79.6124, 'name' => 'Warangal Fort'],
            ['lat' => 17.9834, 'lng' => 79.5789, 'name' => 'NIT Warangal'],
        ];

        // Pick random start and end locations
        $startLocation = $baseLocations[array_rand($baseLocations)];
        $endLocation = $baseLocations[array_rand($baseLocations)];
        
        // Ensure start and end are different
        while ($startLocation === $endLocation) {
            $endLocation = $baseLocations[array_rand($baseLocations)];
        }

        // Trip start time (random time during the day)
        $startTime = $date->copy()->setTime(rand(6, 22), rand(0, 59), rand(0, 59));
        
        // Trip duration (30 minutes to 3 hours)
        $durationMinutes = rand(30, 180);
        $endTime = $startTime->copy()->addMinutes($durationMinutes);
        
        // Number of GPS points in this trip (every 2-5 minutes)
        $pointInterval = rand(2, 5); // minutes
        $totalPoints = intval($durationMinutes / $pointInterval);
        
        for ($i = 0; $i <= $totalPoints; $i++) {
            $currentTime = $startTime->copy()->addMinutes($i * $pointInterval);
            
            // Interpolate between start and end coordinates
            $progress = $totalPoints > 0 ? $i / $totalPoints : 0;
            $lat = $startLocation['lat'] + ($endLocation['lat'] - $startLocation['lat']) * $progress;
            $lng = $startLocation['lng'] + ($endLocation['lng'] - $startLocation['lng']) * $progress;
            
            // Add some random variation to make it realistic
            $lat += (rand(-100, 100) / 100000); // ±0.001 degrees
            $lng += (rand(-100, 100) / 100000);
            
            // Calculate speed (0 at start/end, higher in middle)
            $speed = 0;
            if ($i > 0 && $i < $totalPoints) {
                $speed = rand(20, 80); // 20-80 km/h
            }
            
            // Create GPS data point
            GpsData::create([
                'device_id' => $device->id,
                'latitude' => round($lat, 6),
                'longitude' => round($lng, 6),
                'speed' => $speed,
                'direction' => rand(0, 360),
                'altitude' => rand(300, 500),
                'satellites' => rand(4, 12),
                'battery_level' => rand(20, 100),
                'signal_strength' => rand(1, 5),
                'recorded_at' => $currentTime,
                'raw_data' => json_encode([
                    'trip_id' => $device->id . '_' . $date->format('Ymd') . '_' . $tripNumber,
                    'location_name' => $i === 0 ? $startLocation['name'] : 
                                    ($i === $totalPoints ? $endLocation['name'] : 'En route')
                ])
            ]);
        }
    }
}
