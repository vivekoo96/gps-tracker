<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TestGpsController extends Controller
{
    /**
     * Simple GPS data receiver for testing (no authentication, minimal validation)
     */
    public function receiveTestData(Request $request): JsonResponse
    {
        try {
            // Always return JSON
            $response = [
                'status' => 'received',
                'timestamp' => now()->toISOString(),
                'method' => $request->method(),
                'data_received' => $request->all(),
            ];

            // Basic GPS data extraction
            $deviceId = $request->input('device_id', 'TEST_DEVICE_' . rand(100, 999));
            $latitude = (float) $request->input('latitude', $request->input('lat', 0));
            $longitude = (float) $request->input('longitude', $request->input('lng', 0));
            $speed = (float) $request->input('speed', 0);

            if ($latitude && $longitude) {
                // Try to create/update device
                try {
                    $device = Device::firstOrCreate(
                        ['unique_id' => $deviceId],
                        [
                            'name' => "Test Device {$deviceId}",
                            'imei' => $deviceId,
                            'model' => 'Test GPS Tracker',
                            'device_type' => 'gps_tracker',
                            'status' => 'active',
                            'creator' => 'test_system',
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'speed' => $speed,
                            'last_location_update' => now(),
                            'is_moving' => $speed > 1,
                        ]
                    );

                    // Update existing device
                    $device->update([
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'speed' => $speed,
                        'last_location_update' => now(),
                        'is_moving' => $speed > 1,
                        'status' => 'active',
                    ]);

                    // Create position record
                    $position = Position::create([
                        'device_id' => $device->id,
                        'fix_time' => now(),
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'speed' => $speed,
                        'course' => $request->input('heading', 0),
                        'altitude' => $request->input('altitude', 0),
                        'satellites' => $request->input('satellites', 0),
                        'attributes' => json_encode([
                            'battery_level' => $request->input('battery', 100),
                            'test_data' => true,
                            'raw_request' => $request->all(),
                        ]),
                    ]);

                    $response['device_created'] = true;
                    $response['device_id'] = $device->id;
                    $response['position_id'] = $position->id;
                    $response['message'] = 'GPS data processed successfully';

                } catch (\Exception $dbError) {
                    $response['device_created'] = false;
                    $response['db_error'] = $dbError->getMessage();
                    $response['message'] = 'GPS data received but database error occurred';
                }
            } else {
                $response['message'] = 'GPS data received but no valid coordinates';
            }

            Log::info('Test GPS Data Processed', $response);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $errorResponse = [
                'status' => 'error',
                'message' => 'Failed to process test GPS data',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ];

            Log::error('Test GPS Controller Error', $errorResponse);

            return response()->json($errorResponse, 500);
        }
    }

    /**
     * Health check endpoint
     */
    public function healthCheck(): JsonResponse
    {
        try {
            Log::info('Health check accessed');
            
            return response()->json([
                'status' => 'ok',
                'message' => 'GPS receiver is working perfectly!',
                'timestamp' => now()->toISOString(),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'endpoints' => [
                    'health' => url('/gps/health'),
                    'test' => url('/gps/test'),
                    'production' => url('/gps/data'),
                ],
                'database_status' => 'connected',
                'device_count' => \App\Models\Device::count(),
            ], 200, [
                'Content-Type' => 'application/json'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Health check failed',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500, [
                'Content-Type' => 'application/json'
            ]);
        }
    }

    /**
     * Get recent test data
     */
    public function getTestData(): JsonResponse
    {
        try {
            $devices = Device::where('creator', 'test_system')
                ->with(['positions' => function($query) {
                    $query->latest()->take(5);
                }])
                ->latest()
                ->take(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'test_devices' => $devices,
                'count' => $devices->count(),
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }
}
