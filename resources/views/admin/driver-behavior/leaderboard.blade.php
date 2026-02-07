<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Driver Leaderboard</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Top performing drivers ranked by safety score</p>
                </div>
                <a href="{{ route('admin.driver-behavior.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Back to Dashboard
                </a>
            </div>

            <!-- Period Selector -->
            <div class="mb-6 flex space-x-2">
                <a href="?period=daily" class="px-4 py-2 rounded-lg {{ $period == 'daily' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                    Daily
                </a>
                <a href="?period=weekly" class="px-4 py-2 rounded-lg {{ $period == 'weekly' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                    Weekly
                </a>
                <a href="?period=monthly" class="px-4 py-2 rounded-lg {{ $period == 'monthly' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                    Monthly
                </a>
            </div>

            <!-- Leaderboard -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($scores as $score)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg {{ $score->rank <= 3 ? 'border-2 border-' . ($score->rank == 1 ? 'yellow' : ($score->rank == 2 ? 'gray' : 'orange')) . '-400' : '' }}">
                            <div class="flex items-center space-x-4 flex-1">
                                <!-- Rank Badge -->
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-full bg-{{ $score->getScoreColor() }}-100 dark:bg-{{ $score->getScoreColor() }}-900/20 flex items-center justify-center">
                                        @if($score->rank == 1)
                                        <span class="text-2xl">🥇</span>
                                        @elseif($score->rank == 2)
                                        <span class="text-2xl">🥈</span>
                                        @elseif($score->rank == 3)
                                        <span class="text-2xl">🥉</span>
                                        @else
                                        <span class="text-lg font-bold text-{{ $score->getScoreColor() }}-600 dark:text-{{ $score->getScoreColor() }}-400">{{ $score->rank }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Driver Info -->
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $score->driver->name }}</h3>
                                    <div class="flex items-center space-x-4 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ $score->total_trips }} trips</span>
                                        <span>•</span>
                                        <span>{{ number_format($score->total_distance, 1) }} km</span>
                                        <span>•</span>
                                        <span>{{ $score->total_violations }} violations</span>
                                    </div>
                                </div>

                                <!-- Score Breakdown -->
                                <div class="hidden md:flex space-x-6">
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Safety</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($score->safety_score, 1) }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Efficiency</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($score->efficiency_score, 1) }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Compliance</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($score->compliance_score, 1) }}</p>
                                    </div>
                                </div>

                                <!-- Overall Score -->
                                <div class="text-right">
                                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($score->score, 1) }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Grade: <span class="font-semibold">{{ $score->score_grade }}</span></p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $score->getScoreColor() }}-100 text-{{ $score->getScoreColor() }}-800 dark:bg-{{ $score->getScoreColor() }}-900/20 dark:text-{{ $score->getScoreColor() }}-400">
                                        {{ ucfirst($score->performance_level) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <p>No scores available for this period</p>
                            <p class="text-sm mt-2">Scores are calculated automatically based on driver behavior</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $scores->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
