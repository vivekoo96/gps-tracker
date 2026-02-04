<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Raise Manual Ticket') }}
            </h2>
             <a href="{{ route('admin.tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Cancel
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.tickets.store') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="device_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Vehicle / Device</label>
                            <select id="device_id" name="device_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select a Vehicle</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">
                                        {{ $device->name }} 
                                        {{ $device->vehicle_no ? "($device->vehicle_no)" : "" }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="alert_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alert Type</label>
                            <select id="alert_type" name="alert_type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="SOS">SOS Alert</option>
                                <option value="BATTERY">Battery Removal / Low Battery</option>
                                <option value="SPEED">Over Speeding</option>
                                <option value="ROUTE_DEVIATION">Route Deviation</option>
                                <option value="NO_DATA">No Communication / Offline</option>
                                <option value="OTHER">Other / User Complaint</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description / Details</label>
                            <textarea id="description" name="description" rows="4" required placeholder="Describe the issue..." class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Raise Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
