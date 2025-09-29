<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Device Setup & Testing') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Connection Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">📡 GPS Device Connection</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Server Endpoints</h4>
                            <div class="space-y-2 text-sm">
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <strong>POST:</strong> <code class="text-blue-600">{{ url('/gps/data') }}</code>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <strong>GET:</strong> <code class="text-blue-600">{{ url('/gps/data') }}</code>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <strong>Device Specific:</strong> <code class="text-blue-600">{{ url('/gps/{device_id}') }}</code>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Server Details</h4>
                            <div class="space-y-2 text-sm">
                                <div><strong>Domain:</strong> {{ request()->getHost() }}</div>
                                <div><strong>Port:</strong> {{ request()->getPort() }}</div>
                                <div><strong>Protocol:</strong> HTTP/HTTPS</div>
                                <div><strong>Format:</strong> JSON, Form Data, Query Params, NMEA</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Formats -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">📋 Supported Data Formats</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- JSON Format -->
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">1. JSON Format (Recommended)</h4>
                            <pre class="bg-gray-50 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto"><code>{
  "device_id": "GPS001",
  "latitude": 23.0225,
  "longitude": 72.5714,
  "speed": 45.5,
  "heading": 180.0,
  "altitude": 100.5,
  "satellites": 8,
  "battery": 85,
  "timestamp": "2025-01-27T12:30:00Z",
  "ignition": true
}</code></pre>
                        </div>

                        <!-- Form Data -->
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">2. Form Data / Query Parameters</h4>
                            <pre class="bg-gray-50 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto"><code>device_id=GPS001
lat=23.0225
lng=72.5714
speed=45.5
heading=180.0
altitude=100.5
satellites=8
battery=85
timestamp=2025-01-27T12:30:00Z</code></pre>
                        </div>

                        <!-- URL Example -->
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">3. GET URL Example</h4>
                            <pre class="bg-gray-50 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto"><code>{{ url('/gps/data') }}?device_id=GPS001&lat=23.0225&lng=72.5714&speed=45.5</code></pre>
                        </div>

                        <!-- NMEA Format -->
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">4. NMEA Format</h4>
                            <pre class="bg-gray-50 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto"><code>$GPRMC,123519,A,4807.038,N,01131.000,E,022.4,084.4,230394,003.1,W*6A</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test GPS Data -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">🧪 Test GPS Data Transmission</h3>
                </div>
                <div class="p-6">
                    <form id="testGpsForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Device ID</label>
                                <input type="text" id="device_id" value="TEST_DEVICE_001" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Latitude</label>
                                <input type="number" id="latitude" value="23.0225" step="0.000001"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Longitude</label>
                                <input type="number" id="longitude" value="72.5714" step="0.000001"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Speed (km/h)</label>
                                <input type="number" id="speed" value="0" step="0.1"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Heading (degrees)</label>
                                <input type="number" id="heading" value="0" min="0" max="360"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Battery (%)</label>
                                <input type="number" id="battery" value="100" min="0" max="100"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap gap-4">
                            <button type="button" onclick="testDebugEndpoint()" 
                                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                🐛 Debug Test
                            </button>
                            <button type="button" onclick="testServerHealth()" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                🔍 Health Check
                            </button>
                            <button type="button" onclick="sendTestData()" 
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                📡 Send Test Data
                            </button>
                            <button type="button" onclick="simulateMovement()" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                🚗 Simulate Movement
                            </button>
                            <button type="button" onclick="getCurrentLocation()" 
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                📍 Use My Location
                            </button>
                        </div>
                    </form>
                    
                    <div id="testResults" class="mt-6 hidden">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Test Results:</h4>
                        <pre id="testOutput" class="bg-gray-50 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto"></pre>
                    </div>
                </div>
            </div>

            <!-- Device Configuration -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">⚙️ Common Device Configuration</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Popular GPS Tracker Commands</h4>
                            <div class="space-y-2 text-sm">
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <strong>TK103/TK102:</strong><br>
                                    <code>adminip#{{ request()->getHost() }}#{{ request()->getPort() }}#</code>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <strong>GT06/GT02:</strong><br>
                                    <code>SERVER,1,{{ request()->getHost() }},{{ request()->getPort() }},0#</code>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <strong>Concox (GT06N):</strong><br>
                                    <code>SSERVER,1,{{ request()->getHost() }},{{ request()->getPort() }}#</code>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Mobile App Testing</h4>
                            <div class="space-y-2 text-sm">
                                <p>You can also test using mobile apps that send GPS data:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>GPS Logger for Android:</strong> Configure HTTP POST</li>
                                    <li><strong>Traccar Client:</strong> Set server URL</li>
                                    <li><strong>OsmAnd:</strong> Online tracking plugin</li>
                                    <li><strong>Custom App:</strong> Use our API endpoints</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">📊 Recent GPS Data</h3>
                        <button onclick="refreshActivity()" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="recentActivity" class="space-y-2">
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No recent GPS data received. Send test data to see results here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let simulationInterval;
        
        async function sendTestData() {
            const data = {
                device_id: document.getElementById('device_id').value,
                latitude: parseFloat(document.getElementById('latitude').value),
                longitude: parseFloat(document.getElementById('longitude').value),
                speed: parseFloat(document.getElementById('speed').value),
                heading: parseFloat(document.getElementById('heading').value),
                battery: parseInt(document.getElementById('battery').value),
                timestamp: new Date().toISOString()
            };
            
            try {
                // Try test endpoint first (simpler, better error handling)
                const response = await fetch('{{ route("gps.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response. Check server logs.');
                }
                
                const result = await response.json();
                showTestResult(result, response.status);
                refreshActivity();
                
            } catch (error) {
                console.error('Test data error:', error);
                showTestResult({
                    error: error.message,
                    suggestion: 'Try the health check endpoint or check server logs'
                }, 500);
            }
        }
        
        // Test server health
        async function testServerHealth() {
            try {
                console.log('Testing health endpoint: {{ route("gps.health") }}');
                
                const response = await fetch('{{ route("gps.health") }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log('Health check response status:', response.status);
                console.log('Health check response headers:', response.headers);
                
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.log('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                }
                
                const result = await response.json();
                console.log('Health check result:', result);
                showTestResult(result, response.status);
                
            } catch (error) {
                console.error('Health check error:', error);
                showTestResult({
                    error: 'Health check failed: ' + error.message,
                    suggestion: 'Check browser console and server logs',
                    debug_url: '{{ route("debug.test") }}'
                }, 500);
            }
        }
        
        // Test debug endpoint
        async function testDebugEndpoint() {
            try {
                const response = await fetch('{{ route("debug.test") }}');
                const result = await response.json();
                showTestResult(result, response.status);
            } catch (error) {
                showTestResult({error: 'Debug test failed: ' + error.message}, 500);
            }
        }
        
        function simulateMovement() {
            if (simulationInterval) {
                clearInterval(simulationInterval);
                simulationInterval = null;
                document.querySelector('button[onclick="simulateMovement()"]').textContent = 'Simulate Movement';
                return;
            }
            
            document.querySelector('button[onclick="simulateMovement()"]').textContent = 'Stop Simulation';
            
            let lat = parseFloat(document.getElementById('latitude').value);
            let lng = parseFloat(document.getElementById('longitude').value);
            let heading = 0;
            
            simulationInterval = setInterval(() => {
                // Simulate movement in a circle
                heading += 10;
                if (heading >= 360) heading = 0;
                
                const radius = 0.001; // Small radius for demo
                lat += radius * Math.cos(heading * Math.PI / 180);
                lng += radius * Math.sin(heading * Math.PI / 180);
                
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
                document.getElementById('heading').value = heading;
                document.getElementById('speed').value = (Math.random() * 50 + 10).toFixed(1);
                
                sendTestData();
            }, 2000); // Send data every 2 seconds
        }
        
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                    document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
                    document.getElementById('speed').value = (position.coords.speed || 0).toFixed(1);
                    document.getElementById('heading').value = (position.coords.heading || 0).toFixed(1);
                }, function(error) {
                    alert('Error getting location: ' + error.message);
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }
        
        function showTestResult(result, status) {
            const resultsDiv = document.getElementById('testResults');
            const outputPre = document.getElementById('testOutput');
            
            resultsDiv.classList.remove('hidden');
            outputPre.textContent = JSON.stringify(result, null, 2);
            
            if (status >= 200 && status < 300) {
                outputPre.className = 'bg-green-50 dark:bg-green-900 p-4 rounded text-sm overflow-x-auto';
            } else {
                outputPre.className = 'bg-red-50 dark:bg-red-900 p-4 rounded text-sm overflow-x-auto';
            }
        }
        
        async function refreshActivity() {
            // This would fetch recent GPS data from your API
            // For now, we'll just show a message
            const activityDiv = document.getElementById('recentActivity');
            activityDiv.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-4">Refreshing... Check your dashboard for updated device locations.</p>';
        }
    </script>
    @endpush
</x-app-layout>
