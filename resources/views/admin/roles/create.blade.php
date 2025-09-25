<x-app-layout>
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold mb-4">Create Role</h1>
            <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2">
                    @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Save</button>
                <a href="{{ route('admin.roles.index') }}" class="ml-2 underline">Cancel</a>
            </form>
        </div>
    </div>
 </x-app-layout>


