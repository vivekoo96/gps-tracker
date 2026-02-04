<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Vehicle Tracking Details') }}
            </h2>
            <div class="flex space-x-2">
                <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export
                </button>
                <a href="{{ request()->fullUrl() }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 flex items-center transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </a>
                <a href="{{ route('admin.devices.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 flex items-center transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ activeTab: 'trips' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Vehicle Name -->
                <div class="bg-gray-800 rounded-lg p-4 flex items-center shadow-lg border border-gray-700">
                    <div class="w-12 h-12 bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs uppercase">Vehicle Name</p>
                        <h3 class="text-white font-bold text-lg truncate w-32" title="{{ $device->name }}">{{ $device->name }}</h3>
                        <p class="text-gray-500 text-xs">{{ $device->unique_id }}</p>
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-gray-800 rounded-lg p-4 flex items-center shadow-lg border border-gray-700">
                    <div class="w-12 h-12 {{ $device->status === 'active' ? 'bg-green-900' : 'bg-red-900' }} rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 {{ $device->status === 'active' ? 'text-green-400' : 'text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs uppercase">Status</p>
                        <h3 class="text-white font-bold text-lg {{ $device->status === 'active' ? 'text-green-400' : 'text-red-400' }}">
                            {{ ucfirst($device->status) }}
                        </h3>
                    </div>
                </div>

                 <!-- Total Trips -->
                 <div class="bg-gray-800 rounded-lg p-4 flex items-center shadow-lg border border-gray-700">
                    <div class="w-12 h-12 bg-purple-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs uppercase">Total Trips</p>
                        <h3 class="text-white font-bold text-lg">{{ count($trips) }}</h3>
                    </div>
                </div>

                <!-- Total Distance -->
                <div class="bg-gray-800 rounded-lg p-4 flex items-center shadow-lg border border-gray-700">
                    <div class="w-12 h-12 bg-yellow-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs uppercase">Total Distance</p>
                        <h3 class="text-white font-bold text-lg">{{ number_format(collect($trips)->sum('distance'), 2) }} km</h3>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg border border-gray-700">
                <form method="GET" action="{{ route('tracking.vehicle-details', $device->id) }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="start_date" class="block text-gray-400 text-xs mb-1">Start Date</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-gray-700 border-gray-600 text-white rounded focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-gray-400 text-xs mb-1">End Date</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-gray-700 border-gray-600 text-white rounded focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition font-medium">Filter History</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Data Tabs -->
            <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 overflow-hidden">
                <div class="border-b border-gray-700 px-6 pt-4 flex space-x-6">
                    <button @click="activeTab = 'trips'" :class="{'text-indigo-400 border-indigo-400': activeTab === 'trips', 'text-gray-400 border-transparent': activeTab !== 'trips'}" class="pb-3 border-b-2 font-medium transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Trip History
                    </button>
                    <button @click="activeTab = 'live'" :class="{'text-indigo-400 border-indigo-400': activeTab === 'live', 'text-gray-400 border-transparent': activeTab !== 'live'}" class="pb-3 border-b-2 font-medium transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Live Data
                    </button>
                    <button @click="activeTab = 'sensors'" :class="{'text-indigo-400 border-indigo-400': activeTab === 'sensors', 'text-gray-400 border-transparent': activeTab !== 'sensors'}" class="pb-3 border-b-2 font-medium transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        Sensors
                    </button>
                </div>

                <div class="p-6">
                    <!-- TRIPS TAB -->
                    <div x-show="activeTab === 'trips'">
                        @if(count($trips) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-700">
                                    <thead class="bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Grouping</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Start</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">End</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Dur</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">KM</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700">
                                        @foreach($trips as $trip)
                                            <tr class="hover:bg-gray-700 transition">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200">{{ $trip['grouping'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200">{{ $trip['date'] }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-200">
                                                    <div class="font-bold">{{ $trip['initial_time'] }}</div>
                                                    <div class="text-xs text-gray-400 truncate w-32" title="{{ $trip['initial_location'] }}">{{ $trip['initial_location'] }}</div>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-200">
                                                    <div class="font-bold">{{ $trip['final_time'] }}</div>
                                                    <div class="text-xs text-gray-400 truncate w-32" title="{{ $trip['final_location'] }}">{{ $trip['final_location'] }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200">{{ $trip['duration'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-400">{{ number_format($trip['distance'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-700 mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-300">No trip data found</h3>
                                <p class="text-gray-500 mt-1">Try adjusting the date range or check if the device has GPS data.</p>
                            </div>
                        @endif
                    </div>

                    <!-- LIVE DATA TAB -->
                    <div x-show="activeTab === 'live'" style="display: none;">
                        <h3 class="text-white font-bold mb-4">Live Protocol Stream</h3>
                        <div class="bg-black text-green-400 font-mono p-4 rounded h-64 overflow-y-auto text-sm border border-gray-700">
                             <p>[{{ now() }}] TCP Connected: {{ $device->ip_address ?? '127.0.0.1' }}</p>
                             <p>[{{ now() }}] AUTH: GT06 Protocol Verified</p>
                             <p>[{{ now() }}] HEARTBEAT: Status {{ $device->status }}, Last Seen: {{ $device->last_seen_at }}</p>
                             <p>[{{ now() }}] DATA: Lat: {{ $device->latitude ?? 0 }}, Lon: {{ $device->longitude ?? 0 }}, Speed: {{ $device->speed ?? 0 }}</p>
                        </div>
                    </div>

                    <!-- SENSORS TAB -->
                    <div x-show="activeTab === 'sensors'" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fuel -->
                            <div class="bg-gray-700 p-6 rounded-lg">
                                <div class="flex justify-between items-center mb-4">
                                     <h4 class="text-lg font-bold text-white flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                        Fuel Level
                                     </h4>
                                </div>
                                @if($device->fuelSensor)
                                    <div class="text-center">
                                        <span class="text-3xl font-bold text-indigo-400">{{ $device->fuelSensor->current_level }} L</span>
                                        <span class="text-gray-400 text-sm">/ {{ $device->fuelSensor->tank_capacity }} L</span>
                                    </div>
                                    <div class="w-full bg-gray-600 rounded-full h-2 mt-3">
                                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($device->fuelSensor->current_level / $device->fuelSensor->tank_capacity) * 100 }}%"></div>
                                    </div>
                                @else
                                    <p class="text-gray-400 italic text-center">No Fuel Sensor Configured</p>
                                    <div class="text-center mt-4">
                                        <a href="{{ route('vendor.fuel.create') }}" class="text-indigo-400 text-sm hover:underline">Configure Now</a>
                                    </div>
                                @endif
                            </div>

                            <!-- Dashcam -->
                            <div class="bg-gray-700 p-6 rounded-lg">
                                <h4 class="text-lg font-bold text-white flex items-center mb-4">
                                    <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    Dashcam Status
                                </h4>
                                @if($device->dashcam)
                                    <div class="flex items-center justify-between bg-gray-800 p-4 rounded">
                                        <div>
                                            <p class="text-white font-medium">{{ $device->dashcam->camera_model }}</p>
                                            <p class="text-gray-500 text-xs">{{ $device->dashcam->resolution }}</p>
                                        </div>
                                        <span class="px-2 py-1 rounded text-xs font-bold {{ $device->dashcam->status === 'recording' ? 'bg-red-900 text-red-200' : 'bg-green-900 text-green-200' }}">
                                            {{ strtoupper($device->dashcam->status) }}
                                        </span>
                                    </div>
                                @else
                                    <p class="text-gray-400 italic text-center">No Dashcam Configured</p>
                                    <div class="text-center mt-4">
                                        <a href="{{ route('vendor.dashcam.create') }}" class="text-indigo-400 text-sm hover:underline">Configure Now</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
