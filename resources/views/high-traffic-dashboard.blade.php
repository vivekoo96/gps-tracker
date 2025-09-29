<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('High-Traffic Dashboard') }}
            </h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Real-time via WebSockets
                </span>
                <div id="connectionStatus" class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    🟢 Connected
                </div>
                <button onclick="toggleConnection()" id="toggleBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    🔌 WebSocket Active
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Performance Metrics -->
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-blue-600" id="messagesReceived">0</div>
                        <div class="text-sm text-gray-500">Messages/sec</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600" id="devicesTracked">0</div>
                        <div class="text-sm text-gray-500">Devices Tracked</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-purple-600" id="latency">0ms</div>
                        <div class="text-sm text-gray-500">Avg Latency</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-yellow-600" id="uptime">00:00</div>
                        <div class="text-sm text-gray-500">Uptime</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="totalDevices">{{ $stats['total_devices'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Online Devices -->
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
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Online Devices</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="onlineDevices">{{ $stats['online_devices'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Moving Devices -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Moving Devices</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="movingDevices">{{ $stats['moving_devices'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Positions Today -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Positions Today</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="positionsToday">{{ $stats['positions_today'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- High-Performance Map -->
    <div class="mt-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Real-Time Device Tracking</h3>
                    <div class="flex space-x-2">
                        <span class="text-sm text-gray-500" id="mapStats">0 devices visible</span>
                        <button onclick="toggleFullScreen()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Full Screen
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div id="map" class="rounded-lg h-96 w-full"></div>
            </div>
        </div>
    </div>

    @push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    @endpush

    @push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <!-- Laravel Echo for WebSockets -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
    
    <script>
        let map;
        let deviceMarkers = new Map();
        let echo;
        let startTime = Date.now();
        let messageCount = 0;
        let latencySum = 0;
        let latencyCount = 0;
        
        // Performance tracking
        setInterval(() => {
            updatePerformanceMetrics();
        }, 1000);
        
        function updatePerformanceMetrics() {
            const uptime = Math.floor((Date.now() - startTime) / 1000);
            const minutes = Math.floor(uptime / 60);
            const seconds = uptime % 60;
            
            document.getElementById('messagesReceived').textContent = messageCount;
            document.getElementById('uptime').textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            document.getElementById('latency').textContent = latencyCount > 0 ? Math.round(latencySum / latencyCount) + 'ms' : '0ms';
            
            // Reset message count for next second
            messageCount = 0;
        }
        
        // Initialize map
        function initMap() {
            map = L.map('map').setView([23.0225, 72.5714], 10);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
        }
        
        // Initialize WebSocket connection
        function initWebSocket() {
            // Configure Laravel Echo for WebSockets
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: import.meta.env.VITE_REVERB_APP_KEY,
                wsHost: import.meta.env.VITE_REVERB_HOST,
                wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
                wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
                enabledTransports: ['ws', 'wss'],
            });
            
            // Listen for GPS data updates
            Echo.channel('gps-tracking')
                .listen('.gps.data.received', (e) => {
                    const receiveTime = Date.now();
                    const sendTime = new Date(e.timestamp).getTime();
                    const latency = receiveTime - sendTime;
                    
                    latencySum += latency;
                    latencyCount++;
                    messageCount++;
                    
                    updateDeviceOnMap(e);
                    console.log('Real-time GPS update:', e);
                });
            
            // Listen for dashboard stats updates
            Echo.channel('dashboard-stats')
                .listen('.dashboard.stats.updated', (e) => {
                    updateDashboardStats(e.stats);
                    console.log('Dashboard stats updated:', e);
                });
            
            updateConnectionStatus(true);
        }
        
        // Update device marker on map
        function updateDeviceOnMap(deviceData) {
            const deviceId = deviceData.device_id;
            
            // Remove existing marker if exists
            if (deviceMarkers.has(deviceId)) {
                map.removeLayer(deviceMarkers.get(deviceId));
            }
            
            // Create new marker
            const iconColor = deviceData.is_moving ? 'green' : 'blue';
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
                    <h4 class="font-semibold text-gray-900">${deviceData.device_name || 'Device #' + deviceData.device_id}</h4>
                    <p class="text-sm text-gray-600">Speed: ${deviceData.speed} km/h</p>
                    <p class="text-sm text-gray-600">Status: ${deviceData.is_moving ? 'Moving' : 'Stationary'}</p>
                    <p class="text-xs text-gray-500">Battery: ${deviceData.battery_level || 'N/A'}%</p>
                    <p class="text-xs text-gray-500">Updated: ${new Date(deviceData.timestamp).toLocaleTimeString()}</p>
                </div>
            `;
            
            const marker = L.marker([deviceData.latitude, deviceData.longitude], {icon: customIcon})
                .addTo(map)
                .bindPopup(popupContent);
            
            deviceMarkers.set(deviceId, marker);
            
            // Update map stats
            document.getElementById('mapStats').textContent = `${deviceMarkers.size} devices visible`;
            document.getElementById('devicesTracked').textContent = deviceMarkers.size;
        }
        
        // Update dashboard statistics
        function updateDashboardStats(stats) {
            document.getElementById('totalDevices').textContent = stats.total_devices;
            document.getElementById('onlineDevices').textContent = stats.online_devices;
            document.getElementById('movingDevices').textContent = stats.moving_devices;
            document.getElementById('positionsToday').textContent = stats.positions_today;
        }
        
        // Update connection status
        function updateConnectionStatus(connected) {
            const status = document.getElementById('connectionStatus');
            const btn = document.getElementById('toggleBtn');
            
            if (connected) {
                status.textContent = '🟢 Connected';
                status.className = 'px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
                btn.textContent = '🔌 WebSocket Active';
                btn.className = 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors';
            } else {
                status.textContent = '🔴 Disconnected';
                status.className = 'px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800';
                btn.textContent = '🔌 Reconnect';
                btn.className = 'bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors';
            }
        }
        
        // Toggle connection
        function toggleConnection() {
            if (Echo) {
                Echo.disconnect();
                Echo = null;
                updateConnectionStatus(false);
            } else {
                initWebSocket();
            }
        }
        
        // Full screen toggle
        function toggleFullScreen() {
            const mapContainer = document.getElementById('map').parentElement.parentElement;
            
            if (!document.fullscreenElement) {
                mapContainer.requestFullscreen().then(() => {
                    setTimeout(() => {
                        map.invalidateSize();
                        document.getElementById('map').style.height = '80vh';
                    }, 100);
                });
            } else {
                document.exitFullscreen().then(() => {
                    setTimeout(() => {
                        document.getElementById('map').style.height = '24rem';
                        map.invalidateSize();
                    }, 100);
                });
            }
        }
        
        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            initWebSocket();
        });
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (Echo) {
                Echo.disconnect();
            }
        });
    </script>
    @endpush
</x-app-layout>
