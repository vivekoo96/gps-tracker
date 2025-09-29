<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\GpsData;
use Illuminate\Http\Request;

class GpsTrackingController extends Controller
{
    public function dashboard()
    {
        $devices = Device::with(['latestGpsData'])->get();
        
        return view('gps.dashboard', compact('devices'));
    }

    public function deviceTrack($deviceId)
    {
        $device = Device::findOrFail($deviceId);
        $gpsData = GpsData::getDeviceTrack($deviceId, now()->subDay(), now());
        
        return view('gps.track', compact('device', 'gpsData'));
    }

    public function liveData($deviceId)
    {
        $latestData = GpsData::getLatestPosition($deviceId);
        
        return response()->json($latestData);
    }
}
