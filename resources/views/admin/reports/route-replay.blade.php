@extends('layouts.admin')

@section('title', 'Route Replay & Statistics')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Route Replay & Statistics</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Visualize vehicle movement with detailed statistics and analysis</p>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Filters</h2>
        
        <form id="routeReplayForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Vehicle Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vehicle</label>
                <select name="device_id" id="device_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                    <option value="">Select Vehicle</option>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->vehicle_no }})</option>
                    @endforeach
                </select>
            </div>

            <!-- From Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                <input type="date" name="from_date" id="from_date" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
            </div>

            <!-- To Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                <input type="date" name="to_date" id="to_date" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
            </div>

            <!-- From Time -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Time (Optional)</label>
                <input type="time" name="from_time" id="from_time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
            </div>

            <!-- To Time -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Time (Optional)</label>
                <input type="time" name="to_time" id="to_time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
            </div>

            <!-- Speed Limit -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Speed Limit (km/h)</label>
                <input type="number" name="speed_limit" id="speed_limit" value="60" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
            </div>

            <!-- Stoppage Threshold -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stoppage Min (seconds)</label>
                <input type="number" name="stoppage_threshold" id="stoppage_threshold" value="300" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
            </div>

            <!-- Load Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    Load Route
                </button>
            </div>
        </form>
    </div>

    <!-- Map and Controls -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Map Container -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div id="map" style="height: 600px;"></div>
                
                <!-- Playback Controls -->
                <div id="playbackControls" class="p-4 border-t border-gray-200 dark:border-gray-700 hidden">
                    <div class="flex items-center gap-4 mb-3">
                        <button id="playBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            ▶ Play
                        </button>
                        <button id="pauseBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors hidden">
                            ⏸ Pause
                        </button>
                        <button id="resetBtn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                            ↻ Reset
                        </button>
                        <select id="playbackSpeed" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100">
                            <option value="0.5">0.5x</option>
                            <option value="1" selected>1x</option>
                            <option value="2">2x</option>
                            <option value="5">5x</option>
                            <option value="10">10x</option>
                        </select>
                        <span id="currentTime" class="text-sm text-gray-600 dark:text-gray-400 ml-auto"></span>
                    </div>
                    <input type="range" id="timelineSlider" min="0" max="100" value="0" class="w-full">
                </div>
            </div>
        </div>

        <!-- Statistics Panel -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Statistics</h2>
                
                <div id="statsContainer" class="hidden">
                    <!-- Vehicle Info -->
                    <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Vehicle</p>
                        <p id="vehicleName" class="font-semibold text-gray-900 dark:text-gray-100"></p>
                        <p id="vehicleType" class="text-sm text-gray-500 dark:text-gray-400"></p>
                    </div>

                    <!-- Trip Stats -->
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Distance</p>
                            <p id="totalDistance" class="text-xl font-bold text-blue-600 dark:text-blue-400"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Trip Duration</p>
                            <p id="tripDuration" class="font-semibold text-gray-900 dark:text-gray-100"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Moving Time</p>
                                <p id="movingTime" class="font-semibold text-green-600 dark:text-green-400"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Idle Time</p>
                                <p id="idleTime" class="font-semibold text-orange-600 dark:text-orange-400"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Max Speed</p>
                                <p id="maxSpeed" class="font-semibold text-red-600 dark:text-red-400"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Avg Speed</p>
                                <p id="avgSpeed" class="font-semibold text-gray-900 dark:text-gray-100"></p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Speed Violations</p>
                            <p id="violationsCount" class="font-semibold text-red-600 dark:text-red-400"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Stoppages</p>
                            <p id="stoppagesCount" class="font-semibold text-gray-900 dark:text-gray-100"></p>
                        </div>
                    </div>

                    <!-- Time Range -->
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Start: <span id="startTime"></span></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">End: <span id="endTime"></span></p>
                    </div>
                </div>

                <div id="noDataMessage" class="text-center text-gray-500 dark:text-gray-400 py-8">
                    <p>Select filters and load route to view statistics</p>
                </div>
            </div>

            <!-- Violations List -->
            <div id="violationsPanel" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mt-6 hidden">
                <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-gray-100">Speed Violations</h3>
                <div id="violationsList" class="space-y-2 max-h-64 overflow-y-auto"></div>
            </div>

            <!-- Stoppages List -->
            <div id="stoppagesPanel" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mt-6 hidden">
                <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-gray-100">Stoppages</h3>
                <div id="stoppagesList" class="space-y-2 max-h-64 overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map, routePolyline, currentMarker, startMarker, endMarker;
let routeData = null;
let playbackInterval = null;
let currentIndex = 0;
let violationMarkers = [];
let stoppageMarkers = [];

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    map = L.map('map').setView([23.5880, 87.2680], 6); // India center
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Set default dates
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    document.getElementById('from_date').valueAsDate = yesterday;
    document.getElementById('to_date').valueAsDate = today;
});

// Form submission
document.getElementById('routeReplayForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('{{ route("admin.reports.route-replay.data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            routeData = result;
            displayRoute(result);
            displayStatistics(result);
            displayViolations(result.violations);
            displayStoppages(result.stoppages);
        } else {
            alert(result.message || 'Failed to load route data');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load route data');
    }
});

function displayRoute(data) {
    // Clear existing layers
    if (routePolyline) map.removeLayer(routePolyline);
    if (startMarker) map.removeLayer(startMarker);
    if (endMarker) map.removeLayer(endMarker);
    if (currentMarker) map.removeLayer(currentMarker);
    violationMarkers.forEach(m => map.removeLayer(m));
    stoppageMarkers.forEach(m => map.removeLayer(m));
    
    const positions = data.positions.map(p => [p.lat, p.lng]);
    
    // Draw route polyline
    routePolyline = L.polyline(positions, {
        color: '#3B82F6',
        weight: 4,
        opacity: 0.7
    }).addTo(map);
    
    // Start marker
    startMarker = L.marker(positions[0], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: green; color: white; padding: 5px; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold;">S</div>'
        })
    }).addTo(map).bindPopup('Start');
    
    // End marker
    endMarker = L.marker(positions[positions.length - 1], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: red; color: white; padding: 5px; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold;">E</div>'
        })
    }).addTo(map).bindPopup('End');
    
    // Add violation markers
    data.violations.forEach(v => {
        const marker = L.circleMarker([v.location.lat, v.location.lng], {
            radius: 6,
            fillColor: '#EF4444',
            color: '#DC2626',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map).bindPopup(`Speed: ${v.speed} km/h (Limit: ${v.limit})`);
        violationMarkers.push(marker);
    });
    
    // Add stoppage markers
    data.stoppages.forEach(s => {
        const marker = L.circleMarker([s.location.lat, s.location.lng], {
            radius: 5,
            fillColor: '#F59E0B',
            color: '#D97706',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map).bindPopup(`Stopped: ${s.duration}`);
        stoppageMarkers.push(marker);
    });
    
    map.fitBounds(routePolyline.getBounds());
    
    // Show playback controls
    document.getElementById('playbackControls').classList.remove('hidden');
    document.getElementById('timelineSlider').max = positions.length - 1;
}

function displayStatistics(data) {
    const stats = data.statistics;
    
    document.getElementById('vehicleName').textContent = `${data.device.name} (${data.device.vehicle_no})`;
    document.getElementById('vehicleType').textContent = stats.vehicle_type;
    document.getElementById('totalDistance').textContent = `${stats.total_distance} km`;
    document.getElementById('tripDuration').textContent = stats.trip_duration;
    document.getElementById('movingTime').textContent = stats.moving_time;
    document.getElementById('idleTime').textContent = stats.idle_time;
    document.getElementById('maxSpeed').textContent = `${stats.max_speed} km/h`;
    document.getElementById('avgSpeed').textContent = `${stats.avg_speed} km/h`;
    document.getElementById('violationsCount').textContent = data.violations.length;
    document.getElementById('stoppagesCount').textContent = data.stoppages.length;
    document.getElementById('startTime').textContent = stats.start_time;
    document.getElementById('endTime').textContent = stats.end_time;
    
    document.getElementById('statsContainer').classList.remove('hidden');
    document.getElementById('noDataMessage').classList.add('hidden');
}

function displayViolations(violations) {
    const panel = document.getElementById('violationsPanel');
    const list = document.getElementById('violationsList');
    
    if (violations.length > 0) {
        list.innerHTML = violations.map(v => `
            <div class="text-sm p-2 bg-red-50 dark:bg-red-900/20 rounded border-l-4 border-red-500">
                <p class="font-semibold">${v.speed} km/h (${v.excess} over limit)</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">${v.time}</p>
            </div>
        `).join('');
        panel.classList.remove('hidden');
    } else {
        panel.classList.add('hidden');
    }
}

function displayStoppages(stoppages) {
    const panel = document.getElementById('stoppagesPanel');
    const list = document.getElementById('stoppagesList');
    
    if (stoppages.length > 0) {
        list.innerHTML = stoppages.map(s => `
            <div class="text-sm p-2 bg-orange-50 dark:bg-orange-900/20 rounded border-l-4 border-orange-500">
                <p class="font-semibold">${s.duration}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">${s.start_time}</p>
            </div>
        `).join('');
        panel.classList.remove('hidden');
    } else {
        panel.classList.add('hidden');
    }
}

// Playback controls
document.getElementById('playBtn').addEventListener('click', function() {
    if (!routeData) return;
    
    this.classList.add('hidden');
    document.getElementById('pauseBtn').classList.remove('hidden');
    
    const speed = parseFloat(document.getElementById('playbackSpeed').value);
    const interval = 1000 / speed;
    
    playbackInterval = setInterval(() => {
        if (currentIndex >= routeData.positions.length - 1) {
            document.getElementById('pauseBtn').click();
            return;
        }
        
        currentIndex++;
        updatePlayback();
    }, interval);
});

document.getElementById('pauseBtn').addEventListener('click', function() {
    this.classList.add('hidden');
    document.getElementById('playBtn').classList.remove('hidden');
    clearInterval(playbackInterval);
});

document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('pauseBtn').click();
    currentIndex = 0;
    updatePlayback();
});

document.getElementById('timelineSlider').addEventListener('input', function() {
    document.getElementById('pauseBtn').click();
    currentIndex = parseInt(this.value);
    updatePlayback();
});

function updatePlayback() {
    if (!routeData) return;
    
    const pos = routeData.positions[currentIndex];
    
    if (currentMarker) map.removeLayer(currentMarker);
    
    currentMarker = L.marker([pos.lat, pos.lng], {
        icon: L.divIcon({
            className: 'current-position',
            html: '<div style="background: blue; color: white; padding: 5px; border-radius: 50%; width: 20px; height: 20px; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"></div>'
        })
    }).addTo(map);
    
    map.panTo([pos.lat, pos.lng]);
    
    document.getElementById('timelineSlider').value = currentIndex;
    document.getElementById('currentTime').textContent = pos.time;
}
</script>
@endsection
