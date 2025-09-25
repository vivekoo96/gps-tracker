<x-app-layout>
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold mb-4">Edit Device</h1>
            <form action="{{ route('admin.devices.update', $device) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', $device->name) }}" class="w-full border rounded px-3 py-2">
                    @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Model</label>
                    <select name="model" class="w-full border rounded px-3 py-2">
                        <option value="GT800" @selected(old('model', $device->model)==='GT800')>GT800</option>
                        <option value="MT100" @selected(old('model', $device->model)==='MT100')>MT100</option>
                    </select>
                    @error('model')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">IMEI</label>
                    <input type="text" name="imei" value="{{ old('imei', $device->imei) }}" class="w-full border rounded px-3 py-2">
                    @error('imei')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">SIM Number</label>
                    <input type="text" name="sim_number" value="{{ old('sim_number', $device->sim_number) }}" class="w-full border rounded px-3 py-2">
                    @error('sim_number')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2">
                        <option value="inactive" @selected(old('status', $device->status)==='inactive')>inactive</option>
                        <option value="active" @selected(old('status', $device->status)==='active')>active</option>
                    </select>
                    @error('status')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Update</button>
                <a href="{{ route('admin.devices.index') }}" class="ml-2 underline">Cancel</a>
            </form>
        </div>
    </div>
</x-app-layout>


