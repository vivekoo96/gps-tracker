<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Maintenance Management</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Track vehicle maintenance, schedules, and costs</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Cost -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Cost (This Month)</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₹{{ number_format($stats['total_cost'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overdue Services -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 dark:bg-red-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Overdue Services</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['overdue_services'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Services -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Upcoming Services</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['upcoming_services'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Parts -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-orange-100 dark:bg-orange-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Low Stock Parts</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['low_stock_parts'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Overdue Reminders -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Overdue Services</h3>
                            <a href="{{ route('admin.maintenance.reminders') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View All</a>
                        </div>
                        <div class="space-y-3">
                            @forelse($overdueReminders as $reminder)
                            <div class="flex items-start justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $reminder->device->name ?? 'Unknown Device' }}
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ $reminder->task_name }}
                                    </p>
                                    @if($reminder->km_remaining)
                                    <p class="text-xs text-red-600 dark:text-red-400">
                                        Overdue by {{ abs($reminder->km_remaining) }} km
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No overdue services</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Recent Service History -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Services</h3>
                            <a href="{{ route('admin.maintenance.history') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View All</a>
                        </div>
                        <div class="space-y-3">
                            @forelse($recentRecords as $record)
                            <div class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $record->device->name ?? 'Unknown Device' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $record->task_name }} - {{ $record->service_date->format('M d, Y') }}
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        ₹{{ number_format($record->cost, 2) }}
                                    </p>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No service records yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('admin.maintenance.schedules') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Schedules</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Manage schedules</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.maintenance.history') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">History</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Service records</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.maintenance.parts') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Parts</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Inventory</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.maintenance.reminders') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Reminders</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Active alerts</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
