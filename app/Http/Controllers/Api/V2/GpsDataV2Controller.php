<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Device;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GpsDataV2Controller extends BaseV2Controller
{
    /**
     * Get paginated tracking history for a device.
     */
    public function history(Request $request, string $deviceId): JsonResponse
    {
        $device = Device::find($deviceId);

        if (!$device) {
            return $this->error('Device not found', 'NOT_FOUND', 404);
        }

        $query = Position::where('device_id', $device->id);

        if ($request->has('from')) {
            $query->where('fix_time', '>=', Carbon::parse($request->get('from')));
        }

        if ($request->has('to')) {
            $query->where('fix_time', '<=', Carbon::parse($request->get('to')));
        }

        return $this->paginate($request, $query);
    }

    /**
     * Get latest locations for all devices.
     */
    public function latest(Request $request): JsonResponse
    {
        $query = Device::whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            if (in_array('latest_position', $includes)) {
                $query->with('latest_position');
            }
        }

        return $this->paginate($request, $query);
    }

    /**
     * Store new GPS data (Integration endpoint).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'imei' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'altitude' => 'nullable|numeric',
            'course' => 'nullable|numeric|between:0,359',
            'fix_time' => 'nullable|date',
            'attributes' => 'nullable|array',
        ]);

        $device = Device::where('imei', $validated['imei'])->first();

        if (!$device) {
            return $this->error('Device not found', 'NOT_FOUND', 404);
        }

        $position = Position::create([
            'device_id' => $device->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? 0,
            'altitude' => $validated['altitude'] ?? null,
            'course' => $validated['course'] ?? null,
            'fix_time' => $validated['fix_time'] ?? now(),
            'attributes' => isset($validated['attributes']) ? json_encode($validated['attributes']) : null,
        ]);

        // Trigger Geofence Checks
        try {
            app(\App\Services\GeofenceCheckService::class)->checkPosition($device, $position);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('API v2 Geofence check failed', ['error' => $e->getMessage()]);
        }

        // Update device status
        $device->update([
            'latitude' => $position->latitude,
            'longitude' => $position->longitude,
            'speed' => $position->speed,
            'last_location_update' => $position->fix_time,
            'status' => 'active',
            'is_moving' => $position->speed > 1,
        ]);

        return $this->success($position, 'GPS data stored successfully', 201);
    }
}
