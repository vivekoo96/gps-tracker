<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('GPS Tracking Dashboard') }}
        </h2>
    </x-slot>

<div class="min-h-screen bg-gray-50 dark:bg-gray-100 flex flex-col overflow-hidden">
    <!-- Top Action Bar -->
    <div class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 z-10">
        <div class="max-w-[98%] mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center">
                <div class="p-2 bg-indigo-600 rounded-lg mr-3 shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 uppercase tracking-tight">GHMC Live Monitoring</h1>
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <span class="flex items-center text-green-500"><span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"></span> {{ $onlineDevices }} Active</span>
                        <span class="text-gray-400">|</span>
                        <span class="text-gray-500 dark:text-gray-400 font-mono">{{ $totalDevices }} Total Units</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="refreshData()" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg shadow-indigo-500/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357-2H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content: Map Centric -->
    <div class="flex-1 relative flex overflow-hidden h-[calc(100vh-140px)]">
        <!-- Sidebar: Device List -->
        <div class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto hidden lg:block z-0">
            <div class="p-4 border-b border-gray-50 dark:border-gray-700/50 sticky top-0 bg-white dark:bg-gray-800 z-10">
                <input type="text" placeholder="Search vehicle..." class="w-full text-sm rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 px-3 py-2">
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
                @foreach($devices as $device)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition" data-device-id="{{ $device['id'] }}">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $device['vehicle_no'] }}</span>
                        <span class="status-dot w-3 h-3 rounded-full {{ $device['status'] === 'online' ? 'bg-green-500 animate-pulse border-2 border-white' : 'bg-red-500' }}"></span>
                    </div>
                    <div class="text-xs text-gray-500 uppercase font-medium">{{ $device['name'] }}</div>
                    <div class="mt-2 flex items-center justify-between text-[10px] text-gray-400 font-mono">
                        <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-900 rounded">{{ $device['speed'] }} KM/H</span>
                        <span>{{ \Carbon\Carbon::parse($device['last_update'])->diffForHumans() }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- The Map -->
        <div class="flex-1 relative">
            <div id="map" class="absolute inset-0 z-0"></div>
            
            <!-- Floating Map Overlays -->
            <div class="absolute top-4 right-4 flex flex-col gap-2 z-[1000]">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-md p-3 rounded-xl border border-white/20 shadow-2xl min-w-[150px]">
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Map Layers</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" id="layer-vehicles" checked class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="text-gray-700 dark:text-gray-300">Vehicles</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" id="layer-landmarks" checked class="rounded text-violet-600 focus:ring-indigo-500">
                            <span class="text-gray-700 dark:text-gray-300">Landmarks</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" id="layer-routes" checked class="rounded text-blue-500 focus:ring-indigo-500">
                            <span class="text-gray-700 dark:text-gray-300">Routes</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Map Styles */
    #map {
        filter: saturate(1.1) brightness(1.05);
    }
    .custom-car-icon {
        transition: all 0.5s ease;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }
    .leaflet-popup-content {
        margin: 0;
    }
    .leaflet-container {
        font-family: inherit;
    }
    /* Hide scrollbars but keep functionality */
    .overflow-y-auto::-webkit-scrollbar {
        width: 4px;
    }
    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .dark .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #334155;
    }
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map - Default to Hyderabad (GHMC)
let map = L.map('map').setView([17.3850, 78.4867], 12); 

// Add CartoDB Voyager tiles (Premium & Reliable)
L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
    subdomains: 'abcd',
    maxZoom: 19
}).addTo(map);

    // --- DATA ---
    const devicesData = @json($devices);
    const landmarksData = @json($landmarks);
    const routesData = @json($routes);

    // Layer Groups
    const vehicleLayer = L.layerGroup().addTo(map);
    const landmarkLayer = L.layerGroup().addTo(map);
    const routeLayer = L.layerGroup().addTo(map);
    
    // Feature group for auto-fitting (unaffected by toggles)
    const fitGroup = L.featureGroup();

    const markers = {};

    // --- LANDMARKS ---
    console.log("Landmarks Data:", landmarksData);
    landmarksData.forEach(function(l) {
        const lat = parseFloat(l.latitude);
        const lng = parseFloat(l.longitude);
        if(!isNaN(lat) && !isNaN(lng)) {
            let color = '#8b5cf6'; let iconChar = '📍';
            if (l.type === 'Dump Yard') { color = '#ef4444'; iconChar = '🗑️'; }
            else if (l.type === 'Transfer Station') { color = '#f97316'; iconChar = '♻️'; }
            else if (l.type === 'Garage') { color = '#3b82f6'; iconChar = '🔧'; }
            
            const landmarkIcon = L.divIcon({
                html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3); font-size: 14px;">${iconChar}</div>`,
                className: 'custom-landmark-icon', iconSize: [30, 30], iconAnchor: [15, 15]
            });
            const m = L.marker([lat, lng], { icon: landmarkIcon }).bindPopup(`<strong>${l.name}</strong><br>${l.type}`).addTo(landmarkLayer);
            m.addTo(fitGroup);
        }
    });

    // --- ROUTES ---
    console.log("Routes Data:", routesData);
    routesData.forEach(function(r) {
        let stops = r.stops;
        if (typeof stops === 'string') { try { stops = JSON.parse(stops); } catch(e) { console.error("Route parse error", e); return; } }
        
        if(stops && Array.isArray(stops) && stops.length > 0) {
            const latlngs = stops.map(stop => {
                const sLat = parseFloat(stop.lat || stop.latitude);
                const sLng = parseFloat(stop.lng || stop.longitude);
                return [sLat, sLng];
            }).filter(pos => !isNaN(pos[0]) && !isNaN(pos[1]));

            if (latlngs.length > 1) {
                const p = L.polyline(latlngs, { color: '#3b82f6', weight: 4, opacity: 0.7, dashArray: '10, 10' }).bindPopup(`<strong>Route: ${r.name}</strong><br>${r.description || ''}`).addTo(routeLayer);
                p.addTo(fitGroup);
            }
        }
    });

    // --- INITIAL DEVICES ---
    updateMarkers(devicesData);

    // Initial Auto-fit
    setTimeout(() => {
        if (fitGroup.getLayers().length > 0) { 
            map.fitBounds(fitGroup.getBounds().pad(0.1)); 
        } else if (Object.keys(markers).length > 0) {
            const deviceGroup = L.featureGroup(Object.values(markers));
            map.fitBounds(deviceGroup.getBounds().pad(0.1));
        }
    }, 500);

    // --- LAYER TOGGLES ---
    document.getElementById('layer-vehicles').addEventListener('change', e => e.target.checked ? map.addLayer(vehicleLayer) : map.removeLayer(vehicleLayer));
    document.getElementById('layer-landmarks').addEventListener('change', e => e.target.checked ? map.addLayer(landmarkLayer) : map.removeLayer(landmarkLayer));
    document.getElementById('layer-routes').addEventListener('change', e => e.target.checked ? map.addLayer(routeLayer) : map.removeLayer(routeLayer));

    // --- AJAX REFRESH ---
    setInterval(fetchLiveData, 5000);

    function fetchLiveData() {
        fetch('{{ route("admin.gps.live-data") }}')
            .then(response => response.json())
            .then(data => {
                updateMarkers(data.devices);
            })
            .catch(err => console.error("GPS Update Error", err));
    }

    function updateMarkers(deviceList) {
        deviceList.forEach(function(device) {
            if (device.lat && device.lng) {
                const lat = parseFloat(device.lat);
                const lng = parseFloat(device.lng);
                
                let iconColor = '#ef4444'; // Offline
                if (device.status === 'active' || device.is_online) {
                    iconColor = device.speed > 0 ? '#10b981' : '#3b82f6';
                }

                const rotation = device.heading || 0;
                const iconHtml = `
                    <div style="transform: rotate(${rotation}deg); transform-origin: center; filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.3));">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="18" fill="white" fill-opacity="0.9"/>
                            <path d="M20 6L32 30L20 24L8 30L20 6Z" fill="${iconColor}"/>
                        </svg>
                    </div>
                `;
                
                const customIcon = L.divIcon({ html: iconHtml, iconSize: [40, 40], iconAnchor: [20, 20], popupAnchor: [0, -20], className: 'custom-car-icon' });

                if (markers[device.id]) {
                    markers[device.id].setLatLng([lat, lng]);
                    markers[device.id].setIcon(customIcon);
                    markers[device.id].setPopupContent(createPopup(device));
                } else {
                    const marker = L.marker([lat, lng], {icon: customIcon}).bindPopup(createPopup(device)).addTo(vehicleLayer);
                    markers[device.id] = marker;
                }
                
                // Update Sidebar
                updateSidebarStatus(device);
            }
        });
    }

    function createPopup(device) {
        return `
            <div class="px-2 py-1 min-w-[200px]">
                <h4 class="font-bold text-gray-900 border-b pb-1 mb-2">${device.name}</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status:</span>
                        <span class="${device.status === 'online' ? 'text-green-600' : 'text-red-600'} font-bold capitalize">${device.status}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Speed:</span>
                        <span class="font-mono">${device.speed} km/h</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ignition:</span>
                        <span class="font-bold ${device.ignition ? 'text-green-600' : 'text-gray-500'}">${device.ignition ? 'ON' : 'OFF'}</span>
                    </div>
                    <div class="mt-2 text-xs text-gray-400 text-right">${new Date(device.last_update).toLocaleString()}</div>
                </div>
            </div>
        `;
    }

    function updateSidebarStatus(device) {
        const row = document.querySelector(`[data-device-id="${device.id}"]`);
        if (row) {
            const dot = row.querySelector('.status-dot');
            if (dot) dot.className = `status-dot w-3 h-3 rounded-full ${device.status === 'online' ? 'bg-green-500 animate-pulse border-2 border-white' : 'bg-red-500'}`;
        }
    }

    function refreshData() { fetchLiveData(); }
</script>
</x-app-layout>
