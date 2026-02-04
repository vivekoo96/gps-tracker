<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Trip History Report') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Filters -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 sticky top-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filters</h3>
                        <form method="GET" action="{{ route('admin.reports.trips') }}">
                            <div class="mb-4">
                                <label for="from_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date & Time</label>
                                <input type="datetime-local" name="from_date" id="from_date" value="{{ request('from_date', now()->startOfDay()->format('Y-m-d\TH:i')) }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            
                            <div class="mb-4">
                                <label for="to_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date & Time</label>
                                <input type="datetime-local" name="to_date" id="to_date" value="{{ request('to_date', now()->endOfDay()->format('Y-m-d\TH:i')) }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="mb-4">
                                <label for="device_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vehicle</label>
                                <select name="device_id" id="device_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select a Vehicle</option>
                                    @foreach($devices as $device)
                                        <option value="{{ $device->id }}" {{ request('device_id') == $device->id ? 'selected' : '' }}>
                                            {{ $device->name }} ({{ $device->vehicle_no }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Find Trips
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Results -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                        @if(isset($trips) && $trips->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Start Time</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">End Time</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Duration</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Distance</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Route</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($trips as $trip)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $trip['start_time']->format('M d H:i') }}
                                                    <div class="text-xs text-gray-500">{{ number_format($trip['start_lat'], 4) }}, {{ number_format($trip['start_lon'], 4) }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $trip['end_time']->format('M d H:i') }}
                                                    <div class="text-xs text-gray-500">{{ number_format($trip['end_lat'], 4) }}, {{ number_format($trip['end_lon'], 4) }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $trip['duration'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-indigo-600 dark:text-indigo-400">
                                                    {{ number_format($trip['distance'], 2) }} km
                                                </td>
                                                 <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">View Map</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif(request()->has('from_date'))
                            <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-lg">No trips found. (Check if ignition data is available)</p>
                            </div>
                        @else
                            <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path></svg>
                                <p class="text-lg">Select a vehicle and date range to view trip history.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
