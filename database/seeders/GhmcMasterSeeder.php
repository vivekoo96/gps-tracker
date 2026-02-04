<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zone;
use App\Models\Circle;
use App\Models\Ward;
use App\Models\TransferStation;
use App\Models\Landmark;
use App\Models\Route;
use Illuminate\Support\Facades\DB;

class GhmcMasterSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TransferStation::truncate();
        Landmark::truncate();
        Route::truncate();
        Ward::truncate();
        Circle::truncate();
        Zone::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $ghmcStructure = [
            'Malkajgiri' => [
                'Keesara', 'Alwal', 'Bowenpally', 'Moula Ali', 'Malkajgiri'
            ],
            'Uppal' => [
                'Ghatkesar', 'Kapra', 'Nacharam', 'Uppal', 'Boduppal'
            ],
            'L.B. Nagar' => [
                'Nagole', 'Saroornagar', 'L.B. Nagar', 'Hayathnagar'
            ],
            'Shamshabad' => [
                'Adibatla', 'Badangpet', 'Jalpally', 'Shamshabad'
            ],
            'Rajendranagar' => [
                'Rajendranagar', 'Attapur', 'Bahadurpura', 'Falaknuma', 'Chandrayangutta', 'Jangammet'
            ],
            'Charminar' => [
                'Santoshnagar', 'Yakutpura', 'Malakpet', 'Charminar', 'Moosarambagh'
            ],
            'Golconda' => [
                'Goshamahal', 'Karwan', 'Golconda', 'Mehdipatnam', 'Masab Tank'
            ],
            'Khairatabad' => [
                'Khairatabad', 'Jubilee Hills', 'Borabanda', 'Yousufguda', 'Ameerpet'
            ],
            'Secunderabad' => [
                'Kavadiguda', 'Musheerabad', 'Amberpet', 'Tarnaka', 'Mettuguda'
            ],
            'Serilingampally' => [
                'Narsingi', 'Patancheruvu', 'Ameenpur', 'Miyapur', 'Serilingampally'
            ],
            'Kukatpally' => [
                'Madhapur', 'Allwyn Colony', 'Kukatpally', 'Moosapet'
            ],
            'Quthbullapur' => [
                'Chintal', 'Jeedimetla', 'Kompally', 'Gajularamaram', 'Quthbullapur', 'Suraram', 'Dulapally'
            ]
        ];

        // Known Real Transfer Stations
        $realTransferStations = [
            'Darulshifa' => ['Charminar', 'Yakutpura'],
            'Bandlaguda' => ['Chandrayangutta', 'Falaknuma'],
            'Yousufguda' => ['Yousufguda'],
            'Amberpet' => ['Amberpet'],
            'Macha Bollaram' => ['Alwal'],
            'Imlibun' => ['Malakpet', 'Moosarambagh'],
            'Lower Tank Bund' => ['Kavadiguda'],
            'Sanathnagar' => ['Ameerpet'],
            'Kukatpally' => ['Kukatpally'],
            'L.B. Nagar' => ['L.B. Nagar']
        ];

        foreach ($ghmcStructure as $zoneName => $circles) {
            $zone = Zone::create(['name' => $zoneName . ' Zone']);
            
            foreach ($circles as $index => $circleName) {
                $circle = Circle::create([
                    'name' => $circleName . ' Circle',
                    'zone_id' => $zone->id
                ]);

                // Create 5 Wards per Circle to reach ~300 total wards
                for ($i = 1; $i <= 5; $i++) {
                    $wardName = $circleName . ' Ward ' . $i;
                    $ward = Ward::create([
                        'name' => $wardName,
                        'circle_id' => $circle->id
                    ]);

                    // Check if we should add a Real Transfer Station here
                    $stationAdded = false;
                    foreach ($realTransferStations as $stationName => $targetCircles) {
                        if (in_array($circleName, $targetCircles) && $i == 1) { // Add to first ward of the circle
                            TransferStation::create([
                                'name' => $stationName . ' SCTP',
                                'ward_id' => $ward->id,
                                'capacity' => rand(100, 500),
                                'current_load' => rand(0, 50),
                                'latitude' => 17.3850 + (rand(-100, 100) / 1000), // Approximate Hyd Lat
                                'longitude' => 78.4867 + (rand(-100, 100) / 1000), // Approximate Hyd Long
                            ]);
                            $stationAdded = true;
                        }
                    }

                    // Randomly add generic Transfer Stations (approx 1 every 10 wards to get ~30 more)
                    if (!$stationAdded && rand(1, 10) == 1) {
                         TransferStation::create([
                                'name' => $circleName . ' Transfer Point',
                                'ward_id' => $ward->id,
                                'capacity' => rand(50, 200),
                                'current_load' => rand(0, 30),
                                'latitude' => 17.3850 + (rand(-100, 100) / 1000),
                                'longitude' => 78.4867 + (rand(-100, 100) / 1000),
                            ]);
                    }
                }
            }
        }

        // Seed Landmarks
        $landmarkTypes = ['Garage', 'Dump Yard', 'Transfer Station', 'Office'];
        $firstUser = \App\Models\User::first();
        foreach ($landmarkTypes as $type) {
            Landmark::create([
                'name' => 'Demo ' . $type,
                'type' => $type,
                'latitude' => 17.3850 + (rand(-100, 100) / 1000),
                'longitude' => 78.4867 + (rand(-100, 100) / 1000),
                'created_by' => $firstUser ? $firstUser->id : null
            ]);
        }

        // Seed Routes
        $demoRoutes = [
            'East Logistics Route' => [
                ['lat' => 17.3984, 'lng' => 78.5509],
                ['lat' => 17.4050, 'lng' => 78.5550],
                ['lat' => 17.4120, 'lng' => 78.5600],
                ['lat' => 17.4200, 'lng' => 78.5700],
            ],
            'Central Heritage Path' => [
                ['lat' => 17.3616, 'lng' => 78.4747],
                ['lat' => 17.3650, 'lng' => 78.4780],
                ['lat' => 17.3700, 'lng' => 78.4820],
                ['lat' => 17.3750, 'lng' => 78.4850],
            ]
        ];

        foreach ($demoRoutes as $name => $stops) {
            Route::create([
                'name' => $name,
                'description' => 'Automated demo route for ' . $name,
                'stops' => $stops // Model casts this to array
            ]);
        }
    }
}
