<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Position;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GpsTrackingController extends Controller
{
    public function dashboard()
    {
        $devices = Device::with(['latestPosition'])->get();
        $totalDevices = $devices->count();
        $totalDevices = $devices->count();
        $onlineDevices = $devices->filter(fn($d) => $d->is_online)->count();
        $offlineDevices = $totalDevices - $onlineDevices;
        
        // Get recent GPS data for map
        $recentGpsData = Position::with('device')
            ->has('device') // Only get positions with existing devices
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('fix_time', '>=', now()->subHours(24))
            ->latest('fix_time')
            ->take(100)
            ->get();

        $landmarks = \App\Models\Landmark::all();
        $routes = \App\Models\Route::all();

        return view('admin.gps.dashboard', compact(
            'devices', 
            'totalDevices', 
            'onlineDevices', 
            'offlineDevices',
            'recentGpsData',
            'landmarks',
            'routes'
        ));
    }

    public function deviceMap($deviceId)
    {
        $device = Device::with(['latestPosition'])->findOrFail($deviceId);
        
        // Get device track for last 24 hours
        $gpsTrack = Position::where('device_id', $deviceId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('fix_time', '>=', now()->subHours(24))
            ->orderBy('fix_time', 'asc')
            ->get();

        return view('admin.gps.device-map', compact('device', 'gpsTrack'));
    }

    public function deviceHistory($deviceId, Request $request)
    {
        $device = Device::findOrFail($deviceId);
        
        $from = $request->get('from', now()->subDays(7)->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));
        
        $gpsHistory = Position::where('device_id', $deviceId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('fix_time', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay()
            ])
            ->orderBy('fix_time', 'desc')
            ->paginate(50);

        return view('admin.gps.device-history', compact('device', 'gpsHistory', 'from', 'to'));
    }

    public function liveData($deviceId = null)
    {
        if ($deviceId) {
            $latestData = Position::where('device_id', $deviceId)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->latest('fix_time')
                ->first();
        } else {
            $latestData = Position::with('device')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->latest('fix_time')
                ->take(20)
                ->get();
        }

        return response()->json($latestData);
    }

    public function addTestData()
    {
        $devices = Device::all();
        
        if ($devices->isEmpty()) {
            return redirect()->back()->with('error', 'No devices found. Please create a device first.');
        }

        // Generate test GPS data around Hyderabad, India (GHMC Area)
        $baseLatitude = 17.3850;
        $baseLongitude = 78.4867;
        
        foreach ($devices as $device) {
            // Create a few recent positions for each device
            for ($i = 0; $i < 5; $i++) {
                Position::create([
                    'device_id' => $device->id,
                    'latitude' => $baseLatitude + (rand(-50, 50) / 1000),
                    'longitude' => $baseLongitude + (rand(-50, 50) / 1000),
                    'speed' => rand(0, 80),
                    'course' => rand(0, 360),
                    'altitude' => rand(50, 200),
                    'satellites' => rand(4, 12),
                    'attributes' => json_encode(['battery_level' => rand(20, 100), 'signal_strength' => rand(1, 5)]),
                    'fix_time' => now()->subMinutes(rand(0, 30)),
                    'raw' => 'test_data_hyd_' . $device->id . '_' . $i
                ]);
            }

            // Update device status
            $device->update([
                'last_seen_at' => now(),
                'last_location_update' => now(),
                'status' => 'active'
            ]);
        }

        return redirect()->route('admin.gps.dashboard')->with('success', 'Hyderabad GPS data synced for all devices successfully!');
    }
}
