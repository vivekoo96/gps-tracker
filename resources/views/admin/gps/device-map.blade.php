<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Device Map - ') . $device->name }}
        </h2>
    </x-slot>
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('admin.gps.dashboard') }}" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">
                                    GPS Dashboard
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 text-gray-500 dark:text-gray-400">{{ $device->name }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center mt-2">
                        <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $device->name }} - Live Tracking
                    </h1>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.gps.device-history', $device->id) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                        📊 History
                    </a>
                    <button onclick="refreshMap()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                        🔄 Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Device Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($device->status === 'active')
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            @else
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ $device->status === 'active' ? 'Online' : 'Offline' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if($device->latestGpsData)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Speed</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $device->latestGpsData->speed }} km/h</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Satellites</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $device->latestGpsData->satellites ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Battery</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $device->latestGpsData->battery_level ?? 'N/A' }}%</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Map Container -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
                        </svg>
                        24-Hour Track
                    </h3>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Track Points: <span class="font-semibold">{{ $gpsTrack->count() }}</span>
                        </span>
                        @if($device->latestGpsData)
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Last Update: <span class="font-semibold">{{ $device->latestGpsData->recorded_at->diffForHumans() }}</span>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="p-0">
                <div id="deviceMap" style="height: 600px; width: 100%;"></div>
            </div>
        </div>

        @if($gpsTrack->count() > 0)
        <!-- Recent Locations Table -->
        <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Locations</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Speed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Direction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Altitude</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($gpsTrack->reverse()->take(10) as $gps)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $gps->recorded_at->format('M j, H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($gps->latitude, 6) }}, {{ number_format($gps->longitude, 6) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $gps->speed }} km/h
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $gps->direction }}°
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $gps->altitude ?? 'N/A' }} m
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <!-- No Data Message -->
        <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No GPS Data Found</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    This device hasn't sent any GPS data in the last 24 hours.
                </p>
                <div class="mt-6">
                    <a href="{{ route('admin.gps.add-test-data') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Add Test Data
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map
let deviceMap = L.map('deviceMap').setView([23.0225, 72.5714], 13);

// Add tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(deviceMap);

// GPS track data
const gpsTrack = @json($gpsTrack);

if (gpsTrack.length > 0) {
    // Create polyline for the track
    const trackPoints = gpsTrack.map(gps => [gps.latitude, gps.longitude]);
    const polyline = L.polyline(trackPoints, {color: 'blue', weight: 3, opacity: 0.7}).addTo(deviceMap);
    
    // Add markers for start and end points
    if (trackPoints.length > 0) {
        // Start point (green)
        const startPoint = trackPoints[0];
        L.marker(startPoint, {
            icon: L.divIcon({
                className: 'custom-marker',
                html: '<div style="background-color: #10b981; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        }).addTo(deviceMap).bindPopup(`
            <div class="p-2">
                <h4 class="font-bold text-green-600">Start Point</h4>
                <p><strong>Time:</strong> ${new Date(gpsTrack[0].recorded_at).toLocaleString()}</p>
                <p><strong>Speed:</strong> ${gpsTrack[0].speed} km/h</p>
            </div>
        `);
        
        // End point (red) - only if different from start
        if (trackPoints.length > 1) {
            const endPoint = trackPoints[trackPoints.length - 1];
            const endData = gpsTrack[gpsTrack.length - 1];
            L.marker(endPoint, {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div style="background-color: #ef4444; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(deviceMap).bindPopup(`
                <div class="p-2">
                    <h4 class="font-bold text-red-600">Current Position</h4>
                    <p><strong>Time:</strong> ${new Date(endData.recorded_at).toLocaleString()}</p>
                    <p><strong>Speed:</strong> ${endData.speed} km/h</p>
                    <p><strong>Battery:</strong> ${endData.battery_level || 'N/A'}%</p>
                </div>
            `);
        }
    }
    
    // Fit map to track bounds
    deviceMap.fitBounds(polyline.getBounds().pad(0.1));
} else {
    // No GPS data, show default location
    deviceMap.setView([23.0225, 72.5714], 10);
    
    // Add a placeholder marker
    L.marker([23.0225, 72.5714]).addTo(deviceMap)
        .bindPopup(`
            <div class="p-2">
                <h4 class="font-bold">{{ $device->name }}</h4>
                <p>No GPS data available</p>
            </div>
        `);
}

// Refresh function
function refreshMap() {
    window.location.reload();
}

// Auto-refresh every 60 seconds
setInterval(refreshMap, 60000);
</script>
</x-app-layout>
