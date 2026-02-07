<?php

namespace App\Services\Reports;

use App\Models\GpsData;
use Illuminate\Support\Collection;

class IdleTimeReport extends BaseReport
{
    public function getType(): string
    {
        return 'idle_time';
    }

    public function getColumns(): array
    {
        return [
            'idle_start' => 'Idle Start',
            'idle_end' => 'Idle End',
            'device_name' => 'Device/Vehicle',
            'location' => 'Location',
            'duration' => 'Duration',
            'fuel_wasted' => 'Est. Fuel Wasted (L)',
        ];
    }

    public function generate(): Collection
    {
        $minIdleDuration = $this->filters['min_idle_minutes'] ?? 5; // Default 5 minutes

        $query = GpsData::query()
            ->with('device')
            ->where('speed', '<', 5) // Speed less than 5 km/h
            ->where('ignition', true) // Engine on
            ->orderBy('device_id')
            ->orderBy('created_at');

        $this->applyDeviceFilter($query);
        $this->applyDateFilter($query);

        $idlePoints = $query->get();

        // Group consecutive idle periods
        $idlePeriods = $this->groupIdlePeriods($idlePoints, $minIdleDuration);

        return collect($idlePeriods)->map(function ($period) {
            $fuelWasted = $this->estimateFuelWaste($period['duration']);

            return [
                'idle_start' => $period['start_time']->format('Y-m-d H:i:s'),
                'idle_end' => $period['end_time']->format('Y-m-d H:i:s'),
                'device_name' => $period['device']->name ?? 'Unknown',
                'vehicle_no' => $period['device']->vehicle_no ?? 'N/A',
                'location' => round($period['latitude'], 6) . ', ' . round($period['longitude'], 6),
                'duration' => $this->formatDuration($period['duration']),
                'fuel_wasted' => round($fuelWasted, 2),
            ];
        });
    }

    private function groupIdlePeriods($idlePoints, $minIdleDuration)
    {
        $periods = [];
        $current = null;

        foreach ($idlePoints as $point) {
            if (!$current || 
                $current['device']->id != $point->device_id ||
                $point->created_at->diffInMinutes($current['end_time']) > 5) {
                
                if ($current && $current['duration'] >= $minIdleDuration * 60) {
                    $periods[] = $current;
                }

                $current = [
                    'device' => $point->device,
                    'start_time' => $point->created_at,
                    'end_time' => $point->created_at,
                    'latitude' => $point->latitude,
                    'longitude' => $point->longitude,
                    'duration' => 0,
                ];
            } else {
                $current['end_time'] = $point->created_at;
                $current['duration'] = $current['start_time']->diffInSeconds($current['end_time']);
            }
        }

        if ($current && $current['duration'] >= $minIdleDuration * 60) {
            $periods[] = $current;
        }

        return $periods;
    }

    private function estimateFuelWaste($durationSeconds)
    {
        // Estimate: 0.8 liters per hour of idling
        $idleConsumptionPerHour = 0.8;
        $hours = $durationSeconds / 3600;
        return $hours * $idleConsumptionPerHour;
    }
}
