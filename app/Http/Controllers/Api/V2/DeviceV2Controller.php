<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceV2Controller extends BaseV2Controller
{
    /**
     * Display a listing of devices.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Device::query();
        
        // Filter by vendor if not superadmin (IdentifyVendor middleware handles global scope usually)
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by type
        if ($request->has('device_type')) {
            $query->where('device_type', $request->get('device_type'));
        }

        return $this->paginate($request, $query);
    }

    /**
     * Display the specified device.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $device = Device::find($id);

        if (!$device) {
            return $this->error('Device not found', 'NOT_FOUND', 404);
        }

        // Include latest position if requested
        if ($request->has('include') && in_array('latest_position', explode(',', $request->get('include')))) {
            $device->load('latest_position');
        }

        return $this->success($device);
    }
}
