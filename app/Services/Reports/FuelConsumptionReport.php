<?php

namespace App\Services\Reports;

use App\Models\GpsData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FuelConsumptionReport extends BaseReport
{
    public function getType(): string
    {
        return 'fuel_consumption';
    }

    public function getColumns(): array
    {
        return [
            'date' => 'Date',
            'device_name' => 'Device/Vehicle',
            'distance_km' => 'Distance (km)',
            'fuel_consumed' => 'Fuel Consumed (L)',
            'consumption_rate' => 'Rate (L/100km)',
            'refueling_events' => 'Refueling Events',
            'estimated_cost' => 'Est. Cost',
        ];
    }

    public function generate(): Collection
    {
        // This is a placeholder - actual implementation requires fuel sensor data
        // For now, estimate based on distance
        $query = GpsData::query()
            ->with('device')
            ->select(
                'device_id',
                DB::raw('DATE(created_at) as date'),
                DB::raw('MAX(odometer) - MIN(odometer) as distance_km')
            )
            ->groupBy('device_id', DB::raw('DATE(created_at)'));

        $this->applyDeviceFilter($query);
        $this->applyDateFilter($query);

        $fuelPrice = $this->filters['fuel_price'] ?? 1.5; // Default fuel price per liter

        return collect($query->get())->map(function ($record) use ($fuelPrice) {
            // Estimate fuel consumption (assuming 10 L/100km average)
            $avgConsumption = 10;
            $fuelConsumed = ($record->distance_km * $avgConsumption) / 100;
            $cost = $fuelConsumed * $fuelPrice;

            return [
                'date' => $record->date,
                'device_name' => $record->device->name ?? 'Unknown',
                'vehicle_no' => $record->device->vehicle_no ?? 'N/A',
                'distance_km' => round($record->distance_km, 2),
                'fuel_consumed' => round($fuelConsumed, 2),
                'consumption_rate' => $avgConsumption,
                'refueling_events' => 0, // Placeholder
                'estimated_cost' => round($cost, 2),
            ];
        });
    }
}
