<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Device;
use App\Models\Dashcam;

class DashcamController extends Controller
{
    public function index()
    {
        $dashcams = Dashcam::whereHas('device', function($query) {
             // Global scope handles vendor check
        })->with('device')->latest()->paginate(10);

        return view('vendor.dashcam.index', compact('dashcams'));
    }

    public function create()
    {
        // Only show devices that don't have a dashcam yet
        // Allow both 'dashcam' specific devices AND standard 'gps' devices to have cameras
        $devices = Device::whereDoesntHave('dashcam')
                        ->whereIn('device_type', ['dashcam', 'gps'])
                        ->get();
        return view('vendor.dashcam.create', compact('devices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'camera_model' => 'nullable|string|max:100',
            'resolution' => 'required|string|in:720p,1080p,2K,4K',
            'storage_capacity' => 'nullable|string|max:20',
        ]);

        Dashcam::create($validated);

        return redirect()->route('vendor.dashcam.index')->with('success', 'Dashcam added successfully');
    }
}
