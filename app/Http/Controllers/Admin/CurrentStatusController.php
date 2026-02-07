<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CurrentStatusController extends Controller
{
    /**
     * Display current status dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get zones and wards based on user role
        $zones = DB::table('zones')->get();
        $wards = DB::table('wards')->get();
        
        // If user has zone assignments, filter
        $assignedZones = DB::table('user_zone_assignments')
            ->where('user_id', $user->id)
            ->pluck('zone_id')
            ->toArray();
        
        if (!empty($assignedZones) && !$user->hasRole('admin')) {
            $zones = $zones->whereIn('id', $assignedZones);
        }
        
        return view('admin.status.current-status', compact('zones', 'wards'));
    }

    /**
     * Get current status data for zone/ward
     */
    public function getStatusData(Request $request)
    {
        $user = Auth::user();
        
        // Authorization check
        if (!$user->hasRole('admin')) {
            $assignedZones = DB::table('user_zone_assignments')
                ->where('user_id', $user->id)
                ->pluck('zone_id')
                ->toArray();
            
            if ($request->zone_id && !in_array($request->zone_id, $assignedZones)) {
                return response()->json(['error' => 'Unauthorized zone access'], 403);
            }
        }
        
        // Build device query
        $query = Device::query();
        
        if ($request->zone_id) {
            $query->where('zone_id', $request->zone_id);
        }
        
        if ($request->ward_id) {
            $query->where('ward_id', $request->ward_id);
        }
        
        $devices = $query->get();
        
        // Get latest positions for each device
        $vehicleData = [];
        $statistics = [
            'total_vehicles' => $devices->count(),
            'active' => 0,
            'idle' => 0,
            'offline' => 0,
            'collections_completed' => 0,
            'collections_total' => 0,
            'alerts' => 0
        ];
        
        foreach ($devices as $device) {
            $latestPosition = Position::where('device_id', $device->id)
                ->orderBy('fix_time', 'desc')
                ->first();
            
            if (!$latestPosition) {
                $statistics['offline']++;
                continue;
            }
            
            // Check if offline (no data in last 10 minutes)
            $isOffline = $latestPosition->fix_time->lt(Carbon::now()->subMinutes(10));
            
            if ($isOffline) {
                $statistics['offline']++;
                $status = 'offline';
                $statusColor = 'gray';
            } elseif ($latestPosition->speed > 5) {
                $statistics['active']++;
                $status = 'active';
                $statusColor = 'green';
            } else {
                $statistics['idle']++;
                $status = 'idle';
                $statusColor = 'yellow';
            }
            
            // Collection progress
            $statistics['collections_completed'] += $device->collections_today ?? 0;
            
            $vehicleData[] = [
                'id' => $device->id,
                'name' => $device->name,
                'vehicle_no' => $device->vehicle_no,
                'vehicle_type' => $device->vehicle_type ?? 'Unknown',
                'status' => $status,
                'status_color' => $statusColor,
                'speed' => round($latestPosition->speed, 1),
                'latitude' => $latestPosition->latitude,
                'longitude' => $latestPosition->longitude,
                'last_update' => $latestPosition->fix_time->diffForHumans(),
                'collections_today' => $device->collections_today ?? 0,
                'collection_route' => $device->collection_route ?? 'N/A'
            ];
        }
        
        // Get collection points for zone/ward
        $collectionPointsQuery = DB::table('collection_points');
        
        if ($request->zone_id) {
            $collectionPointsQuery->where('zone_id', $request->zone_id);
        }
        
        if ($request->ward_id) {
            $collectionPointsQuery->where('ward_id', $request->ward_id);
        }
        
        $collectionPoints = $collectionPointsQuery->get();
        $statistics['collections_total'] = $collectionPoints->count();
        
        // Calculate completion percentage
        if ($statistics['collections_total'] > 0) {
            $statistics['completion_percentage'] = round(
                ($collectionPoints->where('status', 'collected')->count() / $statistics['collections_total']) * 100,
                1
            );
        } else {
            $statistics['completion_percentage'] = 0;
        }
        
        return response()->json([
            'success' => true,
            'vehicles' => $vehicleData,
            'collection_points' => $collectionPoints->map(function($cp) {
                return [
                    'id' => $cp->id,
                    'name' => $cp->name,
                    'latitude' => $cp->latitude,
                    'longitude' => $cp->longitude,
                    'status' => $cp->status,
                    'expected_time' => $cp->expected_time,
                    'last_collected_at' => $cp->last_collected_at
                ];
            }),
            'statistics' => $statistics
        ]);
    }
}
