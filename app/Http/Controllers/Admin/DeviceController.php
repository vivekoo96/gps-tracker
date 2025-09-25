<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(): View
    {
        $devices = Device::query()->latest('id')->get();
        return view('admin.devices.index', compact('devices'));
    }

    public function create(): View
    {
        return view('admin.devices.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // General tab
            'name' => ['required', 'string', 'max:100'],
            'unit_type' => ['required', 'string', 'max:50'],
            'device_type' => ['required', 'string', 'max:50'],
            'server_address' => ['nullable', 'string', 'max:255'],
            'unique_id' => ['required', 'string', 'max:32', 'unique:devices,unique_id'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'max:50'],
            'creator' => ['required', 'string', 'max:100'],
            'account' => ['nullable', 'string', 'max:100'],
            
            // Sensors tab
            'mileage_counter' => ['nullable', 'string', 'max:20'],
            'mileage_current_value' => ['nullable', 'numeric', 'min:0'],
            'mileage_auto' => ['nullable', 'boolean'],
            'engine_hours_counter' => ['nullable', 'string', 'max:50'],
            'engine_hours_current_value' => ['nullable', 'numeric', 'min:0'],
            'engine_hours_auto' => ['nullable', 'boolean'],
            'gprs_traffic_counter' => ['nullable', 'numeric', 'min:0'],
            'gprs_traffic_auto' => ['nullable', 'boolean'],
            
            // Status
            'status' => ['required', Rule::in(['inactive', 'active'])],
        ]);

        Device::create($validated);

        return redirect()->route('admin.devices.index')->with('status', 'Device created successfully');
    }

    public function edit(Device $device): View
    {
        return view('admin.devices.edit', compact('device'));
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            // General tab
            'name' => ['required', 'string', 'max:100'],
            'unit_type' => ['required', 'string', 'max:50'],
            'device_type' => ['required', 'string', 'max:50'],
            'server_address' => ['nullable', 'string', 'max:255'],
            'unique_id' => ['required', 'string', 'max:32', Rule::unique('devices', 'unique_id')->ignore($device->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'max:50'],
            'creator' => ['required', 'string', 'max:100'],
            'account' => ['nullable', 'string', 'max:100'],
            
            // Sensors tab
            'mileage_counter' => ['nullable', 'string', 'max:20'],
            'mileage_current_value' => ['nullable', 'numeric', 'min:0'],
            'mileage_auto' => ['nullable', 'boolean'],
            'engine_hours_counter' => ['nullable', 'string', 'max:50'],
            'engine_hours_current_value' => ['nullable', 'numeric', 'min:0'],
            'engine_hours_auto' => ['nullable', 'boolean'],
            'gprs_traffic_counter' => ['nullable', 'numeric', 'min:0'],
            'gprs_traffic_auto' => ['nullable', 'boolean'],
            
            // Status
            'status' => ['required', Rule::in(['inactive', 'active'])],
        ]);

        $device->update($validated);

        return redirect()->route('admin.devices.index')->with('status', 'Device updated');
    }

    public function destroy(Device $device): RedirectResponse
    {
        $device->delete();
        return redirect()->route('admin.devices.index')->with('status', 'Device deleted');
    }
}


