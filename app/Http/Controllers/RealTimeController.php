<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RealTimeController extends Controller
{
    /**
     * Server-Sent Events stream for real-time GPS data
     */
    public function gpsStream(Request $request)
    {
        return response()->stream(function () {
            // Set headers for SSE
            echo "data: " . json_encode(['type' => 'connected', 'message' => 'Real-time GPS stream connected']) . "\n\n";
            ob_flush();
            flush();

            $lastUpdate = now();
            
            while (true) {
                // Check for new GPS data every 2 seconds
                $devices = Device::whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->where('updated_at', '>', $lastUpdate)
                    ->get();

                if ($devices->count() > 0) {
                    $data = [
                        'type' => 'device_update',
                        'timestamp' => now()->toISOString(),
                        'devices' => $devices->map(function ($device) {
                            return [
                                'id' => $device->id,
                                'name' => $device->name ?? 'Device #' . $device->id,
                                'latitude' => (float) $device->latitude,
                                'longitude' => (float) $device->longitude,
                                'speed' => (float) ($device->speed ?? 0),
                                'is_moving' => (bool) $device->is_moving,
                                'battery_level' => $device->battery_level,
                                'last_update' => $device->updated_at->toISOString(),
                                'is_online' => $device->is_online,
                            ];
                        })
                    ];

                    echo "data: " . json_encode($data) . "\n\n";
                    ob_flush();
                    flush();
                    
                    $lastUpdate = now();
                }

                // Send heartbeat every 30 seconds
                if (now()->second % 30 === 0) {
                    echo "data: " . json_encode([
                        'type' => 'heartbeat',
                        'timestamp' => now()->toISOString(),
                        'active_devices' => Device::where('status', 'active')->count()
                    ]) . "\n\n";
                    ob_flush();
                    flush();
                }

                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }

                sleep(2); // Check every 2 seconds
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ]);
    }

    /**
     * Get dashboard statistics stream
     */
    public function dashboardStream()
    {
        return response()->stream(function () {
            echo "data: " . json_encode(['type' => 'connected', 'message' => 'Dashboard stream connected']) . "\n\n";
            ob_flush();
            flush();

            while (true) {
                $stats = [
                    'type' => 'stats_update',
                    'timestamp' => now()->toISOString(),
                    'data' => [
                        'total_devices' => Device::count(),
                        'online_devices' => Device::where('status', 'active')->count(),
                        'moving_devices' => Device::where('is_moving', true)->count(),
                        'offline_devices' => Device::where('status', 'inactive')->count(),
                        'low_battery_devices' => Device::where('battery_level', '<', 20)->count(),
                        'positions_today' => Position::whereDate('fix_time', today())->count(),
                    ]
                ];

                echo "data: " . json_encode($stats) . "\n\n";
                ob_flush();
                flush();

                if (connection_aborted()) {
                    break;
                }

                sleep(5); // Update stats every 5 seconds
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
