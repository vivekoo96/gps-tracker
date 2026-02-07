<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Fuel Management</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Monitor fuel consumption, efficiency, and costs</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Fuel Consumed -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Fuel Consumed (This Month)</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format(abs($stats['total_fuel_consumed']), 1) }} L</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Efficiency -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 dark:bg-green-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Efficiency</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['avg_efficiency'] ?? 0, 2) }} km/L</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Refuel Cost -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Refuel Cost</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₹{{ number_format($stats['total_refuel_cost'] ?? 0, 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Alerts -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 dark:bg-red-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Alerts</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['active_alerts'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Transactions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Transactions</h3>
                            <a href="{{ route('admin.fuel.transactions') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View All</a>
                        </div>
                        <div class="space-y-3">
                            @forelse($recentTransactions as $transaction)
                            <div class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $transaction->device->name ?? 'Unknown Device' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $transaction->detected_at->diffForHumans() }}
                                    </p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $transaction->type_badge_color }}-100 text-{{ $transaction->type_badge_color }}-800 dark:bg-{{ $transaction->type_badge_color }}-900/20 dark:text-{{ $transaction->type_badge_color }}-400">
                                        {{ $transaction->type_label }}
                                    </span>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $transaction->fuel_change > 0 ? '+' : '' }}{{ number_format($transaction->fuel_change, 1) }} L
                                    </p>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No transactions recorded yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Top Efficient Devices -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Most Efficient Vehicles</h3>
                            <a href="{{ route('admin.fuel.efficiency') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View All</a>
                        </div>
                        <div class="space-y-3">
                            @forelse($topDevices as $report)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                                            <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ $loop->iteration }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $report->device->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($report->total_distance, 1) }} km</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($report->average_efficiency, 2) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">km/L</p>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No efficiency data yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('admin.fuel.transactions') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Transactions</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">View all fuel events</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.fuel.efficiency') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Efficiency</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">View efficiency reports</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.fuel.analytics') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Analytics</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">View charts & trends</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.fuel.alerts') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Alerts</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Manage fuel alerts</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
