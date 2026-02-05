<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Live Tracking') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Live Updates</span>
                </div>
                <button onclick="location.reload()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Refresh
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin=""/>
    
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Map Section -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Live Map</h3>
                        <div class="flex items-center space-x-2">
                            <button class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 px-3 py-1 rounded text-sm">
                                Satellite
                            </button>
                            <button class="bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 px-3 py-1 rounded text-sm">
                                Street
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Real Interactive Map -->
                    <div id="map" class="rounded-lg h-96 w-full"></div>
                </div>
            </div>
        </div>

        <!-- Device List -->
        <div class="space-y-6">
            <!-- Active Devices -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Active Devices</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($devices as $device)
                            <div class="flex items-center justify-between p-4 rounded-lg {{ $device['status'] === 'online' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 {{ $device['status'] === 'online' ? 'bg-green-500' : 'bg-red-500' }} rounded-full"></div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $device['name'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $device['status'] === 'online' ? 'Online' : 'Offline' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $device['speed'] }} km/h</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $device['battery'] }}% battery</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Quick Stats</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Devices</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ count($devices) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Online</span>
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                {{ collect($devices)->where('status', 'online')->count() }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Offline</span>
                            <span class="text-sm font-medium text-red-600 dark:text-red-400">
                                {{ collect($devices)->where('status', 'offline')->count() }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Moving</span>
                            <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                {{ collect($devices)->where('speed', '>', 0)->count() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the map
            var map = L.map('map').setView([23.0225, 72.5714], 12); // Default center

            // Add CartoDB Voyager tiles
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

            // Store markers by device ID to update position
            var markers = {};
            var devices = @json($devices);

            // Initial Render
            updateMarkers(devices);
            fitBounds(devices);

            // Auto-refresh via AJAX
            setInterval(fetchLiveData, 5000); // 5 seconds

            function fetchLiveData() {
                fetch('{{ route("tracking.live-data") }}')
                    .then(response => response.json())
                    .then(data => {
                        updateMarkers(data.devices);
                        // Optional: update stats counters via DOM manipulation if needed
                    })
                    .catch(error => console.error('Error fetching GPS data:', error));
            }

            function updateMarkers(deviceList) {
                deviceList.forEach(function(device) {
                    if (device.lat && device.lng) {
                        var lat = parseFloat(device.lat);
                        var lng = parseFloat(device.lng);
                        
                        // Decide Icon Color
                        var iconColor = '#ef4444'; // Red (Offline)
                        if (device.status === 'online') {
                            iconColor = device.speed > 0 ? '#10b981' : '#3b82f6'; // Green (Moving) or Blue (Stopped)
                        }

                        // Create/Update Marker
                        if (markers[device.id]) {
                            // Animate/Move existing marker
                            var marker = markers[device.id];
                            marker.setLatLng([lat, lng]);
                            
                            // Update Icon (if color changed)
                            var newIcon = createCarIcon(iconColor, device.heading || 0);
                            marker.setIcon(newIcon);
                            
                            // Update Popup
                            marker.setPopupContent(createPopupContent(device));
                        } else {
                            // Create New Marker
                            var newIcon = createCarIcon(iconColor, device.heading || 0);
                            var marker = L.marker([lat, lng], { icon: newIcon }).addTo(map);
                            marker.bindPopup(createPopupContent(device));
                            markers[device.id] = marker;
                        }
                    }
                });
            }

            function fitBounds(deviceList) {
                var bounds = [];
                deviceList.forEach(function(d) {
                    if (d.lat && d.lng) bounds.push([d.lat, d.lng]);
                });
                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                }
            }

            function createCarIcon(color, rotation) {
                var iconHtml = `
                    <div style="
                        transform: rotate(${rotation}deg);
                        transform-origin: center;
                        filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.3));
                    ">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="18" fill="white" fill-opacity="0.9"/>
                            <path d="M20 6L32 30L20 24L8 30L20 6Z" fill="${color}"/>
                        </svg>
                    </div>
                `;
                return L.divIcon({
                    className: 'custom-car-icon',
                    html: iconHtml,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20],
                    popupAnchor: [0, -20]
                });
            }

            function createPopupContent(device) {
                return `
                    <div class="p-2">
                        <h4 class="font-semibold text-gray-900">${device.name}</h4>
                        <div class="text-sm text-gray-600 mt-1">
                            <p><strong>Status:</strong> <span class="capitalize ${device.status === 'online' ? 'text-green-600' : 'text-red-600'}">${device.status}</span></p>
                            <p><strong>Speed:</strong> ${device.speed} km/h</p>
                            <p><strong>Battery:</strong> ${device.battery}%</p>
                            ${device.location ? `<p><strong>Location:</strong> ${device.location}</p>` : ''}
                            <p><strong>Last Update:</strong> ${new Date(device.last_update).toLocaleString()}</p>
                        </div>
                    </div>
                `;
            }

            // Map Tiles Toggle logic (kept same as before)
            document.querySelectorAll('button').forEach(function(button) {
                 // ... existing toggle logic can remain or be simplified ...
                 if (button.textContent.trim() === 'Satellite') {
                    button.addEventListener('click', () => {
                        map.eachLayer(l => l._url && l._url.includes('cartocdn') && map.removeLayer(l));
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: 'Tiles © Esri' }).addTo(map);
                    });
                 } else if (button.textContent.trim() === 'Street') {
                    button.addEventListener('click', () => {
                        map.eachLayer(l => l._url && l._url.includes('arcgisonline') && map.removeLayer(l));
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { subdomains: 'abcd' }).addTo(map);
                    });
                 }
            });
        });
    </script>
</x-app-layout>
