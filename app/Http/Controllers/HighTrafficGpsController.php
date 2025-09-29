<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Position;
use App\Events\GpsDataReceived;
use App\Events\DashboardStatsUpdated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class HighTrafficGpsController extends Controller
{
    /**
     * High-performance GPS data receiver with caching and queues
     */
    public function receiveData(Request $request): JsonResponse
    {
        try {
            // Parse GPS data (reuse existing logic)
            $gpsData = $this->parseGpsData($request);
            
            if (!$gpsData) {
                return response()->json(['error' => 'Invalid GPS data format'], 400);
            }

            // Use Redis for device caching (much faster than DB)
            $deviceKey = "device:{$gpsData['device_id']}";
            $device = Cache::remember($deviceKey, 3600, function () use ($gpsData) {
                return $this->findOrCreateDevice($gpsData);
            });
            
            if (!$device) {
                return response()->json(['error' => 'Device not found'], 401);
            }

            // Queue position storage for high throughput
            Queue::push(function ($job) use ($device, $gpsData) {
                $this->storePositionAsync($device, $gpsData);
                $job->delete();
            });

            // Update device status in Redis cache (instant)
            $this->updateDeviceCache($device, $gpsData);

            // Broadcast real-time event (WebSocket)
            broadcast(new GpsDataReceived($device, $gpsData));

            // Update dashboard stats every 10 seconds (throttled)
            $this->updateDashboardStatsThrottled();

            return response()->json([
                'status' => 'success',
                'message' => 'GPS data queued for processing',
                'device_id' => $device->id,
                'timestamp' => now()->toISOString(),
                'performance' => 'high-traffic-optimized'
            ]);

        } catch (\Exception $e) {
            Log::error('High-Traffic GPS Error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to process GPS data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store position data asynchronously
     */
    private function storePositionAsync(Device $device, array $gpsData): void
    {
        try {
            Position::create([
                'device_id' => $device->id,
                'fix_time' => $gpsData['timestamp'],
                'latitude' => $gpsData['latitude'],
                'longitude' => $gpsData['longitude'],
                'speed' => $gpsData['speed'] ?? 0,
                'course' => $gpsData['heading'],
                'altitude' => $gpsData['altitude'],
                'satellites' => $gpsData['satellites'],
                'ignition' => $gpsData['ignition'],
                'attributes' => json_encode(array_filter([
                    'battery_level' => $gpsData['battery_level'] ?? null,
                    'raw_data' => $gpsData,
                ])),
            ]);
        } catch (\Exception $e) {
            Log::error('Async Position Storage Error', [
                'device_id' => $device->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update device status in Redis cache
     */
    private function updateDeviceCache(Device $device, array $gpsData): void
    {
        $cacheKey = "device_status:{$device->id}";
        $deviceData = [
            'id' => $device->id,
            'name' => $device->name,
            'latitude' => $gpsData['latitude'],
            'longitude' => $gpsData['longitude'],
            'speed' => $gpsData['speed'] ?? 0,
            'is_moving' => ($gpsData['speed'] ?? 0) > 1,
            'battery_level' => $gpsData['battery_level'],
            'last_update' => now()->toISOString(),
            'is_online' => true,
        ];

        // Store in Redis with 1-hour expiry
        Redis::setex($cacheKey, 3600, json_encode($deviceData));

        // Also update database asynchronously
        Queue::push(function ($job) use ($device, $gpsData) {
            $device->update([
                'latitude' => $gpsData['latitude'],
                'longitude' => $gpsData['longitude'],
                'speed' => $gpsData['speed'] ?? 0,
                'last_location_update' => $gpsData['timestamp'],
                'status' => 'active',
                'is_moving' => ($gpsData['speed'] ?? 0) > 1,
                'battery_level' => $gpsData['battery_level'],
            ]);
            $job->delete();
        });
    }

    /**
     * Get device locations from Redis cache (super fast)
     */
    public function getDeviceLocationsFromCache(): JsonResponse
    {
        $devices = [];
        $keys = Redis::keys('device_status:*');
        
        foreach ($keys as $key) {
            $deviceData = Redis::get($key);
            if ($deviceData) {
                $devices[] = json_decode($deviceData, true);
            }
        }

        return response()->json($devices);
    }

    /**
     * Update dashboard stats with throttling
     */
    private function updateDashboardStatsThrottled(): void
    {
        $throttleKey = 'dashboard_stats_throttle';
        
        // Only update stats every 10 seconds
        if (!Cache::has($throttleKey)) {
            Cache::put($throttleKey, true, 10);
            
            Queue::push(function ($job) {
                $stats = [
                    'total_devices' => Device::count(),
                    'online_devices' => $this->getOnlineDevicesFromCache(),
                    'moving_devices' => $this->getMovingDevicesFromCache(),
                    'positions_today' => Position::whereDate('fix_time', today())->count(),
                ];
                
                broadcast(new DashboardStatsUpdated($stats));
                $job->delete();
            });
        }
    }

    /**
     * Get online devices count from Redis
     */
    private function getOnlineDevicesFromCache(): int
    {
        $keys = Redis::keys('device_status:*');
        $onlineCount = 0;
        
        foreach ($keys as $key) {
            $deviceData = Redis::get($key);
            if ($deviceData) {
                $device = json_decode($deviceData, true);
                if ($device['is_online']) {
                    $onlineCount++;
                }
            }
        }
        
        return $onlineCount;
    }

    /**
     * Get moving devices count from Redis
     */
    private function getMovingDevicesFromCache(): int
    {
        $keys = Redis::keys('device_status:*');
        $movingCount = 0;
        
        foreach ($keys as $key) {
            $deviceData = Redis::get($key);
            if ($deviceData) {
                $device = json_decode($deviceData, true);
                if ($device['is_moving']) {
                    $movingCount++;
                }
            }
        }
        
        return $movingCount;
    }

    /**
     * Reuse existing GPS parsing logic
     */
    private function parseGpsData(Request $request): ?array
    {
        // JSON POST data
        if ($request->isJson()) {
            return $this->parseJsonData($request->json()->all());
        }

        // Form POST data
        if ($request->isMethod('post') && $request->has(['lat', 'lng'])) {
            return $this->parseFormData($request->all());
        }

        // GET parameters
        if ($request->has(['lat', 'lng']) || $request->has(['latitude', 'longitude'])) {
            return $this->parseQueryParams($request->all());
        }

        return null;
    }

    private function parseJsonData(array $data): ?array
    {
        $required = ['device_id', 'latitude', 'longitude'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return null;
            }
        }

        return [
            'device_id' => $data['device_id'],
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'speed' => isset($data['speed']) ? (float) $data['speed'] : 0,
            'heading' => isset($data['heading']) ? (float) $data['heading'] : null,
            'altitude' => isset($data['altitude']) ? (float) $data['altitude'] : null,
            'satellites' => isset($data['satellites']) ? (int) $data['satellites'] : null,
            'battery_level' => isset($data['battery']) ? (int) $data['battery'] : null,
            'timestamp' => isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now(),
            'ignition' => isset($data['ignition']) ? (bool) $data['ignition'] : null,
        ];
    }

    private function parseFormData(array $data): ?array
    {
        $lat = $data['lat'] ?? $data['latitude'] ?? null;
        $lng = $data['lng'] ?? $data['longitude'] ?? null;
        $deviceId = $data['device_id'] ?? $data['imei'] ?? $data['id'] ?? null;

        if (!$lat || !$lng || !$deviceId) {
            return null;
        }

        return [
            'device_id' => $deviceId,
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
            'speed' => isset($data['speed']) ? (float) $data['speed'] : 0,
            'heading' => isset($data['heading']) ? (float) $data['heading'] : null,
            'altitude' => isset($data['altitude']) ? (float) $data['altitude'] : null,
            'satellites' => isset($data['satellites']) ? (int) $data['satellites'] : null,
            'battery_level' => isset($data['battery']) ? (int) $data['battery'] : null,
            'timestamp' => isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now(),
            'ignition' => isset($data['ignition']) ? (bool) $data['ignition'] : null,
        ];
    }

    private function parseQueryParams(array $params): ?array
    {
        return $this->parseFormData($params);
    }

    private function findOrCreateDevice(array $gpsData): ?Device
    {
        $deviceId = $gpsData['device_id'];
        
        $device = Device::where('unique_id', $deviceId)->first();
        
        if (!$device) {
            $device = Device::create([
                'name' => "Auto Device {$deviceId}",
                'unique_id' => $deviceId,
                'imei' => $deviceId,
                'model' => 'Unknown GPS Tracker',
                'device_type' => 'gps_tracker',
                'status' => 'active',
                'creator' => 'auto_created',
            ]);
        }

        return $device;
    }
}
