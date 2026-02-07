<?php

namespace App\Services\Reports;

use App\Models\GpsData;
use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DriverActivityReport extends BaseReport
{
    public function getType(): string
    {
        return 'driver_activity';
    }

    public function getColumns(): array
    {
        return [
            'driver_name' => 'Driver',
            'device_name' => 'Vehicle',
            'total_trips' => 'Total Trips',
            'total_distance' => 'Distance (km)',
            'total_driving_hours' => 'Driving Hours',
            'avg_speed' => 'Avg Speed (km/h)',
            'violations_count' => 'Violations',
            'driver_score' => 'Score',
        ];
    }

    public function generate(): Collection
    {
        // Group by device (assuming one driver per device)
        $query = GpsData::query()
            ->with('device')
            ->select(
                'device_id',
                DB::raw('COUNT(DISTINCT DATE(created_at)) as total_trips'),
                DB::raw('MAX(odometer) - MIN(odometer) as total_distance'),
                DB::raw('SUM(CASE WHEN ignition = 1 THEN 1 ELSE 0 END) / 60 as driving_hours'),
                DB::raw('AVG(speed) as avg_speed'),
                DB::raw('SUM(CASE WHEN speed > 80 THEN 1 ELSE 0 END) as violations_count')
            )
            ->groupBy('device_id');

        $this->applyDeviceFilter($query);
        $this->applyDateFilter($query);

        return collect($query->get())->map(function ($record) {
            // Calculate driver score (100 - violations penalty)
            $score = max(0, 100 - ($record->violations_count * 2));

            return [
                'driver_name' => $record->device->driver_name ?? 'Unknown',
                'device_name' => $record->device->name ?? 'Unknown',
                'vehicle_no' => $record->device->vehicle_no ?? 'N/A',
                'total_trips' => $record->total_trips,
                'total_distance' => round($record->total_distance, 2),
                'total_driving_hours' => round($record->driving_hours, 2),
                'avg_speed' => round($record->avg_speed, 2),
                'violations_count' => $record->violations_count,
                'driver_score' => round($score, 0),
            ];
        });
    }
}
