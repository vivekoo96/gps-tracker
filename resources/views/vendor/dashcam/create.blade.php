<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Dashcam') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('vendor.dashcam.store') }}">
                        @csrf
                        
                        <!-- Device Selection -->
                        <div class="mb-4">
                            <x-input-label for="device_id" :value="__('Select Device (Dashcam Type)')" />
                            <select id="device_id" name="device_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select a device</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->unique_id }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('device_id')" class="mt-2" />
                            @if($devices->isEmpty())
                                <p class="text-sm text-yellow-600 mt-2">No 'Dashcam' type devices available. Add a device with 'Dashcam' category first.</p>
                            @endif
                        </div>

                        <!-- Camera Model -->
                        <div class="mb-4">
                            <x-input-label for="camera_model" :value="__('Camera Model')" />
                            <x-text-input id="camera_model" class="block mt-1 w-full" type="text" name="camera_model" :value="old('camera_model')" placeholder="e.g. Hikvision K5" />
                            <x-input-error :messages="$errors->get('camera_model')" class="mt-2" />
                        </div>

                        <!-- Resolution -->
                        <div class="mb-4">
                            <x-input-label for="resolution" :value="__('Resolution')" />
                            <select id="resolution" name="resolution" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="720p">HD (720p)</option>
                                <option value="1080p" selected>Full HD (1080p)</option>
                                <option value="2K">2K</option>
                                <option value="4K">4K</option>
                            </select>
                            <x-input-error :messages="$errors->get('resolution')" class="mt-2" />
                        </div>

                        <!-- Storage Capacity -->
                        <div class="mb-4">
                            <x-input-label for="storage_capacity" :value="__('Storage Capacity')" />
                            <x-text-input id="storage_capacity" class="block mt-1 w-full" type="text" name="storage_capacity" :value="old('storage_capacity')" placeholder="e.g. 128GB SD Card" />
                            <x-input-error :messages="$errors->get('storage_capacity')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Add Dashcam') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
