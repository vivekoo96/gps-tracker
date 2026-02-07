<?php

namespace App\Services;

use App\Models\GpsData;
use App\Models\DriverViolation;
use App\Models\Device;
use Carbon\Carbon;

class ViolationDetectionService
{
    // Thresholds for violation detection
    const HARSH_BRAKING_THRESHOLD = 0.4; // g-force
    const HARSH_ACCELERATION_THRESHOLD = 0.35; // g-force
    const HARSH_CORNERING_THRESHOLD = 0.3; // g-force
    const SPEEDING_TOLERANCE = 10; // km/h above limit
    const EXCESSIVE_IDLING_MINUTES = 5;

    /**
     * Detect violations from GPS data
     */
    public function detectViolations(GpsData $current, ?GpsData $previous = null)
    {
        $violations = [];

        if ($previous) {
            // Detect harsh braking
            if ($braking = $this->detectHarshBraking($current, $previous)) {
                $violations[] = $braking;
            }

            // Detect harsh acceleration
            if ($acceleration = $this->detectHarshAcceleration($current, $previous)) {
                $violations[] = $acceleration;
            }

            // Detect harsh cornering
            if ($cornering = $this->detectHarshCornering($current, $previous)) {
                $violations[] = $cornering;
            }
        }

        // Detect speeding
        if ($speeding = $this->detectSpeeding($current)) {
            $violations[] = $speeding;
        }

        // Detect excessive idling
        if ($idling = $this->detectExcessiveIdling($current->device)) {
            $violations[] = $idling;
        }

        return $violations;
    }

    /**
     * Detect harsh braking (sudden deceleration)
     */
    public function detectHarshBraking(GpsData $current, GpsData $previous)
    {
        $timeDiff = $current->timestamp->diffInSeconds($previous->timestamp);
        if ($timeDiff == 0) return null;

        $speedDiff = $previous->speed - $current->speed; // Deceleration
        
        if ($speedDiff <= 0) return null; // Not braking

        // Convert to m/s and calculate deceleration
        $deceleration = ($speedDiff * 1000 / 3600) / $timeDiff; // m/s²
        $gForce = $deceleration / 9.81; // Convert to g-force

        if ($gForce >= self::HARSH_BRAKING_THRESHOLD) {
            return $this->createViolation([
                'device_id' => $current->device_id,
                'violation_type' => 'harsh_braking',
                'severity' => $this->determineSeverity('harsh_braking', $gForce),
                'latitude' => $current->latitude,
                'longitude' => $current->longitude,
                'speed' => $current->speed,
                'metadata' => [
                    'g_force' => round($gForce, 2),
                    'speed_before' => $previous->speed,
                    'speed_after' => $current->speed,
                ],
                'occurred_at' => $current->timestamp,
            ]);
        }

        return null;
    }

    /**
     * Detect harsh acceleration
     */
    public function detectHarshAcceleration(GpsData $current, GpsData $previous)
    {
        $timeDiff = $current->timestamp->diffInSeconds($previous->timestamp);
        if ($timeDiff == 0) return null;

        $speedDiff = $current->speed - $previous->speed; // Acceleration
        
        if ($speedDiff <= 0) return null; // Not accelerating

        // Convert to m/s and calculate acceleration
        $acceleration = ($speedDiff * 1000 / 3600) / $timeDiff; // m/s²
        $gForce = $acceleration / 9.81; // Convert to g-force

        if ($gForce >= self::HARSH_ACCELERATION_THRESHOLD) {
            return $this->createViolation([
                'device_id' => $current->device_id,
                'violation_type' => 'harsh_acceleration',
                'severity' => $this->determineSeverity('harsh_acceleration', $gForce),
                'latitude' => $current->latitude,
                'longitude' => $current->longitude,
                'speed' => $current->speed,
                'metadata' => [
                    'g_force' => round($gForce, 2),
                    'speed_before' => $previous->speed,
                    'speed_after' => $current->speed,
                ],
                'occurred_at' => $current->timestamp,
            ]);
        }

        return null;
    }

    /**
     * Detect harsh cornering
     */
    public function detectHarshCornering(GpsData $current, GpsData $previous)
    {
        // Calculate heading change
        $headingChange = abs($current->course - $previous->course);
        
        // Normalize to 0-180 degrees
        if ($headingChange > 180) {
            $headingChange = 360 - $headingChange;
        }

        $timeDiff = $current->timestamp->diffInSeconds($previous->timestamp);
        if ($timeDiff == 0 || $headingChange < 15) return null; // Minimum 15° turn

        // Calculate lateral g-force (simplified)
        $avgSpeed = ($current->speed + $previous->speed) / 2;
        $turnRate = $headingChange / $timeDiff; // degrees per second
        
        // Approximate lateral acceleration
        $lateralAccel = ($avgSpeed * 1000 / 3600) * ($turnRate * pi() / 180);
        $gForce = abs($lateralAccel) / 9.81;

        if ($gForce >= self::HARSH_CORNERING_THRESHOLD) {
            return $this->createViolation([
                'device_id' => $current->device_id,
                'violation_type' => 'harsh_cornering',
                'severity' => $this->determineSeverity('harsh_cornering', $gForce),
                'latitude' => $current->latitude,
                'longitude' => $current->longitude,
                'speed' => $current->speed,
                'metadata' => [
                    'g_force' => round($gForce, 2),
                    'heading_change' => round($headingChange, 1),
                    'turn_rate' => round($turnRate, 2),
                ],
                'occurred_at' => $current->timestamp,
            ]);
        }

        return null;
    }

    /**
     * Detect speeding violations
     */
    public function detectSpeeding(GpsData $current)
    {
        // Get speed limit for location (you can integrate with a speed limit API)
        $speedLimit = $this->getSpeedLimit($current->latitude, $current->longitude);
        
        if (!$speedLimit) return null;

        $overspeed = $current->speed - $speedLimit;

        if ($overspeed > self::SPEEDING_TOLERANCE) {
            return $this->createViolation([
                'device_id' => $current->device_id,
                'violation_type' => 'speeding',
                'severity' => $this->determineSeverity('speeding', $overspeed),
                'latitude' => $current->latitude,
                'longitude' => $current->longitude,
                'speed' => $current->speed,
                'speed_limit' => $speedLimit,
                'metadata' => [
                    'overspeed_amount' => round($overspeed, 1),
                    'percentage_over' => round(($overspeed / $speedLimit) * 100, 1),
                ],
                'occurred_at' => $current->timestamp,
            ]);
        }

        return null;
    }

    /**
     * Detect excessive idling
     */
    public function detectExcessiveIdling(Device $device)
    {
        // Get recent GPS data for this device
        $recentData = GpsData::where('device_id', $device->id)
            ->where('timestamp', '>=', now()->subMinutes(self::EXCESSIVE_IDLING_MINUTES + 1))
            ->orderBy('timestamp', 'desc')
            ->get();

        if ($recentData->count() < 2) return null;

        // Check if engine is on and speed is near zero
        $idlingCount = 0;
        foreach ($recentData as $data) {
            if ($data->ignition && $data->speed < 5) {
                $idlingCount++;
            }
        }

        $idlingMinutes = $idlingCount; // Approximate

        if ($idlingMinutes >= self::EXCESSIVE_IDLING_MINUTES) {
            $latest = $recentData->first();
            
            return $this->createViolation([
                'device_id' => $device->id,
                'violation_type' => 'excessive_idling',
                'severity' => $this->determineSeverity('excessive_idling', $idlingMinutes),
                'latitude' => $latest->latitude,
                'longitude' => $latest->longitude,
                'speed' => 0,
                'metadata' => [
                    'duration_minutes' => $idlingMinutes,
                    'fuel_wasted_liters' => round($idlingMinutes * 0.8 / 60, 2), // 0.8 L/hour
                ],
                'occurred_at' => $latest->timestamp,
            ]);
        }

        return null;
    }

    /**
     * Determine severity based on violation type and value
     */
    private function determineSeverity($violationType, $value)
    {
        return match($violationType) {
            'harsh_braking' => match(true) {
                $value >= 0.8 => 'critical',
                $value >= 0.6 => 'high',
                $value >= 0.5 => 'medium',
                default => 'low',
            },
            'harsh_acceleration' => match(true) {
                $value >= 0.7 => 'critical',
                $value >= 0.5 => 'high',
                $value >= 0.4 => 'medium',
                default => 'low',
            },
            'harsh_cornering' => match(true) {
                $value >= 0.6 => 'critical',
                $value >= 0.45 => 'high',
                $value >= 0.35 => 'medium',
                default => 'low',
            },
            'speeding' => match(true) {
                $value >= 40 => 'critical',
                $value >= 25 => 'high',
                $value >= 15 => 'medium',
                default => 'low',
            },
            'excessive_idling' => match(true) {
                $value >= 15 => 'high',
                $value >= 10 => 'medium',
                default => 'low',
            },
            default => 'medium',
        };
    }

    /**
     * Create violation record
     */
    private function createViolation(array $data)
    {
        $device = Device::find($data['device_id']);
        
        $data['vendor_id'] = $device->vendor_id;
        $data['driver_id'] = $device->user_id; // Assuming device has assigned driver
        
        $violation = DriverViolation::create($data);

        // Trigger Webhook
        app(\App\Services\WebhookService::class)->trigger('alert.created', [
            'alert_id' => $violation->id,
            'device_id' => $violation->device_id,
            'violation_type' => $violation->violation_type,
            'severity' => $violation->severity,
            'latitude' => $violation->latitude,
            'longitude' => $violation->longitude,
            'occurred_at' => $violation->occurred_at,
        ], $violation->vendor_id);

        return $violation;
    }

    /**
     * Get speed limit for location (placeholder - integrate with API)
     */
    private function getSpeedLimit($latitude, $longitude)
    {
        // TODO: Integrate with speed limit API (Google Maps, HERE, etc.)
        // For now, return default based on area type
        return 60; // Default 60 km/h
    }
}
