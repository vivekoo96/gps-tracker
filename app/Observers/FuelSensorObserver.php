<?php

namespace App\Observers;

use App\Models\FuelSensor;
use Illuminate\Support\Facades\Log;

class FuelSensorObserver
{
    /**
     * Handle the FuelSensor "updated" event.
     */
    public function updated(FuelSensor $fuelSensor): void
    {
        if ($fuelSensor->isDirty('current_level')) {
            $oldLevel = $fuelSensor->getOriginal('current_level');
            $newLevel = $fuelSensor->current_level;
            
            // Check for sudden drop (Theft logic)
            // Example: Drop of more than 10 liters (or %) instantly is suspicious
            if ($oldLevel > 0 && ($oldLevel - $newLevel) > 5) {
                Log::warning("FUEL THEFT ALERT: Device {$fuelSensor->device_id} lost " . ($oldLevel - $newLevel) . " liters instantly!", [
                    'device_id' => $fuelSensor->device_id,
                    'old' => $oldLevel,
                    'new' => $newLevel,
                    'timestamp' => now()
                ]);

                // Here you would trigger an Notification/Email/SMS
                // Notification::send($fuelSensor->device->vendor->users, new FuelTheftAlert($fuelSensor));
            }
        }
    }
}
