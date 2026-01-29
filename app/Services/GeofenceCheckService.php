<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Geofence;
use App\Models\GeofenceEvent;
use App\Models\Position;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeofenceCheckService
{
    /**
     * Check if a device position triggers any geofence events
     *
     * @param Device $device
     * @param Position $position
     * @return void
     */
    public function checkPosition(Device $device, Position $position)
    {
        // Get all active geofences
        $geofences = Geofence::active()->get();

        foreach ($geofences as $geofence) {
            $isInside = $geofence->containsPoint(
                $position->latitude,
                $position->longitude
            );

            $this->handleGeofenceState($device, $geofence, $isInside, $position);
        }
    }

    /**
     * Handle the geofence state for a device
     *
     * @param Device $device
     * @param Geofence $geofence
     * @param bool $isInside
     * @param Position $position
     * @return void
     */
    private function handleGeofenceState(Device $device, Geofence $geofence, bool $isInside, Position $position)
    {
        $cacheKey = "geofence_state_{$device->id}_{$geofence->id}";
        $wasInside = Cache::get($cacheKey, null);

        // First time checking this device-geofence combination
        if ($wasInside === null) {
            Cache::put($cacheKey, $isInside, now()->addDays(7));
            
            // If device is inside on first check, log entry event
            if ($isInside) {
                $this->logEvent($device, $geofence, 'enter', $position);
            }
            return;
        }

        // Check for state change
        if ($wasInside !== $isInside) {
            $eventType = $isInside ? 'enter' : 'exit';
            
            // Log the event
            $this->logEvent($device, $geofence, $eventType, $position);
            
            // Update cache
            Cache::put($cacheKey, $isInside, now()->addDays(7));
        }
    }

    /**
     * Log a geofence event
     *
     * @param Device $device
     * @param Geofence $geofence
     * @param string $eventType
     * @param Position $position
     * @return GeofenceEvent
     */
    private function logEvent(Device $device, Geofence $geofence, string $eventType, Position $position)
    {
        $event = GeofenceEvent::create([
            'geofence_id' => $geofence->id,
            'device_id' => $device->id,
            'event_type' => $eventType,
            'latitude' => $position->latitude,
            'longitude' => $position->longitude,
            'event_time' => $position->fix_time ?? now(),
        ]);

        Log::info("Geofence {$eventType} event", [
            'device' => $device->name,
            'geofence' => $geofence->name,
            'event_type' => $eventType,
        ]);

        // Trigger alert if configured
        $this->triggerAlert($geofence, $device, $eventType, $event);

        return $event;
    }

    /**
     * Trigger alert for geofence event
     *
     * @param Geofence $geofence
     * @param Device $device
     * @param string $eventType
     * @param GeofenceEvent $event
     * @return void
     */
    private function triggerAlert(Geofence $geofence, Device $device, string $eventType, GeofenceEvent $event)
    {
        $alert = $geofence->alert;

        if (!$alert || !$alert->shouldAlert($eventType)) {
            return;
        }

        // Get users to notify
        $users = $alert->getUsersToNotify();

        foreach ($users as $user) {
            // Send notification (you can expand this with email/SMS later)
            $user->notify(new \App\Notifications\GeofenceNotification(
                $geofence,
                $device,
                $eventType,
                $event
            ));
        }

        Log::info("Geofence alert sent", [
            'geofence' => $geofence->name,
            'device' => $device->name,
            'event_type' => $eventType,
            'users_notified' => $users->count(),
        ]);
    }

    /**
     * Clear geofence state cache for a device
     *
     * @param Device $device
     * @return void
     */
    public function clearDeviceCache(Device $device)
    {
        $geofences = Geofence::all();
        
        foreach ($geofences as $geofence) {
            $cacheKey = "geofence_state_{$device->id}_{$geofence->id}";
            Cache::forget($cacheKey);
        }
    }
}
