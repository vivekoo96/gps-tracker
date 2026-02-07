<?php

namespace App\Services\Reports;

use App\Models\GpsData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MileageReport extends BaseReport
{
    public function getType(): string
    {
        return 'mileage';
    }

    public function getColumns(): array
    {
        return [
            'date' => 'Date',
            'device_name' => 'Device/Vehicle',
            'starting_odometer' => 'Starting Odometer (km)',
            'ending_odometer' => 'Ending Odometer (km)',
            'distance_traveled' => 'Distance Traveled (km)',
        ];
    }

    public function generate(): Collection
    {
        $query = GpsData::query()
            ->with('device')
            ->select(
                'device_id',
                DB::raw('DATE(created_at) as date'),
                DB::raw('MIN(odometer) as starting_odometer'),
                DB::raw('MAX(odometer) as ending_odometer'),
                DB::raw('MAX(odometer) - MIN(odometer) as distance_traveled')
            )
            ->groupBy('device_id', DB::raw('DATE(created_at)'));

        $this->applyDeviceFilter($query);
        $this->applyDateFilter($query);

        return $query->get()->map(function ($record) {
            return [
                'date' => $record->date,
                'device_name' => $record->device->name ?? 'Unknown',
                'vehicle_no' => $record->device->vehicle_no ?? 'N/A',
                'starting_odometer' => round($record->starting_odometer, 2),
                'ending_odometer' => round($record->ending_odometer, 2),
                'distance_traveled' => round($record->distance_traveled, 2),
            ];
        });
    }
}
