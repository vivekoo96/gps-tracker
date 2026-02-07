<?php

namespace App\Services;

use App\Models\Device;
use App\Models\FuelEfficiencyReport;
use App\Models\FuelTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FuelEfficiencyService
{
    /**
     * Calculate fuel efficiency for a period
     */
    public function calculateEfficiency(Device $device, $period = 'daily', $date = null)
    {
        $date = $date ?? now();
        [$periodStart, $periodEnd] = $this->getPeriodDates($period, $date);

        // Get fuel transactions for period
        $transactions = FuelTransaction::where('device_id', $device->id)
            ->whereBetween('detected_at', [$periodStart, $periodEnd])
            ->get();

        // Calculate totals
        $totalFuelConsumed = $transactions->where('transaction_type', 'consumption')
            ->sum(function($t) { return abs($t->fuel_change); });
        
        $totalRefuelAmount = $transactions->where('transaction_type', 'refuel')
            ->sum('fuel_change');
        
        $totalRefuelCost = $transactions->where('transaction_type', 'refuel')
            ->sum('cost');
        
        $totalTheftAmount = $transactions->where('transaction_type', 'theft')
            ->sum(function($t) { return abs($t->fuel_change); });

        // Get distance from GPS data
        $totalDistance = $this->calculateDistance($device, $periodStart, $periodEnd);

        // Calculate efficiency (km/L)
        $averageEfficiency = $totalFuelConsumed > 0 ? $totalDistance / $totalFuelConsumed : 0;

        // Create or update report
        $report = FuelEfficiencyReport::updateOrCreate(
            [
                'vendor_id' => $device->vendor_id,
                'device_id' => $device->id,
                'period' => $period,
                'period_start' => $periodStart,
            ],
            [
                'period_end' => $periodEnd,
                'total_fuel_consumed' => $totalFuelConsumed,
                'total_distance' => $totalDistance,
                'average_efficiency' => $averageEfficiency,
                'total_refuel_amount' => $totalRefuelAmount,
                'total_refuel_cost' => $totalRefuelCost,
                'total_theft_amount' => $totalTheftAmount,
                'idle_fuel_consumed' => 0, // TODO: Calculate from idle time
                'trips_count' => 0, // TODO: Count trips
                'calculated_at' => now(),
            ]
        );

        return $report;
    }

    /**
     * Calculate distance from GPS data
     */
    protected function calculateDistance(Device $device, $startDate, $endDate)
    {
        // Get odometer readings
        $firstReading = DB::table('positions')
            ->where('device_id', $device->id)
            ->whereBetween('fix_time', [$startDate, $endDate])
            ->orderBy('fix_time', 'asc')
            ->first();

        $lastReading = DB::table('positions')
            ->where('device_id', $device->id)
            ->whereBetween('fix_time', [$startDate, $endDate])
            ->orderBy('fix_time', 'desc')
            ->first();

        if ($firstReading && $lastReading) {
            // Simple odometer difference
            return max(0, ($lastReading->odometer ?? 0) - ($firstReading->odometer ?? 0));
        }

        return 0;
    }

    /**
     * Get period date range
     */
    protected function getPeriodDates($period, $date)
    {
        return match($period) {
            'daily' => [
                Carbon::parse($date)->startOfDay(),
                Carbon::parse($date)->endOfDay(),
            ],
            'weekly' => [
                Carbon::parse($date)->startOfWeek(),
                Carbon::parse($date)->endOfWeek(),
            ],
            'monthly' => [
                Carbon::parse($date)->startOfMonth(),
                Carbon::parse($date)->endOfMonth(),
            ],
            default => [
                Carbon::parse($date)->startOfDay(),
                Carbon::parse($date)->endOfDay(),
            ],
        };
    }
}
