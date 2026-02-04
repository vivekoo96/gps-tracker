<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Performance Ranking') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Overall Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-indigo-600 rounded-xl p-6 text-white shadow-lg">
                    <h3 class="text-lg font-medium opacity-80">Best Performing Ward</h3>
                    <p class="text-3xl font-bold mt-2">{{ $wardRanking->first()['name'] ?? 'N/A' }}</p>
                    <div class="mt-4 text-sm bg-white/20 rounded-lg p-2">
                        Activity Score: {{ $wardRanking->first()['activity_score'] ?? 0 }}
                    </div>
                </div>
                <div class="bg-emerald-600 rounded-xl p-6 text-white shadow-lg">
                    <h3 class="text-lg font-medium opacity-80">Best Performing Circle</h3>
                    <p class="text-3xl font-bold mt-2">{{ $circleRanking->first()['name'] ?? 'N/A' }}</p>
                    <div class="mt-4 text-sm bg-white/20 rounded-lg p-2">
                        Activity Score: {{ $circleRanking->first()['activity_score'] ?? 0 }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Wards Ranked</h3>
                    <p class="text-3xl font-bold mt-2 text-gray-900 dark:text-gray-100">{{ $wardRanking->count() }}</p>
                    <div class="mt-4 text-sm text-gray-400">
                        Based on today's vehicle activity
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Ward Ranking Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ward-wise Ranking</h3>
                        <span class="text-xs text-gray-500">Sorted by Activity</span>
                    </div>
                    <div class="p-0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rank</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ward Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rating</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($wardRanking as $index => $ward)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($index == 0) <span class="text-yellow-500 font-bold">🥇 1st</span>
                                            @elseif($index == 1) <span class="text-gray-400 font-bold">🥈 2nd</span>
                                            @elseif($index == 2) <span class="text-orange-400 font-bold">🥉 3rd</span>
                                            @else <span class="text-gray-500">{{ $index + 1 }}th</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ward['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $ward['circle'] }} Circle</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono text-indigo-600 font-bold">{{ $ward['activity_score'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $ward['vehicle_count'] }} Vehicles</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $ward['rank_level'] === 'EXCELLENT' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $ward['rank_level'] === 'GOOD' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $ward['rank_level'] === 'AVERAGE' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $ward['rank_level'] === 'POOR' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $ward['rank_level'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Circle Ranking Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Circle-wise Ranking</h3>
                         <span class="text-xs text-gray-500">Sorted by Activity</span>
                    </div>
                    <div class="p-0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rank</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Circle Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rating</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($circleRanking as $index => $circle)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $circle['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $circle['zone'] }} Zone</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono text-indigo-600 font-bold">{{ $circle['activity_score'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $circle['ward_count'] }} Wards</div>
                                    </td>
                                     <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $circle['rank_level'] === 'EXCELLENT' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $circle['rank_level'] === 'GOOD' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $circle['rank_level'] === 'AVERAGE' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $circle['rank_level'] === 'POOR' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $circle['rank_level'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Heatmap / Note section -->
            <div class="bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800 rounded-xl p-8 text-center">
                <svg class="w-12 h-12 text-indigo-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Ward Heatmaps Integration</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-2 max-w-2xl mx-auto">The ranking algorithm currently uses vehicle activity as a proxy for garbage collection efficiency. High activity scores indicate frequent movement and potential collection patterns. Geographic heatmaps are available in the Live Tracking module.</p>
            </div>

        </div>
    </div>
</x-app-layout>
