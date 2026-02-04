<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashcams') }}
            </h2>
            <a href="{{ route('vendor.dashcam.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                Add Dashcam
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($dashcams->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            No dashcams configured yet.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($dashcams as $cam)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="font-bold text-lg">{{ $cam->device->name }}</h3>
                                            <p class="text-sm text-gray-500">{{ $cam->camera_model ?? 'Generic Model' }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $cam->status == 'online' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800' }}">
                                            {{ ucfirst($cam->status) }}
                                        </span>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Resolution</span>
                                            <span class="font-semibold">{{ $cam->resolution }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Storage</span>
                                            <span class="font-semibold">{{ $cam->storage_capacity }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600 flex justify-between">
                                        <button class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View Stream</button>
                                        <button class="text-gray-600 hover:text-gray-800 text-sm">Recordings</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $dashcams->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
