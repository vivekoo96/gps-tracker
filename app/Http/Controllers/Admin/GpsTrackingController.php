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
        $deviceModels = Device::with(['latestPosition'])->get();
        $totalDevices = $deviceModels->count();
        $onlineDevices = $deviceModels->filter(fn($d) => $d->status === 'active')->count();
        $offlineDevices = $totalDevices - $onlineDevices;
        
        $devices = $deviceModels->map(function ($device) {
            $pos = $device->latestPosition;
            return [
                'id' => $device->id,
                'name' => $device->name,
                'vehicle_no' => $device->vehicle_no ?? $device->name,
                'lat' => $pos->latitude ?? $device->latitude,
                'lng' => $pos->longitude ?? $device->longitude,
                'speed' => $pos->speed ?? $device->speed ?? 0,
                'status' => $device->status === 'active' ? 'online' : 'offline',
                'is_online' => $device->status === 'active',
                'heading' => $pos->course ?? $device->heading ?? 0,
                'last_update' => $pos->fix_time ?? $device->last_location_update ?? $device->updated_at,
                'ignition' => $pos->ignition ?? false,
                'battery' => $device->battery_level ?? 0,
            ];
        });

        $landmarks = \App\Models\Landmark::all();
        $routes = \App\Models\Route::all();

        return view('admin.gps.dashboard', compact(
            'devices', 
            'totalDevices', 
            'onlineDevices', 
            'offlineDevices',
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
        $query = Device::with(['latestPosition']);
        
        if ($deviceId) {
            $query->where('id', $deviceId);
        }

        $devices = $query->get()->map(function ($device) {
            $pos = $device->latestPosition;
            return [
                'id' => $device->id,
                'name' => $device->name,
                'vehicle_no' => $device->vehicle_no,
                'lat' => $pos->latitude ?? $device->latitude,
                'lng' => $pos->longitude ?? $device->longitude,
                'speed' => $pos->speed ?? $device->speed ?? 0,
                'status' => $device->status === 'active' ? 'online' : 'offline',
                'is_online' => $device->status === 'active',
                'heading' => $pos->course ?? $device->heading ?? 0,
                'last_update' => $pos->fix_time ?? $device->last_location_update ?? $device->updated_at,
                'ignition' => $pos->ignition ?? false,
                'battery' => $device->battery_level ?? 0,
                'satellites' => $pos->satellites ?? $device->satellites ?? 0,
            ];
        });

        return response()->json(['devices' => $devices]);
    }
}
