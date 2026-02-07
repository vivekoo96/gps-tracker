<?php

namespace App\Services\Reports;

use App\Models\GpsData;
use Illuminate\Support\Collection;

class SpeedViolationReport extends BaseReport
{
    public function getType(): string
    {
        return 'speed_violation';
    }

    public function getColumns(): array
    {
        return [
            'violation_time' => 'Time',
            'device_name' => 'Device/Vehicle',
            'location' => 'Location',
            'speed_limit' => 'Speed Limit (km/h)',
            'actual_speed' => 'Actual Speed (km/h)',
            'overspeed' => 'Overspeed (km/h)',
            'duration' => 'Duration',
        ];
    }

    public function generate(): Collection
    {
        $speedLimit = $this->filters['speed_limit'] ?? 80; // Default 80 km/h

        $query = GpsData::query()
            ->with('device')
            ->where('speed', '>', $speedLimit)
            ->orderBy('created_at', 'desc');

        $this->applyDeviceFilter($query);
        $this->applyDateFilter($query);

        $violations = $query->get();

        // Group consecutive violations
        $groupedViolations = $this->groupConsecutiveViolations($violations, $speedLimit);

        return collect($groupedViolations)->map(function ($violation) use ($speedLimit) {
            return [
                'violation_time' => $violation['start_time']->format('Y-m-d H:i:s'),
                'device_name' => $violation['device']->name ?? 'Unknown',
                'vehicle_no' => $violation['device']->vehicle_no ?? 'N/A',
                'location' => round($violation['latitude'], 6) . ', ' . round($violation['longitude'], 6),
                'speed_limit' => $speedLimit,
                'actual_speed' => round($violation['max_speed'], 2),
                'overspeed' => round($violation['max_speed'] - $speedLimit, 2),
                'duration' => $this->formatDuration($violation['duration']),
            ];
        });
    }

    private function groupConsecutiveViolations($violations, $speedLimit)
    {
        $grouped = [];
        $current = null;

        foreach ($violations as $violation) {
            if (!$current || 
                $current['device']->id != $violation->device_id ||
                $violation->created_at->diffInMinutes($current['end_time']) > 5) {
                
                if ($current) {
                    $grouped[] = $current;
                }

                $current = [
                    'device' => $violation->device,
                    'start_time' => $violation->created_at,
                    'end_time' => $violation->created_at,
                    'latitude' => $violation->latitude,
                    'longitude' => $violation->longitude,
                    'max_speed' => $violation->speed,
                    'duration' => 0,
                ];
            } else {
                $current['end_time'] = $violation->created_at;
                $current['max_speed'] = max($current['max_speed'], $violation->speed);
            }
        }

        if ($current) {
            $current['duration'] = $current['start_time']->diffInSeconds($current['end_time']);
            $grouped[] = $current;
        }

        return $grouped;
    }
}
