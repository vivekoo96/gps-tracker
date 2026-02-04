<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Fuel Sensor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('vendor.fuel.store') }}">
                        @csrf
                        
                        <!-- Device Selection -->
                        <div class="mb-4">
                            <x-input-label for="device_id" :value="__('Select Vehicle/Device')" />
                            <select id="device_id" name="device_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select a device</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->unique_id }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('device_id')" class="mt-2" />
                            @if($devices->isEmpty())
                                <p class="text-sm text-yellow-600 mt-2">No compatible 'Fuel' type devices found without sensors.</p>
                            @endif
                        </div>

                        <!-- Tank Capacity -->
                        <div class="mb-4">
                            <x-input-label for="tank_capacity" :value="__('Tank Capacity (Liters)')" />
                            <x-text-input id="tank_capacity" class="block mt-1 w-full" type="number" step="0.01" name="tank_capacity" :value="old('tank_capacity')" required />
                            <x-input-error :messages="$errors->get('tank_capacity')" class="mt-2" />
                        </div>

                        <!-- Calibration Data (Optional JSON) -->
                        <div class="mb-4">
                            <x-input-label for="calibration_data" :value="__('Calibration Data (JSON)')" />
                            <textarea id="calibration_data" name="calibration_data" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder='{"0":0, "100":50, "4095":100}'>{{ old('calibration_data') }}</textarea>
                            <x-input-error :messages="$errors->get('calibration_data')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Add Sensor') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
