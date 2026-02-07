@extends('layouts.admin')

@section('title', 'Current Status - Garbage Collection')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Current Status Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Real-time garbage collection monitoring by zone/ward</p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zone</label>
                <select id="zoneSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                    <option value="">All Zones</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ward</label>
                <select id="wardSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                    <option value="">All Wards</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button id="loadBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    Load Status
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div id="statsCards" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Total Vehicles</p>
            <p id="totalVehicles" class="text-2xl font-bold text-gray-900 dark:text-gray-100">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Active</p>
            <p id="activeVehicles" class="text-2xl font-bold text-green-600 dark:text-green-400">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Idle</p>
            <p id="idleVehicles" class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Offline</p>
            <p id="offlineVehicles" class="text-2xl font-bold text-gray-600 dark:text-gray-400">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Collection Progress</p>
            <p id="collectionProgress" class="text-2xl font-bold text-blue-600 dark:text-blue-400">0%</p>
        </div>
    </div>

    <!-- Map and Vehicle List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Map -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div id="map" style="height: 600px;"></div>
            </div>
        </div>

        <!-- Vehicle List -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Vehicles</h2>
                <div id="vehicleList" class="space-y-3 max-h-[550px] overflow-y-auto">
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">Select zone/ward to view vehicles</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map, vehicleMarkers = [], collectionMarkers = [];
let autoRefreshInterval = null;

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    map = L.map('map').setView([23.5880, 87.2680], 6);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
});

// Load status
document.getElementById('loadBtn').addEventListener('click', loadStatus);

async function loadStatus() {
    const zoneId = document.getElementById('zoneSelect').value;
    const wardId = document.getElementById('wardSelect').value;
    
    try {
        const response = await fetch('{{ route("admin.status.data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ zone_id: zoneId, ward_id: wardId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayStatus(data);
            
            // Start auto-refresh
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshInterval = setInterval(loadStatus, 30000); // 30 seconds
        } else {
            alert(data.error || 'Failed to load status');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load status');
    }
}

function displayStatus(data) {
    // Update statistics
    document.getElementById('totalVehicles').textContent = data.statistics.total_vehicles;
    document.getElementById('activeVehicles').textContent = data.statistics.active;
    document.getElementById('idleVehicles').textContent = data.statistics.idle;
    document.getElementById('offlineVehicles').textContent = data.statistics.offline;
    document.getElementById('collectionProgress').textContent = data.statistics.completion_percentage + '%';
    document.getElementById('statsCards').classList.remove('hidden');
    
    // Clear existing markers
    vehicleMarkers.forEach(m => map.removeLayer(m));
    collectionMarkers.forEach(m => map.removeLayer(m));
    vehicleMarkers = [];
    collectionMarkers = [];
    
    // Add vehicle markers
    const bounds = [];
    data.vehicles.forEach(vehicle => {
        const color = vehicle.status_color === 'green' ? '#10B981' : 
                     vehicle.status_color === 'yellow' ? '#F59E0B' : '#6B7280';
        
        const marker = L.circleMarker([vehicle.latitude, vehicle.longitude], {
            radius: 8,
            fillColor: color,
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map);
        
        marker.bindPopup(`
            <strong>${vehicle.name}</strong><br>
            ${vehicle.vehicle_no}<br>
            Status: ${vehicle.status}<br>
            Speed: ${vehicle.speed} km/h<br>
            Collections: ${vehicle.collections_today}
        `);
        
        vehicleMarkers.push(marker);
        bounds.push([vehicle.latitude, vehicle.longitude]);
    });
    
    // Add collection point markers
    data.collection_points.forEach(cp => {
        const color = cp.status === 'collected' ? '#10B981' : 
                     cp.status === 'skipped' ? '#EF4444' : '#9CA3AF';
        
        const marker = L.circleMarker([cp.latitude, cp.longitude], {
            radius: 5,
            fillColor: color,
            color: '#fff',
            weight: 1,
            opacity: 1,
            fillOpacity: 0.6
        }).addTo(map);
        
        marker.bindPopup(`
            <strong>${cp.name}</strong><br>
            Status: ${cp.status}<br>
            Expected: ${cp.expected_time || 'N/A'}
        `);
        
        collectionMarkers.push(marker);
        bounds.push([cp.latitude, cp.longitude]);
    });
    
    // Fit map to bounds
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
    
    // Update vehicle list
    const vehicleList = document.getElementById('vehicleList');
    vehicleList.innerHTML = data.vehicles.map(v => `
        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-gray-900 dark:text-gray-100">${v.name}</span>
                <span class="px-2 py-1 text-xs rounded-full ${
                    v.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                    v.status === 'idle' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                    'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                }">${v.status.toUpperCase()}</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">${v.vehicle_no}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Speed: ${v.speed} km/h</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Collections: ${v.collections_today}</p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">${v.last_update}</p>
        </div>
    `).join('');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
});
</script>
@endsection
