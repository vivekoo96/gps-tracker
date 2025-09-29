<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Last updated: {{ now()->format('M d, Y H:i') }}
                </span>
                <button onclick="toggleRealTime()" id="realTimeToggle" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors mr-2">
                    🟢 Live Mode
                </button>
                <button onclick="location.reload()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Refresh Data
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Devices -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Devices</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="total_devices">{{ $stats['total_devices'] }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        @if($stats['device_growth_percentage'] >= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">+{{ $stats['device_growth_percentage'] }}%</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ $stats['device_growth_percentage'] }}%</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-2">from last month</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Devices -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Devices</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="online_devices">{{ $stats['online_devices'] }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        @if($stats['active_device_growth_percentage'] >= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">+{{ $stats['active_device_growth_percentage'] }}%</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ $stats['active_device_growth_percentage'] }}%</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-2">from yesterday</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="total_users">{{ $stats['total_users'] }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        @if($stats['user_growth_percentage'] >= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">+{{ $stats['user_growth_percentage'] }}%</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ $stats['user_growth_percentage'] }}%</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-2">from last week</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Alerts</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="total_alerts">{{ $stats['total_alerts'] }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        <span class="text-red-600 dark:text-red-400 font-medium">{{ $stats['critical_alerts'] }} Critical</span>
                        <span class="text-gray-500 dark:text-gray-400 ml-2">{{ $stats['warning_alerts'] }} Warning</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Activity -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Activity</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($stats['recent_activity'] as $activity)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 
                                        @if($activity['status'] === 'success') bg-green-400
                                        @elseif($activity['status'] === 'warning') bg-yellow-400
                                        @elseif($activity['status'] === 'error') bg-red-400
                                        @else bg-blue-400
                                        @endif
                                        rounded-full mt-2"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $activity['message'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $activity['time'] ? $activity['time']->diffForHumans() : 'Unknown time' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">No recent activity</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('tracking.history') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                            View all activity →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Map -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.devices.create') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Add New Device</span>
                    </a>
                    
                    <a href="{{ route('tracking.reports') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">View Reports</span>
                    </a>
                    
                    <a href="{{ route('tracking.live') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Live Tracking</span>
                    </a>
                </div>
            </div>

            <!-- Device Status -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Device Status</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Online</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" data-status="online_devices">{{ $stats['online_devices'] }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Offline</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" data-status="offline_devices">{{ $stats['offline_devices'] }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Moving</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-blue-400 rounded-full mr-2"></div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" data-status="moving_devices">{{ $stats['moving_devices'] }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Low Battery</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" data-status="low_battery_devices">{{ $stats['low_battery_devices'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="mt-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Live Device Locations</h3>
                    <button onclick="toggleFullScreen()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Full Screen
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div id="map" class="rounded-lg h-96 w-full"></div>
                <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                    <span>Showing {{ $stats['online_devices'] }} active devices</span>
                    <button onclick="refreshMap()" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                        Refresh Map
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    @endpush

    @push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <script>
        let map;
        let deviceMarkers = [];
        
        // Initialize map
        function initMap() {
            // Default center (you can change this to your preferred location)
            map = L.map('map').setView([23.0225, 72.5714], 10); // Ahmedabad, India
            
            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Add device markers
            addDeviceMarkers();
        }
        
        // Add device markers to map
        function addDeviceMarkers() {
            const devices = @json($stats['devices_with_location']);
            
            devices.forEach(device => {
                if (device.latitude && device.longitude) {
                    // Create custom icon based on device status
                    const iconColor = device.is_moving ? 'green' : 'blue';
                    const iconHtml = `
                        <div style="
                            background-color: ${iconColor};
                            width: 20px;
                            height: 20px;
                            border-radius: 50%;
                            border: 3px solid white;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                        "></div>
                    `;
                    
                    const customIcon = L.divIcon({
                        html: iconHtml,
                        iconSize: [20, 20],
                        iconAnchor: [10, 10],
                        popupAnchor: [0, -10],
                        className: 'custom-div-icon'
                    });
                    
                    // Create popup content
                    const popupContent = `
                        <div class="p-2">
                            <h4 class="font-semibold text-gray-900">${device.name || 'Device #' + device.id}</h4>
                            <p class="text-sm text-gray-600">ID: ${device.unique_id || 'N/A'}</p>
                            <p class="text-sm text-gray-600">Speed: ${device.speed || 0} km/h</p>
                            <p class="text-sm text-gray-600">Status: ${device.is_moving ? 'Moving' : 'Stationary'}</p>
                            ${device.last_location_update ? 
                                `<p class="text-xs text-gray-500">Last update: ${new Date(device.last_location_update).toLocaleString()}</p>` : 
                                ''
                            }
                        </div>
                    `;
                    
                    // Add marker to map
                    const marker = L.marker([device.latitude, device.longitude], {icon: customIcon})
                        .addTo(map)
                        .bindPopup(popupContent);
                    
                    deviceMarkers.push(marker);
                }
            });
            
            // Fit map to show all markers if any exist
            if (deviceMarkers.length > 0) {
                const group = new L.featureGroup(deviceMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
        
        // Refresh map data
        function refreshMap() {
            // Clear existing markers
            deviceMarkers.forEach(marker => {
                map.removeLayer(marker);
            });
            deviceMarkers = [];
            
            // Reload the page to get fresh data
            location.reload();
        }
        
        // Toggle fullscreen
        function toggleFullScreen() {
            const mapContainer = document.getElementById('map').parentElement.parentElement;
            
            if (!document.fullscreenElement) {
                mapContainer.requestFullscreen().then(() => {
                    // Resize map after entering fullscreen
                    setTimeout(() => {
                        map.invalidateSize();
                        document.getElementById('map').style.height = '80vh';
                    }, 100);
                });
            } else {
                document.exitFullscreen().then(() => {
                    // Reset map size after exiting fullscreen
                    setTimeout(() => {
                        document.getElementById('map').style.height = '24rem';
                        map.invalidateSize();
                    }, 100);
                });
            }
        }
        
        // Real-time Server-Sent Events
        let gpsEventSource;
        let dashboardEventSource;
        
        // Start real-time updates using SSE
        function startRealTimeUpdates() {
            // GPS data stream
            gpsEventSource = new EventSource('{{ route("stream.gps") }}');
            
            gpsEventSource.onmessage = function(event) {
                const data = JSON.parse(event.data);
                
                if (data.type === 'device_update') {
                    updateMapWithRealTimeData(data.devices);
                    console.log('Real-time GPS update:', data.timestamp);
                } else if (data.type === 'heartbeat') {
                    console.log('GPS stream heartbeat:', data.timestamp);
                }
            };
            
            gpsEventSource.onerror = function(event) {
                console.error('GPS stream error:', event);
                showRealTimeStatus(false);
            };
            
            // Dashboard stats stream
            dashboardEventSource = new EventSource('{{ route("stream.dashboard") }}');
            
            dashboardEventSource.onmessage = function(event) {
                const data = JSON.parse(event.data);
                
                if (data.type === 'stats_update') {
                    updateDashboardStats(data.data);
                    console.log('Real-time stats update:', data.timestamp);
                }
            };
            
            dashboardEventSource.onerror = function(event) {
                console.error('Dashboard stream error:', event);
            };
            
            // Show real-time indicator
            showRealTimeStatus(true);
        }
        
        // Stop real-time updates
        function stopRealTimeUpdates() {
            if (gpsEventSource) {
                gpsEventSource.close();
                gpsEventSource = null;
            }
            
            if (dashboardEventSource) {
                dashboardEventSource.close();
                dashboardEventSource = null;
            }
            
            showRealTimeStatus(false);
        }
        
        // Update dashboard statistics
        async function updateDashboardData() {
            try {
                const response = await fetch('{{ route("dashboard") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    // Update stats cards with new data
                    const data = await response.text();
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(data, 'text/html');
                    
                    // Update stat numbers
                    updateStatCard('total_devices', newDoc);
                    updateStatCard('online_devices', newDoc);
                    updateStatCard('total_users', newDoc);
                    updateStatCard('total_alerts', newDoc);
                    
                    // Update device status counts
                    updateDeviceStatus(newDoc);
                    
                    console.log('Dashboard data updated at', new Date().toLocaleTimeString());
                }
            } catch (error) {
                console.error('Error updating dashboard data:', error);
            }
        }
        
        // Update map markers with latest positions
        async function updateMapMarkers() {
            try {
                const response = await fetch('/api/devices/locations', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const devices = await response.json();
                    
                    // Clear existing markers
                    deviceMarkers.forEach(marker => {
                        map.removeLayer(marker);
                    });
                    deviceMarkers = [];
                    
                    // Add updated markers
                    devices.forEach(device => {
                        if (device.latitude && device.longitude) {
                            addDeviceMarker(device);
                        }
                    });
                    
                    console.log('Map markers updated at', new Date().toLocaleTimeString());
                }
            } catch (error) {
                console.error('Error updating map markers:', error);
            }
        }
        
        // Add single device marker
        function addDeviceMarker(device) {
            const iconColor = device.is_moving ? 'green' : 'blue';
            const iconHtml = `
                <div style="
                    background-color: ${iconColor};
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    border: 3px solid white;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                "></div>
            `;
            
            const customIcon = L.divIcon({
                html: iconHtml,
                iconSize: [20, 20],
                iconAnchor: [10, 10],
                popupAnchor: [0, -10],
                className: 'custom-div-icon'
            });
            
            const popupContent = `
                <div class="p-2">
                    <h4 class="font-semibold text-gray-900">${device.name || 'Device #' + device.id}</h4>
                    <p class="text-sm text-gray-600">ID: ${device.unique_id || 'N/A'}</p>
                    <p class="text-sm text-gray-600">Speed: ${device.speed || 0} km/h</p>
                    <p class="text-sm text-gray-600">Status: ${device.is_moving ? 'Moving' : 'Stationary'}</p>
                    <p class="text-xs text-gray-500">Last update: ${new Date(device.last_location_update).toLocaleString()}</p>
                </div>
            `;
            
            const marker = L.marker([device.latitude, device.longitude], {icon: customIcon})
                .addTo(map)
                .bindPopup(popupContent);
            
            deviceMarkers.push(marker);
        }
        
        // Update individual stat card
        function updateStatCard(statName, newDoc) {
            const currentElement = document.querySelector(`[data-stat="${statName}"]`);
            const newElement = newDoc.querySelector(`[data-stat="${statName}"]`);
            
            if (currentElement && newElement && currentElement.textContent !== newElement.textContent) {
                currentElement.textContent = newElement.textContent;
                // Add flash effect
                currentElement.classList.add('bg-green-100', 'dark:bg-green-900');
                setTimeout(() => {
                    currentElement.classList.remove('bg-green-100', 'dark:bg-green-900');
                }, 1000);
            }
        }
        
        // Update device status section
        function updateDeviceStatus(newDoc) {
            const statusElements = ['online_devices', 'offline_devices', 'moving_devices', 'low_battery_devices'];
            statusElements.forEach(status => {
                const current = document.querySelector(`[data-status="${status}"]`);
                const updated = newDoc.querySelector(`[data-status="${status}"]`);
                if (current && updated) {
                    current.textContent = updated.textContent;
                }
            });
        }
        
        // Show real-time status indicator
        function showRealTimeStatus(isActive) {
            let indicator = document.getElementById('realtime-indicator');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.id = 'realtime-indicator';
                indicator.className = 'fixed top-20 right-4 px-3 py-1 rounded-full text-xs font-medium z-50';
                document.body.appendChild(indicator);
            }
            
            if (isActive) {
                indicator.textContent = '🟢 Live Updates Active';
                indicator.className = 'fixed top-20 right-4 px-3 py-1 rounded-full text-xs font-medium z-50 bg-green-100 text-green-800 border border-green-200';
            } else {
                indicator.textContent = '🔴 Live Updates Paused';
                indicator.className = 'fixed top-20 right-4 px-3 py-1 rounded-full text-xs font-medium z-50 bg-red-100 text-red-800 border border-red-200';
            }
        }
        
        // Update map with real-time GPS data
        function updateMapWithRealTimeData(devices) {
            // Clear existing markers
            deviceMarkers.forEach(marker => {
                map.removeLayer(marker);
            });
            deviceMarkers = [];
            
            // Add updated markers
            devices.forEach(device => {
                if (device.latitude && device.longitude) {
                    addDeviceMarker(device);
                }
            });
        }
        
        // Update dashboard statistics with real-time data
        function updateDashboardStats(stats) {
            // Update stat cards with flash effect
            updateStatWithFlash('total_devices', stats.total_devices);
            updateStatWithFlash('online_devices', stats.online_devices);
            updateStatWithFlash('moving_devices', stats.moving_devices);
            
            // Update device status counts
            updateStatusCount('online_devices', stats.online_devices);
            updateStatusCount('offline_devices', stats.offline_devices);
            updateStatusCount('moving_devices', stats.moving_devices);
            updateStatusCount('low_battery_devices', stats.low_battery_devices);
        }
        
        // Update stat with flash effect
        function updateStatWithFlash(statName, newValue) {
            const element = document.querySelector(`[data-stat="${statName}"]`);
            if (element && element.textContent != newValue) {
                element.textContent = newValue;
                // Flash effect
                element.classList.add('bg-green-100', 'dark:bg-green-900');
                setTimeout(() => {
                    element.classList.remove('bg-green-100', 'dark:bg-green-900');
                }, 1000);
            }
        }
        
        // Update status count
        function updateStatusCount(statusName, newValue) {
            const element = document.querySelector(`[data-status="${statusName}"]`);
            if (element) {
                element.textContent = newValue;
            }
        }
        
        // Toggle real-time updates
        function toggleRealTime() {
            const toggleButton = document.getElementById('realTimeToggle');
            
            if (gpsEventSource || dashboardEventSource) {
                stopRealTimeUpdates();
                toggleButton.textContent = '🔴 Start Live Mode';
                toggleButton.className = 'bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors mr-2';
            } else {
                startRealTimeUpdates();
                toggleButton.textContent = '🟢 Live Mode Active';
                toggleButton.className = 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors mr-2';
            }
        }
        
        // Initialize map when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            startRealTimeUpdates(); // Auto-start real-time updates
            
            // Update button text
            const toggleButton = document.getElementById('realTimeToggle');
            toggleButton.textContent = '🟢 Live Mode Active';
        });
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopRealTimeUpdates();
        });
    </script>
    @endpush
</x-app-layout>
