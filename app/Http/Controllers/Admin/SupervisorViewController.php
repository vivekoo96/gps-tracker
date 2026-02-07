<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisorViewController extends Controller
{
    /**
     * Display supervisor's citizen complaint view
     */
    public function index()
    {
        $zones = DB::table('zones')->get();
        $wards = DB::table('wards')->get();
        
        return view('admin.supervisor.citizen-complaints', compact('zones', 'wards'));
    }
    
    /**
     * Search collection point by location/society name
     */
    public function searchLocation(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:3'
        ]);
        
        $searchTerm = $request->search;
        
        $locations = DB::table('collection_points')
            ->leftJoin('zones', 'collection_points.zone_id', '=', 'zones.id')
            ->leftJoin('wards', 'collection_points.ward_id', '=', 'wards.id')
            ->select(
                'collection_points.*',
                'zones.name as zone_name',
                'wards.name as ward_name'
            )
            ->where(function($query) use ($searchTerm) {
                $query->where('collection_points.name', 'like', "%{$searchTerm}%")
                      ->orWhere('collection_points.address', 'like', "%{$searchTerm}%")
                      ->orWhere('collection_points.society_name', 'like', "%{$searchTerm}%");
            })
            ->limit(20)
            ->get();
        
        return response()->json([
            'success' => true,
            'locations' => $locations
        ]);
    }
    
    /**
     * Get collection details for a specific location
     */
    public function getCollectionDetails($locationId)
    {
        $location = DB::table('collection_points')
            ->leftJoin('zones', 'collection_points.zone_id', '=', 'zones.id')
            ->leftJoin('wards', 'collection_points.ward_id', '=', 'wards.id')
            ->select(
                'collection_points.*',
                'zones.name as zone_name',
                'wards.name as ward_name'
            )
            ->where('collection_points.id', $locationId)
            ->first();
        
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found'
            ], 404);
        }
        
        // Get collection history (last 30 days)
        $collectionHistory = DB::table('collection_logs')
            ->leftJoin('devices', 'collection_logs.device_id', '=', 'devices.id')
            ->select(
                'collection_logs.*',
                'devices.vehicle_no',
                'devices.name as vehicle_name'
            )
            ->where('collection_logs.collection_point_id', $locationId)
            ->where('collection_logs.collected_at', '>=', now()->subDays(30))
            ->orderBy('collection_logs.collected_at', 'desc')
            ->get();
        
        // Calculate statistics
        $lastCollected = $collectionHistory->first();
        $scheduledTime = $location->scheduled_time ?? '09:00:00';
        
        $hoursUnserved = null;
        if ($lastCollected) {
            $hoursUnserved = now()->diffInHours($lastCollected->collected_at);
        }
        
        // Get today's scheduled collection
        $todayScheduled = DB::table('collection_schedules')
            ->where('collection_point_id', $locationId)
            ->where('scheduled_date', today())
            ->first();
        
        // Check if collected today
        $collectedToday = $collectionHistory->where('collected_at', '>=', today())->first();
        
        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address,
                'society_name' => $location->society_name ?? 'N/A',
                'zone' => $location->zone_name,
                'ward' => $location->ward_name,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude
            ],
            'scheduled_time' => $scheduledTime,
            'last_collected' => $lastCollected ? [
                'date_time' => $lastCollected->collected_at,
                'vehicle_no' => $lastCollected->vehicle_no,
                'vehicle_name' => $lastCollected->vehicle_name,
                'hours_ago' => $hoursUnserved
            ] : null,
            'today_status' => [
                'scheduled' => $todayScheduled ? true : false,
                'scheduled_time' => $todayScheduled->scheduled_time ?? $scheduledTime,
                'collected' => $collectedToday ? true : false,
                'actual_time' => $collectedToday->collected_at ?? null,
                'delay_minutes' => $this->calculateDelay($todayScheduled, $collectedToday)
            ],
            'collection_history' => $collectionHistory->map(function($log) {
                return [
                    'date' => date('Y-m-d', strtotime($log->collected_at)),
                    'time' => date('H:i:s', strtotime($log->collected_at)),
                    'vehicle_no' => $log->vehicle_no,
                    'vehicle_name' => $log->vehicle_name
                ];
            })
        ]);
    }
    
    /**
     * Calculate delay between scheduled and actual collection
     */
    protected function calculateDelay($scheduled, $actual)
    {
        if (!$scheduled || !$actual) {
            return null;
        }
        
        $scheduledDateTime = date('Y-m-d') . ' ' . $scheduled->scheduled_time;
        $actualDateTime = $actual->collected_at;
        
        $scheduledTimestamp = strtotime($scheduledDateTime);
        $actualTimestamp = strtotime($actualDateTime);
        
        $delayMinutes = ($actualTimestamp - $scheduledTimestamp) / 60;
        
        return round($delayMinutes);
    }
    
    /**
     * Get collection points by zone/ward
     */
    public function getLocationsByArea(Request $request)
    {
        $query = DB::table('collection_points')
            ->leftJoin('zones', 'collection_points.zone_id', '=', 'zones.id')
            ->leftJoin('wards', 'collection_points.ward_id', '=', 'wards.id')
            ->select(
                'collection_points.*',
                'zones.name as zone_name',
                'wards.name as ward_name'
            );
        
        if ($request->zone_id) {
            $query->where('collection_points.zone_id', $request->zone_id);
        }
        
        if ($request->ward_id) {
            $query->where('collection_points.ward_id', $request->ward_id);
        }
        
        $locations = $query->orderBy('collection_points.name')->get();
        
        return response()->json([
            'success' => true,
            'locations' => $locations
        ]);
    }
}
