<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Ticket #') . $ticket->id }}
            </h2>
             <a href="{{ route('admin.tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column: Ticket Info & Device Info -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Ticket Details -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ticket Details</h3>
                             <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                {{ $ticket->status === 'OPEN' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $ticket->status === 'IN_PROGRESS' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $ticket->status === 'CLOSED' ? 'bg-green-100 text-green-800' : '' }}
                            ">
                                {{ $ticket->status }}
                            </span>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alert Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-bold">{{ $ticket->alert_type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Raised At</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->raised_at }}</dd>
                                </div>
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                                        {{ $ticket->description }}
                                    </dd>
                                </div>
                                @if($ticket->closed_at)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Closed At</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->closed_at }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Closed By</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->closedBy->name ?? 'System' }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Device Details -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Vehicle / Device Information</h3>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Device Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->device->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Vehicle Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->device->vehicle_no ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Driver Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->device->driver_name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $ticket->device->driver_contact ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Zone / Ward</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $ticket->device->zone->name ?? '-' }} / {{ $ticket->device->ward->name ?? '-' }}
                                    </dd>
                                </div>
                            </dl>
                             <div class="mt-6">
                                <a href="{{ route('admin.gps.device-map', $ticket->device->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 hover:underline">
                                    View Live Location on Map &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Actions & Logs -->
                <div class="space-y-6">
                    <!-- Action Form -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Take Action</h3>
                        </div>
                        <div class="p-6">
                            <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-4">
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Update Status</label>
                                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="OPEN" {{ $ticket->status == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                                        <option value="IN_PROGRESS" {{ $ticket->status == 'IN_PROGRESS' ? 'selected' : '' }}>IN PROGRESS</option>
                                        <option value="CLOSED" {{ $ticket->status == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Action Taken / Comments</label>
                                    <textarea id="notes" name="notes" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>

                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Update Ticket
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Ticket History Log -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ticket History</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                @forelse($ticket->logs->sortByDesc('created_at') as $log)
                                    <li class="relative pb-4 border-l-2 border-gray-200 dark:border-gray-700 pl-4 last:border-0">
                                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-600"></div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $log->action }} <span class="text-xs font-normal text-gray-500 ml-2">{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            by {{ $log->user->name ?? 'System' }}
                                        </div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 bg-gray-50 dark:bg-gray-700/50 p-2 rounded">
                                            {{ $log->notes }}
                                        </p>
                                    </li>
                                @empty
                                    <li class="text-sm text-gray-500">No activity recorded.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
