<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('admin.geofences.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                                Geofences
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">Create Geofence</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create New Geofence
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="geofenceForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Info Banner -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Creating a Geofence</h4>
                        <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                            Click on the map to set the center point, then adjust the radius using the slider. The circle will show the geofence boundary.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.geofences.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @csrf

                <!-- Map Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                            Map Location
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Click on the map to set the geofence center</p>
                    </div>
                    <div class="p-6">
                        <div id="map" class="w-full h-96 rounded-lg border border-gray-300 dark:border-gray-600"></div>
                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 mb-1">Latitude</label>
                                <input type="text" x-model="latitude" readonly class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 mb-1">Longitude</label>
                                <input type="text" x-model="longitude" readonly class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Fields Section -->
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Geofence Details
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                                <textarea name="description" id="description" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('description') }}</textarea>
                            </div>

                            <!-- Radius -->
                            <div>
                                <label for="radius" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Radius: <span x-text="radius"></span> meters
                                </label>
                                <input type="range" x-model="radius" @input="updateCircle()" min="10" max="5000" step="10"
                                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                                <input type="hidden" name="radius" :value="radius">
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>10m</span>
                                    <span>5km</span>
                                </div>
                            </div>

                            <!-- Color -->
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Color</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" x-model="color" @input="updateCircle()" name="color"
                                           class="h-10 w-20 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <span class="text-sm text-gray-600 dark:text-gray-400" x-text="color"></span>
                                </div>
                            </div>

                            <!-- Hidden inputs for coordinates -->
                            <input type="hidden" name="latitude" :value="latitude">
                            <input type="hidden" name="longitude" :value="longitude">

                            <!-- Active Status -->
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                                       class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Configuration -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                Alert Settings
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="alert_on_entry" id="alert_on_entry" value="1" checked
                                       class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                <label for="alert_on_entry" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Alert on Entry</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="alert_on_exit" id="alert_on_exit" value="1" checked
                                       class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                <label for="alert_on_exit" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Alert on Exit</label>
                            </div>

                            <!-- Users to Notify -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notify Users</label>
                                <div class="max-h-40 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-lg p-3 space-y-2">
                                    @foreach($users as $user)
                                        <div class="flex items-center">
                                            <input type="checkbox" name="notify_users[]" value="{{ $user->id }}" id="user_{{ $user->id }}"
                                                   class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                            <label for="user_{{ $user->id }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $user->name }} ({{ $user->email }})
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.geofences.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Create Geofence
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function geofenceForm() {
            return {
                map: null,
                circle: null,
                marker: null,
                latitude: 28.6139,
                longitude: 77.2090,
                radius: 500,
                color: '#3b82f6',

                init() {
                    this.$nextTick(() => {
                        this.initMap();
                    });
                },

                initMap() {
                    this.map = L.map('map').setView([this.latitude, this.longitude], 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(this.map);

                    // Add initial marker and circle
                    this.updateMapMarkers();

                    // Click to set location
                    this.map.on('click', (e) => {
                        this.latitude = e.latlng.lat.toFixed(8);
                        this.longitude = e.latlng.lng.toFixed(8);
                        this.updateMapMarkers();
                    });
                },

                updateMapMarkers() {
                    // Remove existing marker and circle
                    if (this.marker) this.map.removeLayer(this.marker);
                    if (this.circle) this.map.removeLayer(this.circle);

                    // Add new marker
                    this.marker = L.marker([this.latitude, this.longitude]).addTo(this.map);

                    // Add new circle
                    this.circle = L.circle([this.latitude, this.longitude], {
                        color: this.color,
                        fillColor: this.color,
                        fillOpacity: 0.2,
                        radius: this.radius
                    }).addTo(this.map);

                    this.map.fitBounds(this.circle.getBounds());
                },

                updateCircle() {
                    if (this.circle) {
                        this.circle.setRadius(this.radius);
                        this.circle.setStyle({
                            color: this.color,
                            fillColor: this.color
                        });
                        this.map.fitBounds(this.circle.getBounds());
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
