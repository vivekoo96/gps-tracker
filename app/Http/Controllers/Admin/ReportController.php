<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Position;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function dailyDistance(Request $request)
    {
        $devices = Device::all();
        $reportData = collect();
        
        // If form submitted
        if ($request->has('from_date') && $request->has('to_date')) {
            $startDate = Carbon::parse($request->from_date)->startOfDay();
            $endDate = Carbon::parse($request->to_date)->endOfDay();
            $selectedDeviceIds = $request->device_ids ?? $devices->pluck('id')->toArray();

            // Loop through each day in range
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

            foreach ($selectedDeviceIds as $deviceId) {
                foreach ($period as $date) {
                    // Optimized: Fetch only lat/lon for specific device and day
                    $positions = Position::where('device_id', $deviceId)
                        ->whereBetween('fix_time', [$date->startOfDay(), $date->endOfDay()])
                        ->orderBy('fix_time', 'asc')
                        ->select('latitude', 'longitude')
                        ->get();

                    $totalDistance = 0;
                    if ($positions->count() > 1) {
                        for ($i = 0; $i < $positions->count() - 1; $i++) {
                            $totalDistance += $this->calculateDistance(
                                $positions[$i]->latitude, $positions[$i]->longitude,
                                $positions[$i+1]->latitude, $positions[$i+1]->longitude
                            );
                        }
                    }

                    if ($totalDistance > 0 || $request->show_zeros) {
                         $device = $devices->find($deviceId);
                         $reportData->push([
                             'date' => $date->format('Y-m-d'),
                             'device_name' => $device->name,
                             'vehicle_no' => $device->vehicle_no,
                             'distance_km' => round($totalDistance, 2)
                         ]);
                    }
                }
            }
        }

        return view('admin.reports.daily_distance', compact('devices', 'reportData'));
    }

    public function trips(Request $request) 
    {
        $devices = Device::all();
        $trips = collect();

        if ($request->has('from_date') && $request->has('to_date') && $request->has('device_id')) {
            $startDate = Carbon::parse($request->from_date);
            $endDate = Carbon::parse($request->to_date);
            $device = Device::find($request->device_id);

            $positions = Position::where('device_id', $device->id)
                ->whereBetween('fix_time', [$startDate, $endDate])
                ->orderBy('fix_time', 'asc')
                ->get();

            // Simple Trip Logic: Ignition ON -> OFF
            $currentTrip = null;
            
            foreach ($positions as $pos) {
                $ignition = $pos->ignition; // Assuming boolean or 1/0

                // Trip Start (Ignition turned ON)
                if ($ignition && !$currentTrip) {
                    $currentTrip = [
                        'start_time' => $pos->fix_time,
                        'start_lat' => $pos->latitude,
                        'start_lon' => $pos->longitude,
                        'distance' => 0,
                        'positions' => [$pos]
                    ];
                }
                
                // Trip Continue
                if ($ignition && $currentTrip) {
                    $lastPos = end($currentTrip['positions']);
                    $currentTrip['distance'] += $this->calculateDistance(
                        $lastPos->latitude, $lastPos->longitude,
                        $pos->latitude, $pos->longitude
                    );
                    $currentTrip['positions'][] = $pos;
                }

                // Trip End (Ignition turned OFF)
                if (!$ignition && $currentTrip) {
                    $currentTrip['end_time'] = $pos->fix_time;
                    $currentTrip['end_lat'] = $pos->latitude;
                    $currentTrip['end_lon'] = $pos->longitude;
                    $currentTrip['duration'] = Carbon::parse($currentTrip['start_time'])->diffForHumans($pos->fix_time, true);
                    
                    $trips->push($currentTrip);
                    $currentTrip = null;
                }
            }
        }

        return view('admin.reports.trips', compact('devices', 'trips'));
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        return $distance;
    }

    public function geofences(Request $request) 
    {
        $devices = Device::all();
        $geofences = \App\Models\Geofence::all();
        $events = collect();

        if ($request->has('from_date') && $request->has('to_date')) {
            $startDate = Carbon::parse($request->from_date);
            $endDate = Carbon::parse($request->to_date);
            
            $query = \Illuminate\Support\Facades\DB::table('geofence_events')
                ->join('devices', 'geofence_events.device_id', '=', 'devices.id')
                ->join('geofences', 'geofence_events.geofence_id', '=', 'geofences.id')
                ->whereBetween('event_time', [$startDate, $endDate])
                ->select(
                    'geofence_events.*', 
                    'devices.name as device_name', 'devices.vehicle_no',
                    'geofences.name as geofence_name'
                )
                ->orderBy('event_time', 'desc');

            if ($request->device_id) {
                $query->where('geofence_events.device_id', $request->device_id);
            }

            if ($request->geofence_id) {
                $query->where('geofence_events.geofence_id', $request->geofence_id);
            }

            $events = $query->get();
        }

        return view('admin.reports.geofences', compact('devices', 'geofences', 'events'));
    }
    public function engineUtilization(Request $request)
    {
        $devices = Device::all();
        return view('admin.reports.engine_utilization', compact('devices'));
    }
}
