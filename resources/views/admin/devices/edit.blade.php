<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Device') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.devices.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Devices
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-md">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <strong>Please fix the following errors:</strong>
                    </div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.devices.update', $device) }}" id="device-edit-form">
                @csrf
                @method('PUT')
                
                <!-- Device Information Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit Device Information</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Update the details for your GPS device</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <!-- Primary Device Configuration Section -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Device Configuration
                            </h4>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Device Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" value="{{ old('name', $device->name) }}" 
                                           placeholder="e.g., GPS-001, Vehicle-A" 
                                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out" 
                                           required>
                                    @error('name')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="device_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Device Model <span class="text-red-500">*</span>
                                    </label>
                                <select id="device_type" name="device_type" 
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out" 
                                        onchange="updateServerAddress()" 
                                        required>
                                    <option value="">Select device model</option>
                                    
                                    <!-- GPS Trackers - All configured for YOUR server -->
                                    <option value="GT800" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'GT800')>GT800 - Advanced GPS Tracker</option>
                                    <option value="WanWay EV02" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'WanWay EV02')>WanWay EV02 - Electric Vehicle Tracker</option>
                                    <option value="Concox GT06N" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'Concox GT06N')>Concox GT06N - Basic GPS Tracker</option>
                                    
                                    <!-- TK Series Devices (Port 8082) -->
                                    <option value="TK102" data-server="{{ request()->getHost() }}:8082" @selected(old('device_type', $device->device_type ?? $device->model) === 'TK102')>TK102 - Mini GPS Tracker</option>
                                    <option value="TK103" data-server="{{ request()->getHost() }}:8082" @selected(old('device_type', $device->device_type ?? $device->model) === 'TK103')>TK103 - Vehicle GPS Tracker</option>
                                    <option value="TK104" data-server="{{ request()->getHost() }}:8082" @selected(old('device_type', $device->device_type ?? $device->model) === 'TK104')>TK104 - Advanced Vehicle Tracker</option>
                                    <option value="TK105" data-server="{{ request()->getHost() }}:8082" @selected(old('device_type', $device->device_type ?? $device->model) === 'TK105')>TK105 - Motorcycle Tracker</option>
                                    
                                    <!-- Concox Devices (Port 5023) -->
                                    <option value="Concox GT06" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'Concox GT06')>Concox GT06 - Vehicle Tracker</option>
                                    <option value="Concox GT300" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'Concox GT300')>Concox GT300 - Personal Tracker</option>
                                    <option value="Concox AT4" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'Concox AT4')>Concox AT4 - Asset Tracker</option>
                                    <option value="Concox HVT001" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'Concox HVT001')>Concox HVT001 - Heavy Vehicle Tracker</option>
                                    
                                    <!-- Teltonika Devices (Port 5027) -->
                                    <option value="Teltonika FMB920" data-server="{{ request()->getHost() }}:5027" @selected(old('device_type', $device->device_type ?? $device->model) === 'Teltonika FMB920')>Teltonika FMB920 - Fleet Management</option>
                                    <option value="Teltonika FMB130" data-server="{{ request()->getHost() }}:5027" @selected(old('device_type', $device->device_type ?? $device->model) === 'Teltonika FMB130')>Teltonika FMB130 - Basic Tracker</option>
                                    <option value="Teltonika FMC130" data-server="{{ request()->getHost() }}:5027" @selected(old('device_type', $device->device_type ?? $device->model) === 'Teltonika FMC130')>Teltonika FMC130 - CAN Bus Tracker</option>
                                    <option value="Teltonika FMB125" data-server="{{ request()->getHost() }}:5027" @selected(old('device_type', $device->device_type ?? $device->model) === 'Teltonika FMB125')>Teltonika FMB125 - Compact Tracker</option>
                                    
                                    <!-- Queclink Devices (Port 6001) -->
                                    <option value="Queclink GV300" data-server="{{ request()->getHost() }}:6001" @selected(old('device_type', $device->device_type ?? $device->model) === 'Queclink GV300')>Queclink GV300 - Vehicle Tracker</option>
                                    <option value="Queclink GT300" data-server="{{ request()->getHost() }}:6001" @selected(old('device_type', $device->device_type ?? $device->model) === 'Queclink GT300')>Queclink GT300 - Personal Tracker</option>
                                    <option value="Queclink GV500" data-server="{{ request()->getHost() }}:6001" @selected(old('device_type', $device->device_type ?? $device->model) === 'Queclink GV500')>Queclink GV500 - Advanced Vehicle Tracker</option>
                                    <option value="Queclink GL300" data-server="{{ request()->getHost() }}:6001" @selected(old('device_type', $device->device_type ?? $device->model) === 'Queclink GL300')>Queclink GL300 - Personal GPS Logger</option>
                                    
                                    <!-- Other Popular Models -->
                                    <option value="GT06N" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'GT06N')>GT06N - Basic GPS Tracker</option>
                                    <option value="MT100" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'MT100')>MT100 - Mini GPS Tracker</option>
                                    <option value="H02" data-server="{{ request()->getHost() }}:6001" @selected(old('device_type', $device->device_type ?? $device->model) === 'H02')>H02 - Watch GPS Tracker</option>
                                    <option value="ST901" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'ST901')>ST901 - Vehicle Tracker</option>
                                    <option value="A6" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'A6')>A6 - Personal GPS Tracker</option>
                                    
                                    <!-- OBD Trackers -->
                                    <option value="OBD TK206" data-server="{{ request()->getHost() }}:8082" @selected(old('device_type', $device->device_type ?? $device->model) === 'OBD TK206')>TK206 - OBD GPS Tracker</option>
                                    <option value="OBD GT02A" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'OBD GT02A')>GT02A - OBD Vehicle Tracker</option>
                                    <option value="OBD Concox OB22" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'OBD Concox OB22')>Concox OB22 - OBD Tracker</option>
                                    
                                    <!-- Custom Device Option -->
                                    <option value="Custom Device" data-server="{{ request()->getHost() }}:5023" @selected(old('device_type', $device->device_type ?? $device->model) === 'Custom Device')>Custom Device - Manual Configuration</option>
                                </select>
                                @error('device_type')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="server_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    GPS Server Address <span class="text-blue-500 cursor-help" title="Automatically set based on selected device">ℹ️</span>
                                </label>
                                <input type="text" id="server_address" name="server_address" value="{{ old('server_address', $device->server_address) }}" 
                                       placeholder="Select a device model to auto-configure server address" 
                                       class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out bg-gray-50 dark:bg-gray-600" 
                                       readonly>
                                @error('server_address')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        </div>

                        <!-- Additional Device Details Section -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Device Details
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="unique_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Unique ID / IMEI <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="unique_id" name="unique_id" value="{{ old('unique_id', $device->unique_id ?? $device->imei) }}" 
                                           placeholder="Enter IMEI or unique identifier" 
                                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out" 
                                           required>
                                    @error('unique_id')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        SIM Phone Number
                                    </label>
                                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $device->phone_number ?? $device->sim_number) }}" 
                                           placeholder="+91-9876543210" 
                                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                                    @error('phone_number')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Device Status <span class="text-red-500">*</span>
                                    </label>
                                    <select id="status" name="status" 
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out" 
                                            required>
                                        <option value="active" @selected(old('status', $device->status) === 'active')>✅ Active</option>
                                        <option value="inactive" @selected(old('status', $device->status) === 'inactive')>❌ Inactive</option>
                                    </select>
                                    @error('status')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GHMC Operational Details Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">GHMC Operational Details</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Assign vehicle, driver, and administrative area</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Vehicle No & Type -->
                            <div>
                                <label for="vehicle_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vehicle Number</label>
                                <input type="text" id="vehicle_no" name="vehicle_no" value="{{ old('vehicle_no', $device->vehicle_no) }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="vehicle_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vehicle Type</label>
                                <input type="text" id="vehicle_type" name="vehicle_type" value="{{ old('vehicle_type', $device->vehicle_type) }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Driver Info -->
                            <div>
                                <label for="driver_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Driver Name</label>
                                <input type="text" id="driver_name" name="driver_name" value="{{ old('driver_name', $device->driver_name) }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="driver_contact" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Driver Contact</label>
                                <input type="text" id="driver_contact" name="driver_contact" value="{{ old('driver_contact', $device->driver_contact) }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Administrative Hierarchy -->
                            <div>
                                <label for="zone_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zone</label>
                                <select id="zone_id" name="zone_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select Zone</option>
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}" {{ old('zone_id', $device->zone_id) == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="circle_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Circle</label>
                                <select id="circle_id" name="circle_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select Circle</option>
                                    @foreach($circles as $circle)
                                        <option value="{{ $circle->id }}" {{ old('circle_id', $device->circle_id) == $circle->id ? 'selected' : '' }}>{{ $circle->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="ward_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ward</label>
                                <select id="ward_id" name="ward_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select Ward</option>
                                    @foreach($wards as $ward)
                                        <option value="{{ $ward->id }}" {{ old('ward_id', $device->ward_id) == $ward->id ? 'selected' : '' }}>{{ $ward->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="transfer_station_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transfer Station</label>
                                <select id="transfer_station_id" name="transfer_station_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select Station</option>
                                    @foreach($transferStations as $station)
                                        <option value="{{ $station->id }}" {{ old('transfer_station_id', $device->transfer_station_id) == $station->id ? 'selected' : '' }}>{{ $station->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <button type="button" onclick="resetForm()" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Reset Form
                                </button>
                            </div>
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('admin.devices.index') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Update Device
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Update server address based on device type
        function updateServerAddress() {
            const deviceSelect = document.getElementById('device_type');
            const selectedOption = deviceSelect.options[deviceSelect.selectedIndex];
            const serverAddress = selectedOption.getAttribute('data-server');
            const serverInput = document.getElementById('server_address');
            
            if (serverAddress) {
                serverInput.value = serverAddress;
            } else {
                serverInput.value = '';
            }
        }

        // Reset form
        function resetForm() {
            if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
                document.getElementById('device-edit-form').reset();
                document.getElementById('server_address').value = '';
            }
        }

        // Set initial server address on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateServerAddress();
        });

        // Form validation
        document.getElementById('device-edit-form').addEventListener('submit', function(e) {
            const requiredFields = ['name', 'device_type', 'unique_id', 'status'];
            let isValid = true;
            
            requiredFields.forEach(function(fieldName) {
                const field = document.getElementById(fieldName);
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</x-app-layout>


