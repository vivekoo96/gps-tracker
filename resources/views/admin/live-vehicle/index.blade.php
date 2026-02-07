@extends('layouts.admin')

@section('title', 'Live Vehicle View')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Live Vehicle View</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Real-time vehicle tracking with comprehensive information</p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zone</label>
                <select id="zoneFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                    <option value="">All Zones</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ward</label>
                <select id="wardFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                    <option value="">All Wards</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transfer Station</label>
                <select id="transferStationFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                    <option value="">All Stations</option>
                    @foreach($transferStations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vehicle</label>
                <select id="vehicleFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                    <option value="">Select Vehicle</option>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->vehicle_no }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button id="loadVehicleBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    Load Vehicle
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Vehicle Info Panel -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Vehicle Information</h2>
                <div id="vehicleInfo" class="space-y-3">
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">Select a vehicle to view details</p>
                </div>
            </div>

            <!-- Alerts Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Recent Alerts</h2>
                <div id="alertsPanel" class="space-y-2 max-h-64 overflow-y-auto">
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">No alerts</p>
                </div>
            </div>
        </div>

        <!-- Map View -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div id="liveMap" style="height: 600px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map, currentMarker, pathPolyline;
let autoRefreshInterval = null;
let currentVehicleId = null;

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    map = L.map('liveMap').setView([23.5880, 87.2680], 6);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
});

// Load vehicle button
document.getElementById('loadVehicleBtn').addEventListener('click', loadVehicle);

async function loadVehicle() {
    const vehicleId = document.getElementById('vehicleFilter').value;
    const zoneId = document.getElementById('zoneFilter').value;
    const wardId = document.getElementById('wardFilter').value;
    const transferStationId = document.getElementById('transferStationFilter').value;
    
    if (!vehicleId && !zoneId && !wardId && !transferStationId) {
        alert('Please select at least one filter');
        return;
    }
    
    try {
        const response = await fetch('{{ route("admin.live-vehicle.info") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                vehicle_id: vehicleId,
                zone_id: zoneId,
                ward_id: wardId,
                transfer_station_id: transferStationId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentVehicleId = data.vehicle.id;
            displayVehicleInfo(data);
            await loadVehiclePath(data.vehicle.id);
            
            // Start auto-refresh
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshInterval = setInterval(() => loadVehicle(), 10000); // 10 seconds
        } else {
            alert(data.error || 'Failed to load vehicle');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load vehicle');
    }
}

function displayVehicleInfo(data) {
    const { vehicle, current_position, statistics, alerts } = data;
    
    // Vehicle Info
    document.getElementById('vehicleInfo').innerHTML = `
        <div class="space-y-2">
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Vehicle:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${vehicle.name}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Vehicle No:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${vehicle.vehicle_no}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Type:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${vehicle.vehicle_type}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Contact:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${vehicle.contact}</span>
            </div>
            <hr class="border-gray-200 dark:border-gray-700">
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Zone:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${vehicle.zone}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Ward:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${vehicle.ward}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Transfer Station:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${vehicle.transfer_station}</span>
            </div>
            <hr class="border-gray-200 dark:border-gray-700">
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Current Speed:</span>
                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">${current_position.speed} km/h</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Max Speed:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${statistics.max_speed} km/h</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Avg Speed:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${statistics.avg_speed} km/h</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Trip Time:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${statistics.trip_time}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Idle Time:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${statistics.idle_time}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Distance:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${statistics.distance} km</span>
            </div>
            <hr class="border-gray-200 dark:border-gray-700">
            <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Last Update:</span>
                <span class="text-xs font-semibold text-green-600 dark:text-green-400">${current_position.last_update}</span>
            </div>
        </div>
    `;
    
    // Alerts
    if (alerts.length > 0) {
        document.getElementById('alertsPanel').innerHTML = alerts.map(alert => {
            const icons = {
                'ignition_on': '🟢',
                'ignition_off': '🔴',
                'overspeeding': '⚡',
                'battery_removal': '🔋',
                'no_communication': '📡'
            };
            
            return `
                <div class="flex items-center justify-between p-2 border border-gray-200 dark:border-gray-700 rounded">
                    <span class="text-lg">${icons[alert.type] || '⚠️'}</span>
                    <span class="text-sm text-gray-900 dark:text-gray-100 flex-1 mx-2">${alert.type.replace(/_/g, ' ').toUpperCase()}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">${alert.time}</span>
                </div>
            `;
        }).join('');
    } else {
        document.getElementById('alertsPanel').innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-4">No alerts</p>';
    }
    
    // Update map marker
    if (currentMarker) {
        map.removeLayer(currentMarker);
    }
    
    currentMarker = L.marker([current_position.latitude, current_position.longitude], {
        icon: L.divIcon({
            html: '<div style="font-size: 24px;">🚛</div>',
            className: 'vehicle-marker',
            iconSize: [30, 30]
        })
    }).addTo(map);
    
    currentMarker.bindPopup(`
        <strong>${vehicle.name}</strong><br>
        ${vehicle.vehicle_no}<br>
        Speed: ${current_position.speed} km/h<br>
        ${current_position.last_update}
    `).openPopup();
    
    map.setView([current_position.latitude, current_position.longitude], 14);
}

async function loadVehiclePath(vehicleId) {
    try {
        const response = await fetch('{{ route("admin.live-vehicle.path") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                vehicle_id: vehicleId,
                duration: 60 // last 60 minutes
            })
        });
        
        const data = await response.json();
        
        if (data.success && data.positions.length > 0) {
            // Remove old path
            if (pathPolyline) {
                map.removeLayer(pathPolyline);
            }
            
            // Draw new path
            const pathCoords = data.positions.map(p => [p.lat, p.lng]);
            pathPolyline = L.polyline(pathCoords, {
                color: '#3B82F6',
                weight: 3,
                opacity: 0.7
            }).addTo(map);
        }
    } catch (error) {
        console.error('Error loading path:', error);
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
});
</script>
@endsection
