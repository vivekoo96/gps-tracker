<?php

namespace App\Services;

use App\Models\MaintenanceSchedule;
use App\Models\Device;
use Carbon\Carbon;

class MaintenanceScheduleService
{
    /**
     * Calculate next service date/km for a schedule
     */
    public function calculateNextService(MaintenanceSchedule $schedule, $currentKm, $lastServiceDate = null)
    {
        $nextServiceKm = null;
        $nextServiceDate = null;

        if ($schedule->interval_type === 'odometer' || $schedule->interval_type === 'both') {
            if ($schedule->interval_km) {
                $nextServiceKm = $currentKm + $schedule->interval_km;
            }
        }

        if ($schedule->interval_type === 'time' || $schedule->interval_type === 'both') {
            if ($schedule->interval_days) {
                $baseDate = $lastServiceDate ? Carbon::parse($lastServiceDate) : now();
                $nextServiceDate = $baseDate->addDays($schedule->interval_days);
            }
        }

        return [
            'next_service_km' => $nextServiceKm,
            'next_service_date' => $nextServiceDate,
        ];
    }

    /**
     * Check which services are due for a device
     */
    public function checkDueServices(Device $device)
    {
        $currentKm = $device->odometer ?? 0;
        $currentDate = now();

        $schedules = MaintenanceSchedule::where('device_id', $device->id)
            ->where('is_active', true)
            ->get();

        $dueServices = [];

        foreach ($schedules as $schedule) {
            $isDue = false;

            // Check odometer-based
            if ($schedule->interval_type === 'odometer' || $schedule->interval_type === 'both') {
                $lastRecord = $device->maintenanceRecords()
                    ->where('schedule_id', $schedule->id)
                    ->latest('service_date')
                    ->first();

                $lastServiceKm = $lastRecord ? $lastRecord->odometer_reading : 0;
                $kmSinceService = $currentKm - $lastServiceKm;

                if ($kmSinceService >= $schedule->interval_km) {
                    $isDue = true;
                }
            }

            // Check time-based
            if ($schedule->interval_type === 'time' || $schedule->interval_type === 'both') {
                $lastRecord = $device->maintenanceRecords()
                    ->where('schedule_id', $schedule->id)
                    ->latest('service_date')
                    ->first();

                $lastServiceDate = $lastRecord ? $lastRecord->service_date : $device->created_at;
                $daysSinceService = $currentDate->diffInDays($lastServiceDate);

                if ($daysSinceService >= $schedule->interval_days) {
                    $isDue = true;
                }
            }

            if ($isDue) {
                $dueServices[] = $schedule;
            }
        }

        return $dueServices;
    }

    /**
     * Get upcoming maintenance within threshold
     */
    public function getUpcomingMaintenance(Device $device, $kmThreshold = 500, $daysThreshold = 7)
    {
        $currentKm = $device->odometer ?? 0;
        $currentDate = now();

        $schedules = MaintenanceSchedule::where('device_id', $device->id)
            ->where('is_active', true)
            ->get();

        $upcoming = [];

        foreach ($schedules as $schedule) {
            $lastRecord = $device->maintenanceRecords()
                ->where('schedule_id', $schedule->id)
                ->latest('service_date')
                ->first();

            // Check odometer
            if ($schedule->interval_km) {
                $lastServiceKm = $lastRecord ? $lastRecord->odometer_reading : 0;
                $nextServiceKm = $lastServiceKm + $schedule->interval_km;
                $kmRemaining = $nextServiceKm - $currentKm;

                if ($kmRemaining > 0 && $kmRemaining <= $kmThreshold) {
                    $upcoming[] = [
                        'schedule' => $schedule,
                        'type' => 'odometer',
                        'remaining' => $kmRemaining,
                        'unit' => 'km',
                    ];
                }
            }

            // Check time
            if ($schedule->interval_days) {
                $lastServiceDate = $lastRecord ? $lastRecord->service_date : $device->created_at;
                $nextServiceDate = Carbon::parse($lastServiceDate)->addDays($schedule->interval_days);
                $daysRemaining = $currentDate->diffInDays($nextServiceDate, false);

                if ($daysRemaining > 0 && $daysRemaining <= $daysThreshold) {
                    $upcoming[] = [
                        'schedule' => $schedule,
                        'type' => 'time',
                        'remaining' => $daysRemaining,
                        'unit' => 'days',
                    ];
                }
            }
        }

        return $upcoming;
    }
}
