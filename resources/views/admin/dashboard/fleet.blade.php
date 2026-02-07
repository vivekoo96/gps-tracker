@extends('layouts.admin')

@section('title', 'Fleet Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Fleet Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Real-time fleet status overview</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Total Vehicles</p>
            <p id="totalVehicles" class="text-2xl font-bold text-gray-900 dark:text-gray-100">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 cursor-pointer hover:bg-green-50 dark:hover:bg-green-900" onclick="filterByStatus('running')">
            <p class="text-sm text-gray-600 dark:text-gray-400">Running</p>
            <p id="runningVehicles" class="text-2xl font-bold text-green-600 dark:text-green-400">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 cursor-pointer hover:bg-yellow-50 dark:hover:bg-yellow-900" onclick="filterByStatus('idle')">
            <p class="text-sm text-gray-600 dark:text-gray-400">Idle</p>
            <p id="idleVehicles" class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900" onclick="filterByStatus('standby')">
            <p class="text-sm text-gray-600 dark:text-gray-400">Standby</p>
            <p id="standbyVehicles" class="text-2xl font-bold text-blue-600 dark:text-blue-400">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 cursor-pointer hover:bg-red-50 dark:hover:bg-red-900" onclick="filterByStatus('no_communication')">
            <p class="text-sm text-gray-600 dark:text-gray-400">No Communication</p>
            <p id="noCommunicationVehicles" class="text-2xl font-bold text-red-600 dark:text-red-400">0</p>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vehicle Number</label>
                <input type="text" id="vehicleNoSearch" placeholder="Search..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
            </div>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                    <option value="">All Status</option>
                    <option value="running">Running</option>
                    <option value="idle">Idle</option>
                    <option value="standby">Standby</option>
                    <option value="no_communication">No Communication</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button id="searchBtn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    Search
                </button>
                <button id="resetBtn" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Fleet Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vehicle No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Zone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ward</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transfer Station</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Speed</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Update</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="fleetTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            Loading fleet data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Map Modal -->
<div id="mapModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-11/12 max-w-4xl">
        <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Vehicle Location</h3>
            <button onclick="closeMapModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="vehicleMap" style="height: 500px;"></div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let autoRefreshInterval = null;
let vehicleMap = null;

// Load fleet data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadFleetData();
    
    // Auto-refresh every 15 seconds
    autoRefreshInterval = setInterval(loadFleetData, 15000);
});

// Search button
document.getElementById('searchBtn').addEventListener('click', loadFleetData);

// Reset button
document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('vehicleNoSearch').value = '';
    document.getElementById('zoneFilter').value = '';
    document.getElementById('wardFilter').value = '';
    document.getElementById('statusFilter').value = '';
    loadFleetData();
});

// Filter by status (from statistics cards)
function filterByStatus(status) {
    document.getElementById('statusFilter').value = status;
    loadFleetData();
}

async function loadFleetData() {
    try {
        const response = await fetch('{{ route("admin.fleet-dashboard.data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                vehicle_no: document.getElementById('vehicleNoSearch').value,
                zone_id: document.getElementById('zoneFilter').value,
                ward_id: document.getElementById('wardFilter').value,
                status: document.getElementById('statusFilter').value
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayFleetData(data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function displayFleetData(data) {
    // Update statistics
    document.getElementById('totalVehicles').textContent = data.statistics.total;
    document.getElementById('runningVehicles').textContent = data.statistics.running;
    document.getElementById('idleVehicles').textContent = data.statistics.idle;
    document.getElementById('standbyVehicles').textContent = data.statistics.standby;
    document.getElementById('noCommunicationVehicles').textContent = data.statistics.no_communication;
    
    // Update table
    const tbody = document.getElementById('fleetTableBody');
    
    if (data.fleet.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No vehicles found</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.fleet.map(vehicle => {
        const statusBadge = getStatusBadge(vehicle.status, vehicle.status_color);
        const speedBadge = getSpeedBadge(vehicle.speed, vehicle.speed_indicator, vehicle.speed_color);
        const disconnectAlert = vehicle.is_disconnected ? '<span class="text-red-600 text-xs">⚠️ Disconnected</span>' : '';
        
        return `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">${vehicle.vehicle_no}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${vehicle.vehicle_type}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${vehicle.zone}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${vehicle.ward}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${vehicle.transfer_station}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${vehicle.current_location}</td>
                <td class="px-4 py-3 text-sm">${speedBadge}</td>
                <td class="px-4 py-3 text-sm">${statusBadge} ${disconnectAlert}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${vehicle.last_update}<br><span class="text-xs text-gray-500">${vehicle.last_update_full}</span></td>
                <td class="px-4 py-3 text-sm">
                    <button onclick="showVehicleOnMap(${vehicle.id}, '${vehicle.vehicle_no}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                        📍 Map
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function getStatusBadge(status, color) {
    const colors = {
        'green': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'yellow': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'blue': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'gray': 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
    };
    
    return `<span class="px-2 py-1 text-xs rounded-full ${colors[color]}">${status.toUpperCase().replace(/_/g, ' ')}</span>`;
}

function getSpeedBadge(speed, indicator, color) {
    const colors = {
        'green': 'text-green-600 dark:text-green-400',
        'orange': 'text-orange-600 dark:text-orange-400',
        'red': 'text-red-600 dark:text-red-400'
    };
    
    const icons = {
        'normal': '✓',
        'alarming': '⚠️',
        'above_alarming': '🚨'
    };
    
    return `<span class="${colors[color]} font-semibold">${icons[indicator]} ${speed} km/h</span>`;
}

async function showVehicleOnMap(vehicleId, vehicleNo) {
    try {
        const response = await fetch(`{{ url('/admin/fleet-dashboard/vehicle') }}/${vehicleId}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('modalTitle').textContent = `${vehicleNo} - Location`;
            document.getElementById('mapModal').classList.remove('hidden');
            document.getElementById('mapModal').classList.add('flex');
            
            // Initialize map if not already
            if (!vehicleMap) {
                vehicleMap = L.map('vehicleMap');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(vehicleMap);
            }
            
            // Clear existing markers
            vehicleMap.eachLayer(layer => {
                if (layer instanceof L.Marker) {
                    vehicleMap.removeLayer(layer);
                }
            });
            
            // Add marker
            const marker = L.marker([data.vehicle.latitude, data.vehicle.longitude]).addTo(vehicleMap);
            marker.bindPopup(`
                <strong>${data.vehicle.name}</strong><br>
                ${data.vehicle.vehicle_no}<br>
                Speed: ${data.vehicle.speed} km/h<br>
                ${data.vehicle.last_update}
            `).openPopup();
            
            vehicleMap.setView([data.vehicle.latitude, data.vehicle.longitude], 15);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load vehicle location');
    }
}

function closeMapModal() {
    document.getElementById('mapModal').classList.add('hidden');
    document.getElementById('mapModal').classList.remove('flex');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
});
</script>
@endsection
