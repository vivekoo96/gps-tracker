<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Driver Violations</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Review and manage driver safety violations</p>
                </div>
                <a href="{{ route('admin.driver-behavior.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Back to Dashboard
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                        <select name="type" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">All Types</option>
                            <option value="harsh_braking">Harsh Braking</option>
                            <option value="harsh_acceleration">Harsh Acceleration</option>
                            <option value="harsh_cornering">Harsh Cornering</option>
                            <option value="speeding">Speeding</option>
                            <option value="excessive_idling">Excessive Idling</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Severity</label>
                        <select name="severity" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">All Severities</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Driver</label>
                        <select name="driver_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">All Drivers</option>
                            @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                        <input type="date" name="date_from" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Violations Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Driver</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Severity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($violations as $violation)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-2">{{ $violation->getIcon() }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $violation->type_label }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $violation->driver->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $violation->getSeverityColor() }}-100 text-{{ $violation->getSeverityColor() }}-800 dark:bg-{{ $violation->getSeverityColor() }}-900/20 dark:text-{{ $violation->getSeverityColor() }}-400">
                                    {{ $violation->severity_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <a href="{{ $violation->location_url }}" target="_blank" class="text-blue-600 hover:underline">
                                    {{ number_format($violation->latitude, 4) }}, {{ number_format($violation->longitude, 4) }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $violation->occurred_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($violation->acknowledged_at)
                                <span class="text-green-600 dark:text-green-400">✓ Acknowledged</span>
                                @else
                                <a href="{{ route('admin.driver-behavior.violations.show', $violation->id) }}" class="text-blue-600 hover:underline">Review</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No violations found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $violations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
