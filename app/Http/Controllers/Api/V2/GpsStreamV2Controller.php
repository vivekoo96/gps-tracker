<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Device;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GpsStreamV2Controller extends BaseV2Controller
{
    /**
     * Authenticated real-time GPS stream (SSE)
     * Scoped by the authenticated user's tenant.
     */
    public function stream(Request $request): StreamedResponse
    {
        return new StreamedResponse(function () use ($request) {
            // Send connection success event
            echo "event: connected\n";
            echo "data: " . json_encode([
                'status' => 'success',
                'message' => 'Real-time GPS stream V2 connected',
                'timestamp' => now()->toIso8601String()
            ]) . "\n\n";
            
            if (ob_get_level() > 0) ob_flush();
            flush();

            $lastUpdate = now();

            // Maximum execution time for the stream
            $startTime = time();
            $maxTime = 3600; // 1 hour

            while (time() - $startTime < $maxTime) {
                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }

                // Get devices that have been updated since our last check
                // VendorScope is automatically applied to Device model
                $devices = Device::where('updated_at', '>', $lastUpdate)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get();

                if ($devices->count() > 0) {
                    $payload = $devices->map(function ($device) {
                        return [
                            'id' => $device->id,
                            'name' => $device->name,
                            'vehicle_no' => $device->vehicle_no,
                            'latitude' => (float) $device->latitude,
                            'longitude' => (float) $device->longitude,
                            'speed' => (float) ($device->speed ?? 0),
                            'course' => (float) ($device->heading ?? 0),
                            'status' => $device->status,
                            'last_update' => $device->updated_at->toIso8601String(),
                        ];
                    });

                    echo "event: update\n";
                    echo "data: " . json_encode($payload) . "\n\n";
                    
                    if (ob_get_level() > 0) ob_flush();
                    flush();

                    $lastUpdate = now();
                } else {
                    // Send heartbeat to keep connection alive every 15 seconds
                    if (time() % 15 === 0) {
                        echo "event: heartbeat\n";
                        echo "data: " . json_encode(['timestamp' => now()->toIso8601String()]) . "\n\n";
                        
                        if (ob_get_level() > 0) ob_flush();
                        flush();
                    }
                }

                sleep(2); // Poll interval
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ]);
    }
}
