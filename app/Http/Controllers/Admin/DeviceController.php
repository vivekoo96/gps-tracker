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
        $zones = \App\Models\Zone::all();
        $circles = \App\Models\Circle::all();
        $wards = \App\Models\Ward::all();
        $transferStations = \App\Models\TransferStation::all();
        
        return view('admin.devices.create', compact('zones', 'circles', 'wards', 'transferStations'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Enforce Subscription Limits for Vendors
        $user = auth()->user();
        if ($user->vendor_id && !$user->isSuperAdmin()) {
            $subscriptionService = app(\App\Services\SubscriptionService::class);
            if (!$subscriptionService->canCreateDevice($user->vendor)) {
                return back()->withInput()->withErrors(['limit' => 'You have reached the maximum number of devices allowed by your subscription plan.']);
            }
        }

        $validated = $request->validate([
            // General tab
            'name' => ['required', 'string', 'max:100'],
            'unit_type' => ['required', 'string', 'max:50'],
            'device_category' => ['required', 'in:gps,fuel,dashcam'], // Maps to DB device_type
            'device_model' => ['required', 'string', 'max:50'],       // Maps to DB model
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

            // GHMC Fields
            'vehicle_no' => ['nullable', 'string', 'max:20'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'driver_contact' => ['nullable', 'string', 'max:20'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'circle_id' => ['nullable', 'exists:circles,id'],
            'ward_id' => ['nullable', 'exists:wards,id'],
            'transfer_station_id' => ['nullable', 'exists:transfer_stations,id'],
        ]);

        // Map fields
        $data = $validated;
        $data['device_type'] = $validated['device_category']; // DB column is device_type
        $data['model'] = $validated['device_model'];          // DB column is model
        $data['imei'] = $validated['unique_id'];
        
        // Auto-assign vendor if not set (for Vendor Admins)
        if (auth()->user()->vendor_id) {
            $data['vendor_id'] = auth()->user()->vendor_id;
        }

        Device::create($data);

        return redirect()->route('admin.devices.index')->with('status', 'Device created successfully');
    }

    public function edit(Device $device): View
    {
        $zones = \App\Models\Zone::all();
        $circles = \App\Models\Circle::all();
        $wards = \App\Models\Ward::all();
        $transferStations = \App\Models\TransferStation::all();
        
        return view('admin.devices.edit', compact('device', 'zones', 'circles', 'wards', 'transferStations'));
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

            // GHMC Fields
            'vehicle_no' => ['nullable', 'string', 'max:20'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'driver_contact' => ['nullable', 'string', 'max:20'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'circle_id' => ['nullable', 'exists:circles,id'],
            'ward_id' => ['nullable', 'exists:wards,id'],
            'transfer_station_id' => ['nullable', 'exists:transfer_stations,id'],
        ]);

        // Map fields
        $data = $validated;
        // Map new fields to legacy fields for backward compatibility
        if (isset($validated['device_type'])) {
            $data['model'] = $validated['device_type'];
        }
        if (isset($validated['unique_id'])) {
            $data['imei'] = $validated['unique_id'];
        }
        
        $device->update($data);

        return redirect()->route('admin.devices.index')->with('status', 'Device updated');
    }

    public function destroy(Device $device): RedirectResponse
    {
        $device->delete();
        return redirect()->route('admin.devices.index')->with('status', 'Device deleted');
    }
}
