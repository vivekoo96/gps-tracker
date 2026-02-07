<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Driver Behavior Monitoring</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Track driver safety, performance, and compliance</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Violations -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 dark:bg-red-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Violations</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_violations']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Critical Violations -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Critical</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['critical_violations']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Score -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Score</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['avg_score'] ?? 0, 1) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unread Alerts -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900/20 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Alerts</dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['unread_alerts']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Violations -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Violations</h3>
                            <a href="{{ route('admin.driver-behavior.violations') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View All</a>
                        </div>
                        <div class="space-y-3">
                            @forelse($recentViolations as $violation)
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-2xl">{{ $violation->getIcon() }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $violation->type_label }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $violation->driver->name ?? 'Unknown Driver' }} • {{ $violation->occurred_at->diffForHumans() }}
                                    </p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $violation->getSeverityColor() }}-100 text-{{ $violation->getSeverityColor() }}-800 dark:bg-{{ $violation->getSeverityColor() }}-900/20 dark:text-{{ $violation->getSeverityColor() }}-400">
                                        {{ $violation->severity_label }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No violations recorded yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Top Performers</h3>
                            <a href="{{ route('admin.driver-behavior.leaderboard') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Leaderboard</a>
                        </div>
                        <div class="space-y-3">
                            @forelse($topDrivers as $score)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-{{ $score->getScoreColor() }}-100 dark:bg-{{ $score->getScoreColor() }}-900/20 flex items-center justify-center">
                                            <span class="text-sm font-bold text-{{ $score->getScoreColor() }}-600 dark:text-{{ $score->getScoreColor() }}-400">{{ $score->rank }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $score->driver->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $score->total_violations }} violations</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($score->score, 1) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Grade: {{ $score->score_grade }}</p>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No scores calculated yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.driver-behavior.violations') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">View Violations</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Review and acknowledge violations</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.driver-behavior.leaderboard') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Leaderboard</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">View driver rankings</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.driver-behavior.alerts') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Alerts</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Manage safety alerts</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
