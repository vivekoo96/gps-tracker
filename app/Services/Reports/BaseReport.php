<?php

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Carbon\Carbon;

abstract class BaseReport
{
    protected $filters = [];
    protected $dateFrom;
    protected $dateTo;
    protected $devices = [];
    protected $groupBy = null;
    protected $sortBy = [];

    abstract public function generate(): Collection;
    abstract public function getColumns(): array;
    abstract public function getType(): string;

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        
        // Extract common filters
        $this->dateFrom = isset($filters['date_from']) ? Carbon::parse($filters['date_from']) : Carbon::now()->subDays(7);
        $this->dateTo = isset($filters['date_to']) ? Carbon::parse($filters['date_to']) : Carbon::now();
        $this->devices = $filters['devices'] ?? [];
        $this->groupBy = $filters['group_by'] ?? null;
        $this->sortBy = $filters['sort_by'] ?? [];

        return $this;
    }

    protected function applyDeviceFilter($query)
    {
        if (!empty($this->devices)) {
            $query->whereIn('device_id', $this->devices);
        }
        return $query;
    }

    protected function applyDateFilter($query, $dateColumn = 'created_at')
    {
        return $query->whereBetween($dateColumn, [$this->dateFrom, $this->dateTo]);
    }

    protected function formatDistance($meters)
    {
        return round($meters / 1000, 2); // Convert to km
    }

    protected function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    protected function calculateFuelConsumption($distance, $fuelUsed)
    {
        if ($distance == 0) return 0;
        return round(($fuelUsed / $distance) * 100, 2); // L/100km
    }
}
