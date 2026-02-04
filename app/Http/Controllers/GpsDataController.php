<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GpsDataController extends Controller
{
    /**
     * Receive GPS data from real devices
     * Supports multiple protocols: HTTP POST, GET parameters, and raw data
     */
    public function receiveData(Request $request): JsonResponse
    {
        // Set JSON response headers immediately
        header('Content-Type: application/json');
        
        try {
            // Log incoming request for debugging
            Log::info('GPS Data Received', [
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'raw' => $request->getContent(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Try to parse data from different sources
            $gpsData = $this->parseGpsData($request);
            
            if (!$gpsData) {
                return response()->json(['error' => 'Invalid GPS data format'], 400);
            }

            // Find or create device
            $device = $this->findOrCreateDevice($gpsData);
            
            if (!$device) {
                return response()->json(['error' => 'Device not found or unauthorized'], 401);
            }

            // Secure API Check (Phase 4)
            $token = $request->header('X-Device-Token');
            if ($token && $device->api_secret && $token !== $device->api_secret) {
                return response()->json(['error' => 'Invalid Device Token'], 401);
            }

            // Store position data
            $position = $this->storePosition($device, $gpsData);
            
            // Update device status
            $this->updateDeviceStatus($device, $gpsData);

            // Process peripherals (Fuel, Dashcam)
            $this->processPeripheralData($device, $gpsData);

            // Trigger real-time update (you can use events here for WebSockets)
            Log::info('GPS Data Processed - Real-time update triggered', [
                'device_id' => $device->id,
                'position_id' => $position->id,
                'latitude' => $gpsData['latitude'],
                'longitude' => $gpsData['longitude'],
                'speed' => $gpsData['speed'] ?? 0,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'GPS data received and processed',
                'device_id' => $device->id,
                'position_id' => $position->id,
                'timestamp' => now()->toISOString(),
                'real_time' => 'SSE streams will update automatically'
            ]);

        } catch (\Exception $e) {
            Log::error('GPS Data Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to process GPS data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store GPS data via API (for testing and simple integrations)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate incoming data
            $validated = $request->validate([
                'imei' => 'required|string',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'speed' => 'nullable|numeric|min:0',
                'altitude' => 'nullable|numeric',
                'fix_time' => 'nullable|date',
                'course' => 'nullable|numeric|between:0,359',
                'satellites' => 'nullable|integer|min:0',
            ]);

            // Find device by IMEI
            $device = Device::where('imei', $validated['imei'])->first();
            
            if (!$device) {
                return response()->json([
                    'error' => 'Device not found',
                    'message' => 'No device found with the provided IMEI'
                ], 404);
            }

            // Create position record
            $position = Position::create([
                'device_id' => $device->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'speed' => $validated['speed'] ?? 0,
                'altitude' => $validated['altitude'] ?? null,
                'course' => $validated['course'] ?? null,
                'satellites' => $validated['satellites'] ?? null,
                'fix_time' => $validated['fix_time'] ?? now(),
            ]);

            // Check geofences
            try {
                app(\App\Services\GeofenceCheckService::class)->checkPosition($device, $position);
            } catch (\Exception $e) {
                Log::error('Geofence check failed', [
                    'device_id' => $device->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Update device last seen
            $device->update([
                'last_seen_at' => now(),
                'status' => 'active'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'GPS data stored successfully',
                'position_id' => $position->id,
                'device_id' => $device->id
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('GPS Data Store Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to store GPS data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse GPS data from various formats
     */
    private function parseGpsData(Request $request): ?array
    {
        // Method 1: JSON POST data
        if ($request->isJson()) {
            return $this->parseJsonData($request->json()->all());
        }

        // Method 2: Form POST data
        if ($request->isMethod('post') && $request->has(['lat', 'lng'])) {
            return $this->parseFormData($request->all());
        }

        // Method 3: GET parameters (common for simple GPS devices)
        if ($request->has(['lat', 'lng']) || $request->has(['latitude', 'longitude'])) {
            return $this->parseQueryParams($request->all());
        }

        // Method 4: Raw data (for devices sending custom protocols)
        $rawData = $request->getContent();
        if (!empty($rawData)) {
            return $this->parseRawData($rawData);
        }

        return null;
    }

    /**
     * Parse JSON formatted GPS data
     */
    private function parseJsonData(array $data): ?array
    {
        $required = ['device_id', 'latitude', 'longitude'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return null;
            }
        }

        // Base standard keys
        $parsed = [
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

        // Merge with original data to keep custom keys (like adc1, dashcam_status)
        // We prioritize parsed values but keep everything else
        return array_merge($data, $parsed);
    }

    /**
     * Parse form data
     */
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

    /**
     * Parse query parameters
     */
    private function parseQueryParams(array $params): ?array
    {
        return $this->parseFormData($params);
    }

    /**
     * Parse raw data (for custom protocols like NMEA, etc.)
     */
    private function parseRawData(string $rawData): ?array
    {
        // Example: Parse NMEA GPRMC sentence
        if (strpos($rawData, '$GPRMC') !== false) {
            return $this->parseNmeaData($rawData);
        }

        // Example: Parse simple comma-separated format
        // Format: device_id,lat,lng,speed,timestamp
        $parts = explode(',', trim($rawData));
        if (count($parts) >= 3) {
            return [
                'device_id' => $parts[0],
                'latitude' => (float) $parts[1],
                'longitude' => (float) $parts[2],
                'speed' => isset($parts[3]) ? (float) $parts[3] : 0,
                'timestamp' => isset($parts[4]) ? Carbon::parse($parts[4]) : now(),
            ];
        }

        return null;
    }

    /**
     * Parse NMEA GPRMC data
     */
    private function parseNmeaData(string $nmeaData): ?array
    {
        // Basic NMEA GPRMC parser
        // $GPRMC,time,status,lat,lat_dir,lng,lng_dir,speed,course,date,mag_var,mag_var_dir,checksum
        
        $lines = explode("\n", $nmeaData);
        foreach ($lines as $line) {
            if (strpos($line, '$GPRMC') === 0) {
                $parts = explode(',', $line);
                if (count($parts) >= 12 && $parts[2] === 'A') { // Valid fix
                    $lat = $this->convertDMSToDecimal($parts[3], $parts[4]);
                    $lng = $this->convertDMSToDecimal($parts[5], $parts[6]);
                    
                    return [
                        'device_id' => 'nmea_device', // You might want to extract this from somewhere else
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'speed' => isset($parts[7]) ? (float) $parts[7] * 1.852 : 0, // Convert knots to km/h
                        'heading' => isset($parts[8]) ? (float) $parts[8] : null,
                        'timestamp' => now(),
                    ];
                }
            }
        }
        
        return null;
    }

    /**
     * Convert DMS (Degrees, Minutes, Seconds) to Decimal Degrees
     */
    private function convertDMSToDecimal(string $coordinate, string $direction): float
    {
        if (empty($coordinate)) return 0;
        
        $degrees = (int) substr($coordinate, 0, -7);
        $minutes = (float) substr($coordinate, -7);
        
        $decimal = $degrees + ($minutes / 60);
        
        if (in_array($direction, ['S', 'W'])) {
            $decimal *= -1;
        }
        
        return $decimal;
    }

    /**
     * Find or create device based on GPS data
     */
    private function findOrCreateDevice(array $gpsData): ?Device
    {
        $deviceId = $gpsData['device_id'];
        
        // Try to find device by unique_id first
        $device = Device::where('unique_id', $deviceId)->first();
        
        if (!$device) {
            // Try to find by IMEI or other identifiers
            $device = Device::where('imei', $deviceId)
                          ->orWhere('phone_number', $deviceId)
                          ->first();
        }

        if (!$device) {
            // Auto-create device if it doesn't exist (you might want to disable this in production)
            $device = Device::create([
                'name' => "Auto Device {$deviceId}",
                'unique_id' => $deviceId,
                'imei' => $deviceId, // Use device_id as IMEI for auto-created devices
                'model' => 'Unknown GPS Tracker',
                'device_type' => 'gps_tracker',
                'status' => 'active',
                'creator' => 'auto_created',
            ]);
            
            Log::info("Auto-created device: {$deviceId}");
        }

        return $device;
    }

    /**
     * Store position data
     */
    private function storePosition(Device $device, array $gpsData): Position
    {
        $position = Position::create([
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

        // Check geofences for this position
        try {
            app(\App\Services\GeofenceCheckService::class)->checkPosition($device, $position);
        } catch (\Exception $e) {
            Log::error('Geofence check failed', [
                'device_id' => $device->id,
                'error' => $e->getMessage()
            ]);
        }

        return $position;
    }

    /**
     * Update device status and location
     */
    private function updateDeviceStatus(Device $device, array $gpsData): void
    {
        $updateData = [
            'latitude' => $gpsData['latitude'],
            'longitude' => $gpsData['longitude'],
            'speed' => $gpsData['speed'] ?? 0,
            'last_location_update' => $gpsData['timestamp'],
            'status' => 'active',
            'is_moving' => ($gpsData['speed'] ?? 0) > 1, // Consider moving if speed > 1 km/h
        ];

        if (isset($gpsData['heading'])) {
            $updateData['heading'] = $gpsData['heading'];
        }

        if (isset($gpsData['altitude'])) {
            $updateData['altitude'] = $gpsData['altitude'];
        }

        if (isset($gpsData['satellites'])) {
            $updateData['satellites'] = $gpsData['satellites'];
        }

        if (isset($gpsData['battery_level'])) {
            $updateData['battery_level'] = $gpsData['battery_level'];
        }

        $device->update($updateData);
    }

    /**
     * Process peripheral data (Fuel, Dashcam)
     */
    private function processPeripheralData(Device $device, array $gpsData): void
    {
        // 1. Process Fuel Sensor
        if ($device->fuelSensor) {
            $fuelSensor = $device->fuelSensor;
            
            // Get the configured data source key (default to 'adc1')
            $sourceKey = $fuelSensor->data_source ?? 'adc1';
            
            // Look for the specific key in data, or fallbacks if not found
            $rawValue = $gpsData[$sourceKey] ?? null;
            
            // Fallback for common keys if specific one not found and default was used
            if ($rawValue === null && $sourceKey === 'adc1') {
                $rawValue = $gpsData['fuel'] ?? $gpsData['fuel_level'] ?? $gpsData['ai1'] ?? null;
            }
            
            if ($rawValue !== null) {
                $liters = $this->calculateFuelLiters($rawValue, $fuelSensor->calibration_data);
                
                $fuelSensor->update([
                    'current_level' => $liters,
                    'status' => 'active'
                ]);
            }
        }

        // 2. Process Dashcam
        if ($device->dashcam) {
            $dashcam = $device->dashcam;
            
            // Look for dashcam status keys
            $camStatus = $gpsData['dashcam_status'] ?? $gpsData['dvr_status'] ?? null;
            
            if ($camStatus) {
                // strict mapping or just save passing value
                $statusMap = [
                    '1' => 'online', '0' => 'offline', 
                    'rec' => 'recording', 'error' => 'error'
                ];
                
                $dashcam->update([
                    'status' => $statusMap[$camStatus] ?? 'online' // Default to online if we get a signal
                ]);
            }
        }
    }

    /**
     * Calculate fuel liters based on calibration data using Linear Interpolation
     */
    private function calculateFuelLiters($rawValue, $calibrationData): float
    {
        if (empty($calibrationData) || !is_array($calibrationData)) {
            // No calibration? Return raw or percentage if within common range
            return (float) $rawValue;
        }

        // Sort calibration points by key (sensor value)
        ksort($calibrationData);
        
        $points = [];
        foreach ($calibrationData as $sensorVal => $liters) {
            $points[] = ['x' => (float)$sensorVal, 'y' => (float)$liters];
        }

        $count = count($points);
        if ($count < 2) return (float) $rawValue; // Need at least 2 points

        // Check bounds
        if ($rawValue <= $points[0]['x']) return $points[0]['y'];
        if ($rawValue >= $points[$count - 1]['x']) return $points[$count - 1]['y'];

        // Find the segment the rawValue falls into
        for ($i = 0; $i < $count - 1; $i++) {
            $p1 = $points[$i];
            $p2 = $points[$i + 1];

            if ($rawValue >= $p1['x'] && $rawValue <= $p2['x']) {
                // Linear Interpolation Formula: y = y1 + (x - x1) * (y2 - y1) / (x2 - x1)
                $slope = ($p2['y'] - $p1['y']) / ($p2['x'] - $p1['x']);
                return round($p1['y'] + ($rawValue - $p1['x']) * $slope, 2);
            }
        }

        return 0;
    }

    /**
     * Get device tracking history
     */
    public function getDeviceHistory(Request $request, $deviceId): JsonResponse
    {
        $device = Device::findOrFail($deviceId);
        
        $positions = Position::where('device_id', $device->id)
            ->when($request->has('from'), function ($query) use ($request) {
                return $query->where('fix_time', '>=', $request->get('from'));
            })
            ->when($request->has('to'), function ($query) use ($request) {
                return $query->where('fix_time', '<=', $request->get('to'));
            })
            ->orderBy('fix_time', 'desc')
            ->limit($request->get('limit', 100))
            ->get();

        return response()->json([
            'device' => $device,
            'positions' => $positions,
            'total' => $positions->count()
        ]);
    }

    /**
     * Get all device locations for real-time map updates
     */
    public function getDeviceLocations(): JsonResponse
    {
        $devices = Device::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select([
                'id', 'name', 'unique_id', 'latitude', 'longitude', 
                'speed', 'is_moving', 'last_location_update', 'battery_level',
                'heading', 'altitude', 'satellites', 'status'
            ])
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'name' => $device->name ?? 'Device #' . $device->id,
                    'unique_id' => $device->unique_id,
                    'latitude' => (float) $device->latitude,
                    'longitude' => (float) $device->longitude,
                    'speed' => (float) ($device->speed ?? 0),
                    'is_moving' => (bool) $device->is_moving,
                    'last_location_update' => $device->last_location_update ? $device->last_location_update->toISOString() : null,
                    'battery_level' => $device->battery_level,
                    'heading' => $device->heading,
                    'altitude' => $device->altitude,
                    'satellites' => $device->satellites,
                    'status' => $device->status,
                    'is_online' => $device->is_online,
                ];
            });

        return response()->json($devices);
    }
}
