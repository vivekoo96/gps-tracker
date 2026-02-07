<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceCommandController extends Controller
{
    /**
     * Cut off vehicle engine/power
     */
    public function cutOff(Request $request, $deviceId)
    {
        $device = Device::findOrFail($deviceId);

        // Safety check: Only allow if vehicle is stationary or slow
        if ($device->speed > 20) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cut off engine while vehicle is moving above 20 km/h. Current speed: ' . $device->speed . ' km/h'
            ], 400);
        }

        // Create command
        $command = DeviceCommand::create([
            'device_id' => $device->id,
            'command_type' => 'cut_off',
            'command_hex' => DeviceCommand::generateCutOffCommand(),
            'status' => 'pending',
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cut-off command queued. It will be sent when the device next connects.',
            'command' => $command
        ]);
    }

    /**
     * Restore vehicle engine/power
     */
    public function restore(Request $request, $deviceId)
    {
        $device = Device::findOrFail($deviceId);

        // Create command
        $command = DeviceCommand::create([
            'device_id' => $device->id,
            'command_type' => 'restore',
            'command_hex' => DeviceCommand::generateRestoreCommand(),
            'status' => 'pending',
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Restore command queued. It will be sent when the device next connects.',
            'command' => $command
        ]);
    }

    /**
     * Get command history for a device
     */
    public function history($deviceId)
    {
        $commands = DeviceCommand::where('device_id', $deviceId)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'commands' => $commands
        ]);
    }
}
