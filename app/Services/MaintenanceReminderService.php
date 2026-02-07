<?php

namespace App\Services;

use App\Models\MaintenanceReminder;
use App\Models\MaintenanceSchedule;
use App\Models\Device;
use Carbon\Carbon;

class MaintenanceReminderService
{
    protected $scheduleService;

    public function __construct(MaintenanceScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Generate reminders for all active devices
     */
    public function generateReminders($vendorId)
    {
        $devices = Device::where('vendor_id', $vendorId)
            ->where('status', 'active')
            ->get();

        $remindersCreated = 0;

        foreach ($devices as $device) {
            $remindersCreated += $this->generateDeviceReminders($device);
        }

        return $remindersCreated;
    }

    /**
     * Generate reminders for a specific device
     */
    public function generateDeviceReminders(Device $device)
    {
        $schedules = MaintenanceSchedule::where('device_id', $device->id)
            ->where('is_active', true)
            ->get();

        $remindersCreated = 0;
        $currentKm = $device->odometer ?? 0;
        $currentDate = now();

        foreach ($schedules as $schedule) {
            $lastRecord = $device->maintenanceRecords()
                ->where('schedule_id', $schedule->id)
                ->latest('service_date')
                ->first();

            // Check odometer-based reminders
            if ($schedule->interval_km) {
                $lastServiceKm = $lastRecord ? $lastRecord->odometer_reading : 0;
                $nextServiceKm = $lastServiceKm + $schedule->interval_km;
                $kmRemaining = $nextServiceKm - $currentKm;

                $reminderType = $this->determineReminderType($kmRemaining, $schedule->reminder_km_before);

                if ($reminderType) {
                    $this->createOrUpdateReminder($device, $schedule, [
                        'reminder_type' => $reminderType,
                        'due_km' => $nextServiceKm,
                        'current_km' => $currentKm,
                        'km_remaining' => $kmRemaining,
                        'message' => $this->generateMessage($schedule, $kmRemaining, 'km'),
                    ]);
                    $remindersCreated++;
                }
            }

            // Check time-based reminders
            if ($schedule->interval_days) {
                $lastServiceDate = $lastRecord ? $lastRecord->service_date : $device->created_at;
                $nextServiceDate = Carbon::parse($lastServiceDate)->addDays($schedule->interval_days);
                $daysRemaining = $currentDate->diffInDays($nextServiceDate, false);

                $reminderType = $this->determineReminderType($daysRemaining, $schedule->reminder_days_before);

                if ($reminderType) {
                    $this->createOrUpdateReminder($device, $schedule, [
                        'reminder_type' => $reminderType,
                        'due_date' => $nextServiceDate,
                        'current_km' => $currentKm,
                        'days_remaining' => $daysRemaining,
                        'message' => $this->generateMessage($schedule, $daysRemaining, 'days'),
                    ]);
                    $remindersCreated++;
                }
            }
        }

        return $remindersCreated;
    }

    /**
     * Determine reminder type based on remaining distance/time
     */
    protected function determineReminderType($remaining, $threshold)
    {
        if ($remaining < 0) {
            return 'overdue';
        } elseif ($remaining <= $threshold * 0.5) {
            return 'critical';
        } elseif ($remaining <= $threshold) {
            return 'upcoming';
        }

        return null;
    }

    /**
     * Generate reminder message
     */
    protected function generateMessage($schedule, $remaining, $unit)
    {
        if ($remaining < 0) {
            return "{$schedule->task_name} is overdue by " . abs($remaining) . " {$unit}";
        } else {
            return "{$schedule->task_name} is due in {$remaining} {$unit}";
        }
    }

    /**
     * Create or update reminder
     */
    protected function createOrUpdateReminder(Device $device, MaintenanceSchedule $schedule, array $data)
    {
        $reminder = MaintenanceReminder::updateOrCreate(
            [
                'device_id' => $device->id,
                'schedule_id' => $schedule->id,
                'is_acknowledged' => false,
            ],
            array_merge([
                'vendor_id' => $device->vendor_id,
                'task_name' => $schedule->task_name,
            ], $data)
        );

        return $reminder;
    }

    /**
     * Acknowledge a reminder
     */
    public function acknowledgeReminder($reminderId)
    {
        $reminder = MaintenanceReminder::findOrFail($reminderId);
        $reminder->acknowledge();

        return $reminder;
    }
}
