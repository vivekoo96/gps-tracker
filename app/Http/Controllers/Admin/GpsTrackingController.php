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
        $onlineDevices = $devices->where('status', 'active')->count();
        $offlineDevices = $totalDevices - $onlineDevices;
        
        // Get recent GPS data for map
        $recentGpsData = Position::with('device')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('fix_time', '>=', now()->subHours(24))
            ->latest('fix_time')
            ->take(100)
            ->get();

        return view('admin.gps.dashboard', compact(
            'devices', 
            'totalDevices', 
            'onlineDevices', 
            'offlineDevices',
            'recentGpsData'
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
        // Add some test GPS data for demonstration
        $device = Device::first();
        
        if (!$device) {
            return redirect()->back()->with('error', 'No devices found. Please create a device first.');
        }

        // Generate test GPS data around Ahmedabad, India
        $baseLatitude = 23.0225;
        $baseLongitude = 72.5714;
        
        for ($i = 0; $i < 10; $i++) {
            Position::create([
                'device_id' => $device->id,
                'latitude' => $baseLatitude + (rand(-100, 100) / 10000),
                'longitude' => $baseLongitude + (rand(-100, 100) / 10000),
                'speed' => rand(0, 80),
                'course' => rand(0, 360),
                'altitude' => rand(50, 200),
                'satellites' => rand(4, 12),
                'attributes' => json_encode(['battery_level' => rand(20, 100), 'signal_strength' => rand(1, 5)]),
                'fix_time' => now()->subMinutes(rand(1, 60)),
                'raw' => 'test_data_' . $i
            ]);
        }

        // Update device last seen
        $device->update([
            'last_seen_at' => now(),
            'status' => 'active'
        ]);

        return redirect()->route('admin.gps.dashboard')->with('success', 'Test GPS data added successfully!');
    }
}
