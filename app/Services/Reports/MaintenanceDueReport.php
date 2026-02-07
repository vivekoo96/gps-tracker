<?php

namespace App\Services\Reports;

use App\Models\MaintenanceRecord;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class MaintenanceDueReport extends BaseReport
{
    public function getType(): string
    {
        return 'maintenance_due';
    }

    public function getColumns(): array
    {
        return [
            'device_name' => 'Vehicle',
            'service_type' => 'Service Type',
            'last_service_date' => 'Last Service',
            'last_service_mileage' => 'Last Service Mileage',
            'next_due_date' => 'Next Due Date',
            'next_due_mileage' => 'Next Due Mileage',
            'days_until_due' => 'Days Until Due',
            'km_until_due' => 'KM Until Due',
            'status' => 'Status',
        ];
    }

    public function generate(): Collection
    {
        // This assumes you have maintenance_schedules table
        // For now, return empty collection - you can implement when maintenance module is ready
        return collect([]);
    }
}
