<?php

namespace App\Services;

use App\Models\MaintenanceRecord;
use Carbon\Carbon;

class MaintenanceCostService
{
    /**
     * Calculate total maintenance cost for a period
     */
    public function calculateTotalCost($vendorId, $startDate = null, $endDate = null)
    {
        $query = MaintenanceRecord::where('vendor_id', $vendorId);

        if ($startDate) {
            $query->where('service_date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->where('service_date', '<=', Carbon::parse($endDate));
        }

        return $query->sum('cost');
    }

    /**
     * Get cost breakdown by category
     */
    public function getCostBreakdown($vendorId, $startDate = null, $endDate = null)
    {
        $query = MaintenanceRecord::where('vendor_id', $vendorId);

        if ($startDate) {
            $query->where('service_date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->where('service_date', '<=', Carbon::parse($endDate));
        }

        return $query->selectRaw('category, SUM(cost) as total_cost, COUNT(*) as service_count')
            ->groupBy('category')
            ->get();
    }

    /**
     * Calculate cost per kilometer for a device
     */
    public function getCostPerKm($deviceId, $startDate = null, $endDate = null)
    {
        $query = MaintenanceRecord::where('device_id', $deviceId);

        if ($startDate) {
            $query->where('service_date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->where('service_date', '<=', Carbon::parse($endDate));
        }

        $totalCost = $query->sum('cost');
        $records = $query->orderBy('service_date')->get();

        if ($records->isEmpty()) {
            return 0;
        }

        $firstOdometer = $records->first()->odometer_reading;
        $lastOdometer = $records->last()->odometer_reading;
        $totalDistance = $lastOdometer - $firstOdometer;

        if ($totalDistance <= 0) {
            return 0;
        }

        return $totalCost / $totalDistance;
    }

    /**
     * Predict future maintenance costs
     */
    public function predictMaintenanceCost($vendorId, $months = 3)
    {
        // Get last 6 months of data
        $startDate = now()->subMonths(6);
        $records = MaintenanceRecord::where('vendor_id', $vendorId)
            ->where('service_date', '>=', $startDate)
            ->get();

        if ($records->isEmpty()) {
            return 0;
        }

        $averageMonthlyCost = $records->sum('cost') / 6;

        return $averageMonthlyCost * $months;
    }

    /**
     * Compare maintenance costs across vehicles
     */
    public function compareVehicleCosts($vendorId, $startDate = null, $endDate = null)
    {
        $query = MaintenanceRecord::with('device')
            ->where('vendor_id', $vendorId);

        if ($startDate) {
            $query->where('service_date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->where('service_date', '<=', Carbon::parse($endDate));
        }

        return $query->selectRaw('device_id, SUM(cost) as total_cost, COUNT(*) as service_count, AVG(cost) as avg_cost')
            ->groupBy('device_id')
            ->orderByDesc('total_cost')
            ->get();
    }

    /**
     * Get monthly cost trend
     */
    public function getMonthlyCostTrend($vendorId, $months = 12)
    {
        $startDate = now()->subMonths($months);

        return MaintenanceRecord::where('vendor_id', $vendorId)
            ->where('service_date', '>=', $startDate)
            ->selectRaw('YEAR(service_date) as year, MONTH(service_date) as month, SUM(cost) as total_cost')
            ->groupByRaw('YEAR(service_date), MONTH(service_date)')
            ->orderByRaw('YEAR(service_date), MONTH(service_date)')
            ->get();
    }
}
