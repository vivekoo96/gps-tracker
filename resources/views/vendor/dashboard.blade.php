<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Vendor Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-500 dark:text-gray-400 text-sm uppercase font-semibold">Total Devices</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $stats['total_devices'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-500 dark:text-gray-400 text-sm uppercase font-semibold">Online</div>
                    <div class="text-3xl font-bold text-green-600 mt-2">{{ $stats['online_devices'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-500 dark:text-gray-400 text-sm uppercase font-semibold">Offline</div>
                    <div class="text-3xl font-bold text-red-600 mt-2">{{ $stats['offline_devices'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-500 dark:text-gray-400 text-sm uppercase font-semibold">Plan</div>
                    <div class="text-3xl font-bold text-indigo-600 mt-2">{{ $stats['subscription'] }}</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Quick Actions</h3>
                    <div class="flex space-x-4">
                        <a href="{{ route('admin.devices.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                            Add Device
                        </a>
                        <a href="{{ route('vendor.fuel.index') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                            Fuel Sensors
                        </a>
                        <a href="{{ route('vendor.dashcam.index') }}" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                            Dashcams
                        </a>
                        <a href="{{ route('admin.gps.dashboard') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            View Map
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
