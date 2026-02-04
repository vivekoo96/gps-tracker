<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Super Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Grid -->
            <div class="space-y-6 mb-8">
                <!-- Top Row: 3 Cards (Vendors, Users, Devices) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Vendors -->
                    <a href="{{ route('super_admin.vendors.index') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 relative group hover:shadow-lg transition-all duration-300 block">
                        <div class="p-6 relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Total</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $totalVendors }}</h3>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Registered Vendors</p>
                        </div>
                        <div class="absolute right-0 bottom-0 opacity-10 transform translate-y-1/4 translate-x-1/4">
                            <svg class="w-32 h-32 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </a>

                    <!-- Users -->
                    <a href="{{ route('admin.users.index') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 relative group hover:shadow-lg transition-all duration-300 block">
                        <div class="p-6 relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Active</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $totalUsers }}</h3>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                        </div>
                        <div class="absolute right-0 bottom-0 opacity-10 transform translate-y-1/4 translate-x-1/4">
                            <svg class="w-32 h-32 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </a>

                    <!-- Devices -->
                    <a href="{{ route('admin.devices.index') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 relative group hover:shadow-lg transition-all duration-300 block">
                        <div class="p-6 relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-lg text-green-600 dark:text-green-400 group-hover:bg-green-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                    </svg>
                                </div>
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Online</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $totalDevices }}</h3>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tracked Devices</p>
                        </div>
                        <div class="absolute right-0 bottom-0 opacity-10 transform translate-y-1/4 translate-x-1/4">
                            <svg class="w-32 h-32 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                        </div>
                    </a>
                </div>

                <!-- Bottom Row: 2 Cards (Fuel, Dashcams) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fuel Sensors -->
                    <a href="{{ route('vendor.fuel.index') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 relative group hover:shadow-lg transition-all duration-300 block">
                        <div class="p-6 relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg text-yellow-600 dark:text-yellow-400 group-hover:bg-yellow-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Monitoring</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $totalFuelSensors }}</h3>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fuel Sensors</p>
                        </div>
                        <div class="absolute right-0 bottom-0 opacity-10 transform translate-y-1/4 translate-x-1/4">
                            <svg class="w-32 h-32 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </a>

                    <!-- Dashcams -->
                    <a href="{{ route('vendor.dashcam.index') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 relative group hover:shadow-lg transition-all duration-300 block">
                        <div class="p-6 relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-red-50 dark:bg-red-900/30 rounded-lg text-red-600 dark:text-red-400 group-hover:bg-red-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Live</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $totalDashcams }}</h3>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dashcams</p>
                        </div>
                        <div class="absolute right-0 bottom-0 opacity-10 transform translate-y-1/4 translate-x-1/4">
                            <svg class="w-32 h-32 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">System Growth (This Year)</h3>
                <div class="relative h-96 w-full">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('growthChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [
                        {
                            label: 'Users',
                            data: {{ json_encode($userGrowth) }},
                            borderColor: '#3b82f6', // Blue 500
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.4
                        },
                        {
                            label: 'Devices',
                            data: {{ json_encode($deviceGrowth) }},
                            borderColor: '#10b981', // Green 500
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.4
                        },
                        {
                            label: 'Fuel Sensors',
                            data: {{ json_encode($fuelGrowth) }},
                            borderColor: '#f59e0b', // Yellow 500
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            borderWidth: 2,
                            tension: 0.4
                        },
                        {
                            label: 'Dashcams',
                            data: {{ json_encode($dashcamGrowth) }},
                            borderColor: '#ef4444', // Red 500
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(156, 163, 175, 0.1)'
                            },
                            ticks: {
                                color: '#9ca3af'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#9ca3af'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#9ca3af'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
