<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RouteReplayController extends Controller
{
    /**
     * Display route replay UI
     */
    public function index()
    {
        $devices = Device::all();
        $zones = DB::table('zones')->get();
        $wards = DB::table('wards')->get();
        
        return view('admin.reports.route-replay', compact('devices', 'zones', 'wards'));
    }

    /**
     * Get route data for replay
     */
    public function getRouteData(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);

        $device = Device::findOrFail($request->device_id);
        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);

        // Build query
        $query = Position::where('device_id', $device->id)
            ->whereBetween('fix_time', [$fromDate, $toDate]);

        // Time-based filtering
        if ($request->from_time) {
            $query->whereTime('fix_time', '>=', $request->from_time);
        }
        if ($request->to_time) {
            $query->whereTime('fix_time', '<=', $request->to_time);
        }

        $positions = $query->orderBy('fix_time', 'asc')->get();

        if ($positions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No route data found for the selected period'
            ]);
        }

        // Calculate statistics
        $statistics = $this->calculateStatistics($positions, $device);
        
        // Detect speed violations
        $speedLimit = $request->speed_limit ?? 60; // Default 60 km/h
        $violations = $this->detectSpeedViolations($positions, $speedLimit);
        
        // Detect stoppages
        $stoppageThreshold = $request->stoppage_threshold ?? 300; // 5 minutes
        $stoppages = $this->detectStoppages($positions, $stoppageThreshold);

        return response()->json([
            'success' => true,
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'vehicle_no' => $device->vehicle_no,
                'vehicle_type' => $device->vehicle_type ?? 'Unknown'
            ],
            'positions' => $positions->map(function($pos) {
                return [
                    'lat' => $pos->latitude,
                    'lng' => $pos->longitude,
                    'speed' => $pos->speed,
                    'time' => $pos->fix_time->format('Y-m-d H:i:s'),
                    'timestamp' => $pos->fix_time->timestamp
                ];
            }),
            'statistics' => $statistics,
            'violations' => $violations,
            'stoppages' => $stoppages
        ]);
    }

    /**
     * Calculate comprehensive statistics
     */
    protected function calculateStatistics($positions, $device)
    {
        if ($positions->isEmpty()) {
            return null;
        }

        $totalDistance = 0;
        $totalSpeed = 0;
        $maxSpeed = 0;
        $idleTime = 0;
        $movingTime = 0;

        // Calculate distance and speeds
        for ($i = 0; $i < $positions->count() - 1; $i++) {
            $pos1 = $positions[$i];
            $pos2 = $positions[$i + 1];
            
            // Distance
            $distance = $this->calculateDistance(
                $pos1->latitude, $pos1->longitude,
                $pos2->latitude, $pos2->longitude
            );
            $totalDistance += $distance;
            
            // Speed tracking
            $totalSpeed += $pos1->speed;
            if ($pos1->speed > $maxSpeed) {
                $maxSpeed = $pos1->speed;
            }
            
            // Time interval
            $interval = $pos1->fix_time->diffInSeconds($pos2->fix_time);
            
            // Idle vs moving time
            if ($pos1->speed == 0) {
                $idleTime += $interval;
            } else {
                $movingTime += $interval;
            }
        }

        $avgSpeed = $positions->count() > 0 ? $totalSpeed / $positions->count() : 0;
        $tripDuration = $positions->first()->fix_time->diffInSeconds($positions->last()->fix_time);

        return [
            'vehicle_type' => $device->vehicle_type ?? 'Unknown',
            'total_distance' => round($totalDistance, 2),
            'trip_duration' => $this->formatDuration($tripDuration),
            'trip_duration_seconds' => $tripDuration,
            'moving_time' => $this->formatDuration($movingTime),
            'idle_time' => $this->formatDuration($idleTime),
            'max_speed' => round($maxSpeed, 2),
            'avg_speed' => round($avgSpeed, 2),
            'start_time' => $positions->first()->fix_time->format('Y-m-d H:i:s'),
            'end_time' => $positions->last()->fix_time->format('Y-m-d H:i:s'),
            'start_location' => [
                'lat' => $positions->first()->latitude,
                'lng' => $positions->first()->longitude
            ],
            'end_location' => [
                'lat' => $positions->last()->latitude,
                'lng' => $positions->last()->longitude
            ]
        ];
    }

    /**
     * Detect speed violations
     */
    protected function detectSpeedViolations($positions, $speedLimit)
    {
        $violations = [];
        
        foreach ($positions as $pos) {
            if ($pos->speed > $speedLimit) {
                $violations[] = [
                    'time' => $pos->fix_time->format('Y-m-d H:i:s'),
                    'speed' => round($pos->speed, 2),
                    'limit' => $speedLimit,
                    'excess' => round($pos->speed - $speedLimit, 2),
                    'location' => [
                        'lat' => $pos->latitude,
                        'lng' => $pos->longitude
                    ]
                ];
            }
        }
        
        return $violations;
    }

    /**
     * Detect stoppages
     */
    protected function detectStoppages($positions, $thresholdSeconds)
    {
        $stoppages = [];
        $currentStoppage = null;
        
        foreach ($positions as $index => $pos) {
            if ($pos->speed == 0) {
                if (!$currentStoppage) {
                    // Start new stoppage
                    $currentStoppage = [
                        'start_time' => $pos->fix_time,
                        'start_index' => $index,
                        'location' => [
                            'lat' => $pos->latitude,
                            'lng' => $pos->longitude
                        ]
                    ];
                }
                $currentStoppage['end_time'] = $pos->fix_time;
                $currentStoppage['end_index'] = $index;
            } else {
                // Vehicle started moving
                if ($currentStoppage) {
                    $duration = $currentStoppage['start_time']->diffInSeconds($currentStoppage['end_time']);
                    
                    // Only record if duration exceeds threshold
                    if ($duration >= $thresholdSeconds) {
                        $stoppages[] = [
                            'start_time' => $currentStoppage['start_time']->format('Y-m-d H:i:s'),
                            'end_time' => $currentStoppage['end_time']->format('Y-m-d H:i:s'),
                            'duration' => $this->formatDuration($duration),
                            'duration_seconds' => $duration,
                            'location' => $currentStoppage['location']
                        ];
                    }
                    
                    $currentStoppage = null;
                }
            }
        }
        
        // Handle stoppage that extends to end of data
        if ($currentStoppage) {
            $duration = $currentStoppage['start_time']->diffInSeconds($currentStoppage['end_time']);
            if ($duration >= $thresholdSeconds) {
                $stoppages[] = [
                    'start_time' => $currentStoppage['start_time']->format('Y-m-d H:i:s'),
                    'end_time' => $currentStoppage['end_time']->format('Y-m-d H:i:s'),
                    'duration' => $this->formatDuration($duration),
                    'duration_seconds' => $duration,
                    'location' => $currentStoppage['location']
                ];
            }
        }
        
        return $stoppages;
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Format duration in human-readable format
     */
    protected function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $secs);
        } else {
            return sprintf('%ds', $secs);
        }
    }
}
