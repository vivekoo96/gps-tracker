<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Field Supervisor Dashboard') }}
            </h2>
            
            <form method="GET" action="{{ route('admin.supervisor.dashboard') }}" class="flex items-center gap-2">
                <label for="ward_id" class="text-sm text-gray-500">Supervising Ward:</label>
                <select name="ward_id" id="ward_id" onchange="this.form.submit()" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 text-sm">
                    @foreach($wards as $w)
                        <option value="{{ $w->id }}" {{ $selectedWardId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if($ward)
            <!-- Ward Quick Info -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <p class="text-sm text-gray-500">Vehicles Online</p>
                    <div class="flex items-end justify-between mt-2">
                        <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['online_vehicles'] }}/{{ $stats['total_vehicles'] }}</span>
                        <span class="text-green-500 text-sm font-bold">{{ $stats['total_vehicles'] > 0 ? round(($stats['online_vehicles']/$stats['total_vehicles'])*100) : 0 }}%</span>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <p class="text-sm text-gray-500">Pending Complaints</p>
                    <div class="flex items-end justify-between mt-2">
                        <span class="text-3xl font-bold text-red-600">{{ $stats['open_tickets'] }}</span>
                        <a href="{{ route('admin.tickets.index') }}" class="text-indigo-600 text-xs hover:underline">View All</a>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <p class="text-sm text-gray-500">Waste Capacity Status</p>
                    <div class="space-y-3 mt-2">
                        @forelse($ward->transferStations as $ts)
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span>{{ $ts->name }}</span>
                                <span>{{ $ts->waste_percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                <div class="h-1.5 rounded-full {{ $ts->waste_percentage > 80 ? 'bg-red-600' : ($ts->waste_percentage > 50 ? 'bg-orange-500' : 'bg-green-500') }}" style="width: {{ $ts->waste_percentage }}%"></div>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-gray-400">No transfer stations in this ward.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-indigo-600 p-6 rounded-xl text-white shadow-lg">
                    <p class="text-sm opacity-80 uppercase tracking-wider font-bold">Ward Efficiency</p>
                    <p class="text-3xl font-bold mt-2">82%</p>
                    <p class="text-xs mt-2 opacity-70">Top 15% in Circle</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Vehicle Status List -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100">Live Vehicle Status (Ward: {{ $ward->name }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($ward->devices as $device)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $device->vehicle_no }}</div>
                                        <div class="text-xs text-gray-500">{{ $device->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($device->is_online)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Moving</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Parked</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $device->latestPosition ? $device->latestPosition->fix_time->diffForHumans() : 'Never' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('admin.gps.device-map', $device->id) }}" class="text-indigo-600 hover:text-indigo-900">Track</a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="p-6 text-center text-gray-500">No vehicles assigned to this ward.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Ward Tickets -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden h-fit">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100">Recent Complaints</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @forelse($recentTickets as $ticket)
                        <div class="flex items-start gap-3 pb-4 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <div class="mt-1">
                                <span class="w-3 h-3 rounded-full block {{ $ticket->status === 'OPEN' ? 'bg-red-500' : 'bg-blue-500' }}"></span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $ticket->alert_type }}</p>
                                <p class="text-xs text-gray-500">For {{ $ticket->device->vehicle_no }}</p>
                                <p class="text-xs text-indigo-500 mt-1">{{ $ticket->raised_at->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </a>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 text-center py-4">No active complaints.</p>
                        @endforelse
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50">
                        <a href="{{ route('admin.tickets.create') }}" class="block w-full text-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Raise New Ticket
                        </a>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
