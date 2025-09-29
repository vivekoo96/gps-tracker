<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Add New Device') }}
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

            <form method="POST" action="{{ route('admin.devices.store') }}" id="device-form">
                @csrf
                
                <!-- Device Information Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Basic Information</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Enter the basic details for your GPS device</p>
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
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" 
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
                                    <option value="GT800" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'GT800' ? 'selected' : '' }}>GT800 - Advanced GPS Tracker</option>
                                    <option value="WanWay EV02" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'WanWay EV02' ? 'selected' : '' }}>WanWay EV02 - Electric Vehicle Tracker</option>
                                    <option value="Concox GT06N" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'Concox GT06N' ? 'selected' : '' }}>Concox GT06N - Basic GPS Tracker</option>
                                    
                                    <!-- TK Series Devices (Port 8082) -->
                                    <option value="TK102" data-server="{{ request()->getHost() }}:8082" {{ old('device_type') == 'TK102' ? 'selected' : '' }}>TK102 - Mini GPS Tracker</option>
                                    <option value="TK103" data-server="{{ request()->getHost() }}:8082" {{ old('device_type') == 'TK103' ? 'selected' : '' }}>TK103 - Vehicle GPS Tracker</option>
                                    <option value="TK104" data-server="{{ request()->getHost() }}:8082" {{ old('device_type') == 'TK104' ? 'selected' : '' }}>TK104 - Advanced Vehicle Tracker</option>
                                    <option value="TK105" data-server="{{ request()->getHost() }}:8082" {{ old('device_type') == 'TK105' ? 'selected' : '' }}>TK105 - Motorcycle Tracker</option>
                                    
                                    <!-- Concox Devices (Port 5023) -->
                                    <option value="Concox GT06" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'Concox GT06' ? 'selected' : '' }}>Concox GT06 - Vehicle Tracker</option>
                                    <option value="Concox GT300" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'Concox GT300' ? 'selected' : '' }}>Concox GT300 - Personal Tracker</option>
                                    <option value="Concox AT4" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'Concox AT4' ? 'selected' : '' }}>Concox AT4 - Asset Tracker</option>
                                    <option value="Concox HVT001" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'Concox HVT001' ? 'selected' : '' }}>Concox HVT001 - Heavy Vehicle Tracker</option>
                                    
                                    <!-- Teltonika Devices (Port 5027) -->
                                    <option value="Teltonika FMB920" data-server="{{ request()->getHost() }}:5027" {{ old('device_type') == 'Teltonika FMB920' ? 'selected' : '' }}>Teltonika FMB920 - Fleet Management</option>
                                    <option value="Teltonika FMB130" data-server="{{ request()->getHost() }}:5027" {{ old('device_type') == 'Teltonika FMB130' ? 'selected' : '' }}>Teltonika FMB130 - Basic Tracker</option>
                                    <option value="Teltonika FMC130" data-server="{{ request()->getHost() }}:5027" {{ old('device_type') == 'Teltonika FMC130' ? 'selected' : '' }}>Teltonika FMC130 - CAN Bus Tracker</option>
                                    <option value="Teltonika FMB125" data-server="{{ request()->getHost() }}:5027" {{ old('device_type') == 'Teltonika FMB125' ? 'selected' : '' }}>Teltonika FMB125 - Compact Tracker</option>
                                    
                                    <!-- Queclink Devices (Port 6001) -->
                                    <option value="Queclink GV300" data-server="{{ request()->getHost() }}:6001" {{ old('device_type') == 'Queclink GV300' ? 'selected' : '' }}>Queclink GV300 - Vehicle Tracker</option>
                                    <option value="Queclink GT300" data-server="{{ request()->getHost() }}:6001" {{ old('device_type') == 'Queclink GT300' ? 'selected' : '' }}>Queclink GT300 - Personal Tracker</option>
                                    <option value="Queclink GV500" data-server="{{ request()->getHost() }}:6001" {{ old('device_type') == 'Queclink GV500' ? 'selected' : '' }}>Queclink GV500 - Advanced Vehicle Tracker</option>
                                    <option value="Queclink GL300" data-server="{{ request()->getHost() }}:6001" {{ old('device_type') == 'Queclink GL300' ? 'selected' : '' }}>Queclink GL300 - Personal GPS Logger</option>
                                    
                                    <!-- Other Popular Models -->
                                    <option value="GT06N" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'GT06N' ? 'selected' : '' }}>GT06N - Basic GPS Tracker</option>
                                    <option value="MT100" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'MT100' ? 'selected' : '' }}>MT100 - Mini GPS Tracker</option>
                                    <option value="H02" data-server="{{ request()->getHost() }}:6001" {{ old('device_type') == 'H02' ? 'selected' : '' }}>H02 - Watch GPS Tracker</option>
                                    <option value="ST901" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'ST901' ? 'selected' : '' }}>ST901 - Vehicle Tracker</option>
                                    <option value="A6" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'A6' ? 'selected' : '' }}>A6 - Personal GPS Tracker</option>
                                    
                                    <!-- OBD Trackers -->
                                    <option value="OBD TK206" data-server="{{ request()->getHost() }}:8082" {{ old('device_type') == 'OBD TK206' ? 'selected' : '' }}>TK206 - OBD GPS Tracker</option>
                                    <option value="OBD GT02A" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'OBD GT02A' ? 'selected' : '' }}>GT02A - OBD Vehicle Tracker</option>
                                    <option value="OBD Concox OB22" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'OBD Concox OB22' ? 'selected' : '' }}>Concox OB22 - OBD Tracker</option>
                                    
                                    <!-- Custom Device Option -->
                                    <option value="Custom Device" data-server="{{ request()->getHost() }}:5023" {{ old('device_type') == 'Custom Device' ? 'selected' : '' }}>Custom Device - Manual Configuration</option>
                                </select>
                                @error('device_type')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="server_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    GPS Server Address <span class="text-blue-500 cursor-help" title="Automatically set based on selected device">ℹ️</span>
                                </label>
                                <input type="text" id="server_address" name="server_address" value="{{ old('server_address') }}" 
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
                                    <label for="unit_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Unit Type <span class="text-red-500">*</span>
                                    </label>
                                    <select id="unit_type" name="unit_type" 
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out" 
                                            required>
                                        <option value="">Select unit type</option>
                                        <option value="vehicle" {{ old('unit_type') == 'vehicle' ? 'selected' : '' }}>🚗 Vehicle</option>
                                        <option value="person" {{ old('unit_type') == 'person' ? 'selected' : '' }}>👤 Person</option>
                                        <option value="asset" {{ old('unit_type') == 'asset' ? 'selected' : '' }}>📦 Asset</option>
                                        <option value="container" {{ old('unit_type') == 'container' ? 'selected' : '' }}>🚛 Container</option>
                                    </select>
                                    @error('unit_type')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="unique_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Unique ID / IMEI <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="unique_id" name="unique_id" value="{{ old('unique_id') }}" 
                                               placeholder="Enter IMEI or unique identifier" 
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out pr-10" 
                                               required>
                                        <button type="button" onclick="generateUniqueId()" 
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                                title="Generate Random ID">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    @error('unique_id')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        SIM Phone Number
                                    </label>
                                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" 
                                           placeholder="+91-9876543210" 
                                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                                    @error('phone_number')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Server Configuration Info Card -->
                <div class="bg-blue-50 dark:bg-blue-900/20 overflow-hidden shadow-sm rounded-xl border border-blue-200 dark:border-blue-800 mb-6">
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                                    📡 Device Configuration Instructions
                                </h3>
                                <div class="text-sm text-blue-800 dark:text-blue-200">
                                    <p class="font-medium mb-2">How to configure your GPS device:</p>
                                    <ul class="list-disc list-inside space-y-1 text-xs">
                                        <li>Send SMS to device: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">SERVER,<span id="server-display">Select a device first</span>,0#</code></li>
                                        <li>Or use device app to set server IP and port</li>
                                        <li>Server address is automatically configured based on your device selection</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuration Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Device Configuration</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Configure device settings and access</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Device Status <span class="text-red-500">*</span>
                                </label>
                                <select id="status" name="status" 
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out" 
                                        required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>✅ Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>❌ Inactive</option>
                                </select>
                                @error('status')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Device Password
                                </label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" value="{{ old('password') }}" 
                                           placeholder="Optional device password" 
                                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out pr-10">
                                    <button type="button" onclick="togglePassword()" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg id="password-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="creator" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Created By <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="creator" name="creator" value="{{ old('creator', auth()->user()->name) }}" 
                                       class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out" 
                                       readonly>
                                @error('creator')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="account" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Account
                                </label>
                                <input type="text" id="account" name="account" value="{{ old('account', auth()->user()->name) }}" 
                                       placeholder="Account name" 
                                       class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                                @error('account')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GPS Server Information Card -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 overflow-hidden shadow-sm rounded-xl border border-blue-200 dark:border-blue-800 mb-6">
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                                    📡 How GPS Server Address Works
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800 dark:text-blue-200">
                                    <div>
                                        <h4 class="font-medium mb-2">🔄 Data Flow:</h4>
                                        <ol class="list-decimal list-inside space-y-1 text-xs">
                                            <li>GPS device gets location from satellites</li>
                                            <li>Device sends data to your server address</li>
                                            <li>Your server receives and stores the data</li>
                                            <li>Your web app displays the location</li>
                                        </ol>
                                    </div>
                                    <div>
                                        <h4 class="font-medium mb-2">⚙️ Real Server Addresses by Brand:</h4>
                                        <ul class="list-disc list-inside space-y-1 text-xs">
                                            <li><strong>GT800, WanWay EV02, Concox GT06N:</strong> Pre-configured servers</li>
                                            <li><strong>TK Series:</strong> gps.tkstar.com (ports 8841-8845)</li>
                                            <li><strong>Concox devices:</strong> gps.concox.com (ports 8001-8005)</li>
                                            <li><strong>Teltonika devices:</strong> gps.teltonika.com (ports 5027-5030)</li>
                                            <li><strong>Queclink devices:</strong> gps.queclink.com (ports 6001-6004)</li>
                                            <li><strong>Other models:</strong> Brand-specific server addresses</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mt-4 p-3 bg-blue-100 dark:bg-blue-800/50 rounded-lg">
                                    <p class="text-xs text-blue-800 dark:text-blue-200">
                                        <strong>💡 Quick Start:</strong> Select your device model from the comprehensive list above and the real GPS server address will be automatically configured. 
                                        Each device has its specific server address and port according to the manufacturer's specifications. 
                                        Configure your GPS device to send data to the displayed server address.
                                    </p>
                                </div>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create Device
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Generate random unique ID
        function generateUniqueId() {
            const timestamp = Date.now().toString();
            const random = Math.random().toString(36).substring(2, 8);
            document.getElementById('unique_id').value = timestamp + random;
        }



        // Toggle password visibility
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            } else {
                passwordField.type = 'password';
                passwordIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        // Reset form
        function resetForm() {
            if (confirm('Are you sure you want to reset the form? All data will be lost.')) {
                document.getElementById('device-form').reset();
                document.getElementById('creator').value = '{{ auth()->user()->name }}';
                document.getElementById('account').value = '{{ auth()->user()->name }}';
                document.getElementById('status').value = 'active';
                document.getElementById('server_address').value = '';
                document.getElementById('server-display').textContent = 'Select a device first';
            }
        }

        // Form validation
        document.getElementById('device-form').addEventListener('submit', function(e) {
            const requiredFields = ['name', 'unit_type', 'device_type', 'unique_id', 'creator', 'status'];
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

        // Update server address based on device type
        function updateServerAddress() {
            const deviceSelect = document.getElementById('device_type');
            const selectedOption = deviceSelect.options[deviceSelect.selectedIndex];
            const serverAddress = selectedOption.getAttribute('data-server');
            const serverInput = document.getElementById('server_address');
            const serverDisplay = document.getElementById('server-display');
            
            if (serverAddress) {
                serverInput.value = serverAddress;
                serverDisplay.textContent = serverAddress;
            } else {
                serverInput.value = '';
                serverDisplay.textContent = 'Select a device first';
            }
        }

        // Auto-generate device name based on type and unit type
        document.getElementById('device_type').addEventListener('change', function() {
            const deviceType = this.value;
            const unitType = document.getElementById('unit_type').value;
            const nameField = document.getElementById('name');
            
            if (deviceType && unitType && !nameField.value) {
                const timestamp = new Date().getTime().toString().slice(-4);
                nameField.value = `${deviceType}-${unitType.toUpperCase()}-${timestamp}`;
            }
        });
    </script>
</x-app-layout>
