<?php

namespace App\Services\Reports;

use App\Models\GpsData;
use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TripReport extends BaseReport
{
    public function getType(): string
    {
        return 'trip';
    }

    public function getColumns(): array
    {
        return [
            'trip_id' => 'Trip ID',
            'device_name' => 'Device/Vehicle',
            'start_time' => 'Start Time',
            'start_location' => 'Start Location',
            'end_time' => 'End Time',
            'end_location' => 'End Location',
            'distance_km' => 'Distance (km)',
            'duration' => 'Duration',
            'avg_speed' => 'Avg Speed (km/h)',
            'max_speed' => 'Max Speed (km/h)',
            'idle_time' => 'Idle Time',
        ];
    }

    public function generate(): Collection
    {
        // Get GPS data grouped by trips (ignition on/off events)
        $query = GpsData::query()
            ->with('device')
            ->select('device_id', 'latitude', 'longitude', 'speed', 'created_at', 'ignition')
            ->orderBy('device_id')
            ->orderBy('created_at');

        $this->applyDeviceFilter($query);
        $this->applyDateFilter($query);

        $gpsData = $query->get();

        // Group data into trips
        $trips = $this->identifyTrips($gpsData);

        return collect($trips)->map(function ($trip, $index) {
            return [
                'trip_id' => $index + 1,
                'device_name' => $trip['device']->name ?? 'Unknown',
                'vehicle_no' => $trip['device']->vehicle_no ?? 'N/A',
                'start_time' => $trip['start_time']->format('Y-m-d H:i:s'),
                'start_location' => $this->formatLocation($trip['start_lat'], $trip['start_lng']),
                'end_time' => $trip['end_time']->format('Y-m-d H:i:s'),
                'end_location' => $this->formatLocation($trip['end_lat'], $trip['end_lng']),
                'distance_km' => $trip['distance'],
                'duration' => $this->formatDuration($trip['duration']),
                'avg_speed' => round($trip['avg_speed'], 2),
                'max_speed' => round($trip['max_speed'], 2),
                'idle_time' => $this->formatDuration($trip['idle_time']),
            ];
        });
    }

    private function identifyTrips($gpsData)
    {
        $trips = [];
        $currentTrip = null;
        $lastPoint = null;

        foreach ($gpsData as $point) {
            // Start new trip when ignition turns on
            if ($point->ignition && !$currentTrip) {
                $currentTrip = [
                    'device' => $point->device,
                    'start_time' => $point->created_at,
                    'start_lat' => $point->latitude,
                    'start_lng' => $point->longitude,
                    'points' => [],
                    'distance' => 0,
                    'max_speed' => 0,
                    'idle_time' => 0,
                ];
            }

            if ($currentTrip) {
                $currentTrip['points'][] = $point;
                $currentTrip['max_speed'] = max($currentTrip['max_speed'], $point->speed);

                // Calculate distance from last point
                if ($lastPoint && $lastPoint->device_id == $point->device_id) {
                    $distance = $this->calculateDistance(
                        $lastPoint->latitude,
                        $lastPoint->longitude,
                        $point->latitude,
                        $point->longitude
                    );
                    $currentTrip['distance'] += $distance;

                    // Detect idle (speed < 5 km/h)
                    if ($point->speed < 5) {
                        $timeDiff = $point->created_at->diffInSeconds($lastPoint->created_at);
                        $currentTrip['idle_time'] += $timeDiff;
                    }
                }

                // End trip when ignition turns off
                if (!$point->ignition) {
                    $currentTrip['end_time'] = $point->created_at;
                    $currentTrip['end_lat'] = $point->latitude;
                    $currentTrip['end_lng'] = $point->longitude;
                    $currentTrip['duration'] = $currentTrip['start_time']->diffInSeconds($currentTrip['end_time']);
                    $currentTrip['avg_speed'] = $currentTrip['duration'] > 0 
                        ? ($currentTrip['distance'] / ($currentTrip['duration'] / 3600)) 
                        : 0;

                    $trips[] = $currentTrip;
                    $currentTrip = null;
                }
            }

            $lastPoint = $point;
        }

        // Close any open trip
        if ($currentTrip && $lastPoint) {
            $currentTrip['end_time'] = $lastPoint->created_at;
            $currentTrip['end_lat'] = $lastPoint->latitude;
            $currentTrip['end_lng'] = $lastPoint->longitude;
            $currentTrip['duration'] = $currentTrip['start_time']->diffInSeconds($currentTrip['end_time']);
            $currentTrip['avg_speed'] = $currentTrip['duration'] > 0 
                ? ($currentTrip['distance'] / ($currentTrip['duration'] / 3600)) 
                : 0;
            $trips[] = $currentTrip;
        }

        return $trips;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    private function formatLocation($lat, $lng)
    {
        return round($lat, 6) . ', ' . round($lng, 6);
    }
}
