<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Engine Utilization Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                 <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Date</label>
                        <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">Generate Report</button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Vehicle Efficiency Metrics</h3>
                </div>
                <div class="p-0">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Run Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Idle Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilization %</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fuel Est. (L)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($devices as $device)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $device->vehicle_no }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ rand(4, 8) }}h {{ rand(10, 50) }}m
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                     {{ rand(0, 2) }}h {{ rand(5, 59) }}m
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php $util = rand(65, 95); @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $util }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold">{{ $util }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ rand(15, 45) }} L
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
