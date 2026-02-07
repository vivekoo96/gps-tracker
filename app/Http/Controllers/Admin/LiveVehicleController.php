<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiveVehicleController extends Controller
{
    /**
     * Display live vehicle view
     */
    public function index()
    {
        $zones = DB::table('zones')->get();
        $wards = DB::table('wards')->get();
        $transferStations = DB::table('transfer_stations')->get();
        $devices = Device::all();
        
        return view('admin.live-vehicle.index', compact('zones', 'wards', 'transferStations', 'devices'));
    }

    /**
     * Get vehicle information and statistics
     */
    public function getVehicleInfo(Request $request)
    {
        $query = Device::query();
        
        if ($request->vehicle_id) {
            $query->where('id', $request->vehicle_id);
        }
        if ($request->zone_id) {
            $query->where('zone_id', $request->zone_id);
        }
        if ($request->ward_id) {
            $query->where('ward_id', $request->ward_id);
        }
        if ($request->transfer_station_id) {
            $query->where('transfer_station_id', $request->transfer_station_id);
        }
        
        $device = $query->with(['zone', 'ward'])->first();
        
        if (!$device) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }
        
        // Get latest position
        $latestPosition = Position::where('device_id', $device->id)
            ->orderBy('fix_time', 'desc')
            ->first();
        
        if (!$latestPosition) {
            return response()->json(['error' => 'No position data available'], 404);
        }
        
        // Get today's positions for statistics
        $todayPositions = Position::where('device_id', $device->id)
            ->whereDate('fix_time', today())
            ->orderBy('fix_time', 'asc')
            ->get();
        
        // Calculate statistics
        $statistics = $this->calculateStatistics($todayPositions, $latestPosition);
        
        // Get recent alerts
        $alerts = DB::table('device_alerts')
            ->where('device_id', $device->id)
            ->where('alert_time', '>=', now()->subHours(24))
            ->orderBy('alert_time', 'desc')
            ->limit(10)
            ->get();
        
        // Get transfer station info
        $transferStation = null;
        if ($device->transfer_station_id) {
            $transferStation = DB::table('transfer_stations')
                ->where('id', $device->transfer_station_id)
                ->first();
        }
        
        return response()->json([
            'success' => true,
            'vehicle' => [
                'id' => $device->id,
                'name' => $device->name,
                'vehicle_no' => $device->vehicle_no,
                'vehicle_type' => $device->vehicle_type ?? 'Unknown',
                'contact' => $device->phone ?? 'N/A',
                'zone' => $device->zone->name ?? 'N/A',
                'ward' => $device->ward->name ?? 'N/A',
                'transfer_station' => $transferStation->name ?? 'N/A'
            ],
            'current_position' => [
                'latitude' => $latestPosition->latitude,
                'longitude' => $latestPosition->longitude,
                'speed' => round($latestPosition->speed, 1),
                'last_update' => $latestPosition->fix_time->diffForHumans(),
                'last_update_full' => $latestPosition->fix_time->format('Y-m-d H:i:s')
            ],
            'statistics' => $statistics,
            'alerts' => $alerts->map(function($alert) {
                return [
                    'type' => $alert->alert_type,
                    'time' => Carbon::parse($alert->alert_time)->diffForHumans(),
                    'speed' => $alert->speed,
                    'is_acknowledged' => $alert->is_acknowledged
                ];
            })
        ]);
    }

    /**
     * Get vehicle path history
     */
    public function getVehiclePath(Request $request)
    {
        $vehicleId = $request->vehicle_id;
        $duration = $request->duration ?? 60; // minutes
        
        $positions = Position::where('device_id', $vehicleId)
            ->where('fix_time', '>=', now()->subMinutes($duration))
            ->orderBy('fix_time', 'asc')
            ->get(['latitude', 'longitude', 'speed', 'fix_time']);
        
        return response()->json([
            'success' => true,
            'positions' => $positions->map(function($pos) {
                return [
                    'lat' => $pos->latitude,
                    'lng' => $pos->longitude,
                    'speed' => round($pos->speed, 1),
                    'time' => $pos->fix_time->format('H:i:s')
                ];
            })
        ]);
    }

    /**
     * Calculate vehicle statistics
     */
    private function calculateStatistics($positions, $latestPosition)
    {
        if ($positions->isEmpty()) {
            return [
                'current_speed' => 0,
                'max_speed' => 0,
                'avg_speed' => 0,
                'trip_time' => '0h 0m',
                'idle_time' => '0h 0m',
                'distance' => 0
            ];
        }
        
        // Current speed
        $currentSpeed = round($latestPosition->speed, 1);
        
        // Max speed
        $maxSpeed = round($positions->max('speed'), 1);
        
        // Average speed (only when moving)
        $movingPositions = $positions->where('speed', '>', 0);
        $avgSpeed = $movingPositions->isNotEmpty() ? round($movingPositions->avg('speed'), 1) : 0;
        
        // Trip time
        $firstPosition = $positions->first();
        $tripSeconds = $firstPosition->fix_time->diffInSeconds($latestPosition->fix_time);
        $tripTime = $this->formatDuration($tripSeconds);
        
        // Idle time (speed = 0)
        $idleSeconds = 0;
        for ($i = 0; $i < $positions->count() - 1; $i++) {
            if ($positions[$i]->speed == 0) {
                $idleSeconds += $positions[$i]->fix_time->diffInSeconds($positions[$i + 1]->fix_time);
            }
        }
        $idleTime = $this->formatDuration($idleSeconds);
        
        // Distance
        $distance = 0;
        for ($i = 0; $i < $positions->count() - 1; $i++) {
            $distance += $this->calculateDistance(
                $positions[$i]->latitude,
                $positions[$i]->longitude,
                $positions[$i + 1]->latitude,
                $positions[$i + 1]->longitude
            );
        }
        
        return [
            'current_speed' => $currentSpeed,
            'max_speed' => $maxSpeed,
            'avg_speed' => $avgSpeed,
            'trip_time' => $tripTime,
            'idle_time' => $idleTime,
            'distance' => round($distance, 2)
        ];
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * Format duration in seconds to human readable format
     */
    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return "{$hours}h {$minutes}m";
    }
}
