<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\FuelSensor;

class FuelSensorController extends Controller
{
    public function index()
    {
        // Fetch fuel sensors for devices owned by the vendor
        $sensors = FuelSensor::whereHas('device', function($query) {
             // Global scope handles vendor check on Device
        })->with('device')->latest()->paginate(10);

        return view('vendor.fuel.index', compact('sensors'));
    }

    public function create()
    {
        // Only show devices that don't have a fuel sensor yet
        // Allow both 'fuel' specific devices AND standard 'gps' devices to have sensors
        // Only show devices that don't have a fuel sensor yet
        // Allow both 'fuel' specific devices AND standard 'gps' devices to have sensors
        $devices = Device::whereDoesntHave('fuelSensor')
                        ->whereIn('device_type', ['fuel', 'gps'])
                        ->get();
        return view('vendor.fuel.create', compact('devices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'tank_capacity' => 'required|numeric|min:1',
            'calibration_data' => 'nullable|json',
            'data_source' => 'nullable|string|max:50',
        ]);

        FuelSensor::create($validated);

        return redirect()->route('vendor.fuel.index')->with('success', 'Fuel Sensor added successfully');
    }
}
