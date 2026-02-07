<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Maintenance Reminders</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Active maintenance alerts and notifications</p>
            </div>

            <!-- Filter Tabs -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8">
                    <a href="?type=all" class="border-b-2 {{ request('type', 'all') === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 font-medium text-sm">
                        All Reminders
                    </a>
                    <a href="?type=overdue" class="border-b-2 {{ request('type') === 'overdue' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 font-medium text-sm">
                        Overdue
                    </a>
                    <a href="?type=upcoming" class="border-b-2 {{ request('type') === 'upcoming' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 font-medium text-sm">
                        Upcoming
                    </a>
                </nav>
            </div>

            <div class="space-y-4">
                @forelse($reminders as $reminder)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border-l-4 
                    @if($reminder->reminder_type === 'overdue') border-red-500
                    @elseif($reminder->reminder_type === 'critical') border-orange-500
                    @else border-yellow-500
                    @endif">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $reminder->device->name ?? 'Unknown Device' }}
                                </h3>
                                <span class="ml-3 px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($reminder->reminder_type === 'overdue') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($reminder->reminder_type === 'critical') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @endif">
                                    {{ ucfirst($reminder->reminder_type) }}
                                </span>
                                @if($reminder->is_acknowledged)
                                <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Acknowledged
                                </span>
                                @endif
                            </div>
                            
                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $reminder->task_name }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $reminder->message }}</p>
                            
                            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                @if($reminder->km_remaining !== null)
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Distance Remaining:</span>
                                    <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $reminder->km_remaining >= 0 ? number_format($reminder->km_remaining) : 'Overdue by ' . number_format(abs($reminder->km_remaining)) }} km
                                    </span>
                                </div>
                                @endif
                                
                                @if($reminder->days_remaining !== null)
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Days Remaining:</span>
                                    <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $reminder->days_remaining >= 0 ? $reminder->days_remaining : 'Overdue by ' . abs($reminder->days_remaining) }} days
                                    </span>
                                </div>
                                @endif
                                
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Current Odometer:</span>
                                    <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">{{ number_format($reminder->current_km) }} km</span>
                                </div>
                                
                                @if($reminder->due_date)
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Due Date:</span>
                                    <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">{{ $reminder->due_date->format('M d, Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        @if(!$reminder->is_acknowledged)
                        <div class="ml-4">
                            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                                Acknowledge
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-4 text-gray-500 dark:text-gray-400">No reminders found. All maintenance is up to date!</p>
                </div>
                @endforelse
            </div>
            
            <div class="mt-6">
                {{ $reminders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
