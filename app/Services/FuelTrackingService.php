<?php

namespace App\Services;

use App\Models\Device;
use App\Models\FuelTransaction;
use App\Models\FuelAlert;
use Carbon\Carbon;

class FuelTrackingService
{
    // Thresholds
    const REFUEL_THRESHOLD = 10; // % increase
    const THEFT_THRESHOLD = 10; // % decrease
    const LOW_FUEL_THRESHOLD = 15; // %
    const CRITICAL_FUEL_THRESHOLD = 5; // %
    const TIME_WINDOW_MINUTES = 5;

    /**
     * Process fuel level data from GPS device
     */
    public function processFuelData(Device $device, $fuelLevel, $odometer = null, $latitude = null, $longitude = null)
    {
        // Get previous fuel reading
        $previous = FuelTransaction::where('device_id', $device->id)
            ->latest('detected_at')
            ->first();

        $fuelBefore = $previous ? $previous->fuel_after : $fuelLevel;
        $fuelChange = $fuelLevel - $fuelBefore;

        // Detect refueling
        if ($this->isRefueling($fuelBefore, $fuelLevel, $previous)) {
            $this->createTransaction([
                'vendor_id' => $device->vendor_id,
                'device_id' => $device->id,
                'transaction_type' => 'refuel',
                'fuel_before' => $fuelBefore,
                'fuel_after' => $fuelLevel,
                'fuel_change' => $fuelChange,
                'odometer' => $odometer,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'detected_at' => now(),
            ]);
        }
        // Detect fuel theft
        elseif ($this->isTheft($fuelBefore, $fuelLevel, $previous, $device)) {
            $transaction = $this->createTransaction([
                'vendor_id' => $device->vendor_id,
                'device_id' => $device->id,
                'transaction_type' => 'theft',
                'fuel_before' => $fuelBefore,
                'fuel_after' => $fuelLevel,
                'fuel_change' => $fuelChange,
                'odometer' => $odometer,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'detected_at' => now(),
            ]);

            // Send theft alert
            $this->sendTheftAlert($device, $transaction);
        }
        // Normal consumption
        elseif ($fuelChange < 0) {
            $this->createTransaction([
                'vendor_id' => $device->vendor_id,
                'device_id' => $device->id,
                'transaction_type' => 'consumption',
                'fuel_before' => $fuelBefore,
                'fuel_after' => $fuelLevel,
                'fuel_change' => $fuelChange,
                'odometer' => $odometer,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'detected_at' => now(),
            ]);
        }

        // Update device fuel level
        $this->updateFuelLevel($device, $fuelLevel);

        // Check for low fuel alerts
        $this->checkLowFuel($device, $fuelLevel);
    }

    /**
     * Detect refueling event
     */
    protected function isRefueling($fuelBefore, $fuelAfter, $previous)
    {
        $percentChange = (($fuelAfter - $fuelBefore) / max($fuelBefore, 1)) * 100;

        if ($percentChange >= self::REFUEL_THRESHOLD) {
            // Check time window
            if ($previous && $previous->detected_at->diffInMinutes(now()) <= self::TIME_WINDOW_MINUTES) {
                return true;
            }
            return true; // First reading or outside time window
        }

        return false;
    }

    /**
     * Detect fuel theft
     */
    protected function isTheft($fuelBefore, $fuelAfter, $previous, Device $device)
    {
        $percentChange = (($fuelBefore - $fuelAfter) / max($fuelBefore, 1)) * 100;

        if ($percentChange >= self::THEFT_THRESHOLD) {
            // Check time window and engine status
            if ($previous && $previous->detected_at->diffInMinutes(now()) <= self::TIME_WINDOW_MINUTES) {
                // TODO: Check if engine is off (ignition status)
                return true;
            }
        }

        return false;
    }

    /**
     * Create fuel transaction record
     */
    protected function createTransaction(array $data)
    {
        return FuelTransaction::create($data);
    }

    /**
     * Update device current fuel level
     */
    protected function updateFuelLevel(Device $device, $fuelLevel)
    {
        $device->update([
            'current_fuel_level' => $fuelLevel,
            'last_fuel_update' => now(),
        ]);
    }

    /**
     * Check for low fuel and send alerts
     */
    protected function checkLowFuel(Device $device, $fuelLevel)
    {
        if ($fuelLevel <= self::CRITICAL_FUEL_THRESHOLD) {
            $this->sendLowFuelAlert($device, $fuelLevel, 'critical');
        } elseif ($fuelLevel <= self::LOW_FUEL_THRESHOLD) {
            $this->sendLowFuelAlert($device, $fuelLevel, 'warning');
        }
    }

    /**
     * Send low fuel alert
     */
    protected function sendLowFuelAlert(Device $device, $fuelLevel, $severity)
    {
        // Check if alert already sent recently
        $recentAlert = FuelAlert::where('device_id', $device->id)
            ->where('alert_type', 'low_fuel')
            ->where('sent_at', '>=', now()->subHours(1))
            ->exists();

        if (!$recentAlert) {
            FuelAlert::create([
                'vendor_id' => $device->vendor_id,
                'device_id' => $device->id,
                'alert_type' => 'low_fuel',
                'severity' => $severity,
                'title' => 'Low Fuel Alert',
                'message' => "Device {$device->name} has low fuel level: {$fuelLevel}%",
                'fuel_level' => $fuelLevel,
                'sent_at' => now(),
            ]);
        }
    }

    /**
     * Send fuel theft alert
     */
    protected function sendTheftAlert(Device $device, FuelTransaction $transaction)
    {
        FuelAlert::create([
            'vendor_id' => $device->vendor_id,
            'device_id' => $device->id,
            'alert_type' => 'theft_detected',
            'severity' => 'critical',
            'title' => 'Fuel Theft Detected',
            'message' => "Possible fuel theft detected on {$device->name}. {$transaction->fuel_change}L removed.",
            'fuel_level' => $transaction->fuel_after,
            'metadata' => [
                'transaction_id' => $transaction->id,
                'fuel_removed' => abs($transaction->fuel_change),
            ],
            'sent_at' => now(),
        ]);
    }
}
