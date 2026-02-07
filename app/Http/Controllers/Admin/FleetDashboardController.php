<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FleetDashboardController extends Controller
{
    /**
     * Display fleet dashboard
     */
    public function index()
    {
        $zones = DB::table('zones')->get();
        $wards = DB::table('wards')->get();
        
        return view('admin.dashboard.fleet', compact('zones', 'wards'));
    }

    /**
     * Get fleet data with filters
     */
    public function getFleetData(Request $request)
    {
        $query = Device::query();
        
        // Apply filters
        if ($request->vehicle_no) {
            $query->where('vehicle_no', 'like', '%' . $request->vehicle_no . '%');
        }
        if ($request->zone_id) {
            $query->where('zone_id', $request->zone_id);
        }
        if ($request->ward_id) {
            $query->where('ward_id', $request->ward_id);
        }
        
        $devices = $query->with(['zone', 'ward'])->get();
        
        $fleetData = [];
        $statistics = [
            'total' => $devices->count(),
            'running' => 0,
            'idle' => 0,
            'standby' => 0,
            'no_communication' => 0
        ];
        
        foreach ($devices as $device) {
            $latestPosition = Position::where('device_id', $device->id)
                ->orderBy('fix_time', 'desc')
                ->first();
            
            if (!$latestPosition) {
                $statistics['no_communication']++;
                continue;
            }
            
            // Determine status
            $timeDiff = $latestPosition->fix_time->diffInMinutes(now());
            $isDisconnected = $timeDiff > 10;
            
            if ($isDisconnected) {
                $status = 'no_communication';
                $statusColor = 'gray';
                $statistics['no_communication']++;
            } elseif ($latestPosition->speed > 5) {
                $status = 'running';
                $statusColor = 'green';
                $statistics['running']++;
            } elseif ($latestPosition->speed > 0) {
                $status = 'idle';
                $statusColor = 'yellow';
                $statistics['idle']++;
            } else {
                $status = 'standby';
                $statusColor = 'blue';
                $statistics['standby']++;
            }
            
            // Determine speed indicator
            $speedLimit = $device->zone->speed_limit ?? 60;
            $alarmingSpeed = $speedLimit * 0.9; // 90% of limit
            
            if ($latestPosition->speed > $speedLimit) {
                $speedIndicator = 'above_alarming';
                $speedColor = 'red';
            } elseif ($latestPosition->speed > $alarmingSpeed) {
                $speedIndicator = 'alarming';
                $speedColor = 'orange';
            } else {
                $speedIndicator = 'normal';
                $speedColor = 'green';
            }
            
            // Get transfer station
            $transferStation = null;
            if ($device->transfer_station_id) {
                $transferStation = DB::table('transfer_stations')
                    ->where('id', $device->transfer_station_id)
                    ->first();
            }
            
            // Apply status filter if provided
            if ($request->status && $request->status !== $status) {
                continue;
            }
            
            $vehicleData = [
                'id' => $device->id,
                'vehicle_no' => $device->vehicle_no,
                'vehicle_type' => $device->vehicle_type ?? 'Unknown',
                'zone' => $device->zone->name ?? 'N/A',
                'ward' => $device->ward->name ?? 'N/A',
                'transfer_station' => $transferStation->name ?? 'N/A',
                'current_location' => $this->formatLocation($latestPosition->latitude, $latestPosition->longitude),
                'latitude' => $latestPosition->latitude,
                'longitude' => $latestPosition->longitude,
                'speed' => round($latestPosition->speed, 1),
                'status' => $status,
                'status_color' => $statusColor,
                'speed_indicator' => $speedIndicator,
                'speed_color' => $speedColor,
                'last_update' => $latestPosition->fix_time->diffForHumans(),
                'last_update_full' => $latestPosition->fix_time->format('Y-m-d H:i:s'),
                'is_disconnected' => $isDisconnected
            ];
            
            $fleetData[] = $vehicleData;
        }
        
        return response()->json([
            'success' => true,
            'fleet' => $fleetData,
            'statistics' => $statistics
        ]);
    }

    /**
     * Get single vehicle location for map
     */
    public function getVehicleLocation($vehicleId)
    {
        $device = Device::findOrFail($vehicleId);
        
        $latestPosition = Position::where('device_id', $device->id)
            ->orderBy('fix_time', 'desc')
            ->first();
        
        if (!$latestPosition) {
            return response()->json(['error' => 'No position data'], 404);
        }
        
        return response()->json([
            'success' => true,
            'vehicle' => [
                'name' => $device->name,
                'vehicle_no' => $device->vehicle_no,
                'latitude' => $latestPosition->latitude,
                'longitude' => $latestPosition->longitude,
                'speed' => round($latestPosition->speed, 1),
                'last_update' => $latestPosition->fix_time->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Format location coordinates
     */
    private function formatLocation($lat, $lng)
    {
        return number_format($lat, 6) . ', ' . number_format($lng, 6);
    }
}
