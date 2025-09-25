<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Users</h2>
            <button x-data x-on:click="$dispatch('open-add-user')" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Add User
            </button>
        </div>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
        <style>
            /* Hide modals by default until Alpine.js loads */
            [x-cloak] { display: none !important; }
        </style>
    @endpush

    <div class="py-6" x-data="{ 
        showAdd: false, 
        showEdit: false, 
        showDelete: false, 
        editUser: {}, 
        deleteUser: {},
        init() {
            // Only show modals if there are validation errors from form submission
            @if($errors->any() && !session('status') && old('_token'))
                @if(request()->isMethod('post') && !request()->has('_method'))
                    this.showAdd = true;
                @elseif(request()->isMethod('post') && request()->input('_method') === 'PUT')
                    this.showEdit = true;
                    this.editUser = { 
                        id: '{{ old('user_id', '') }}', 
                        name: '{{ old('name', '') }}', 
                        email: '{{ old('email', '') }}' 
                    };
                @endif
            @endif
        }
    }" 
    @open-add-user.window="showAdd=true" 
    @keydown.escape.window="showAdd=false; showEdit=false; showDelete=false">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('status'))
                        <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ session('error') }}
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <strong>Please fix the following errors:</strong>
                            </div>
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table id="users-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Roles</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($users as $i => $user)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $i + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($user->roles->count() > 0)
                                                @foreach($user->roles as $role)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-1">
                                                        {{ ucfirst($role->name) }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">No roles</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($user->email_verified_at)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $user->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <button @click="editUser = { id: {{ $user->id }}, name: {{ json_encode($user->name) }}, email: {{ json_encode($user->email) }} }; showEdit = true" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" type="button">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                @if($user->id !== auth()->id())
                                                <button @click="deleteUser = { id: {{ $user->id }}, name: {{ json_encode($user->name) }} }; showDelete = true" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" type="button">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add User Modal -->
        <div x-show="showAdd" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" @click="showAdd=false"></div>
            <div class="relative bg-white dark:bg-gray-800 w-full max-w-lg mx-auto rounded-lg shadow-lg">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center">
                    <h3 class="text-lg font-medium">Add User</h3>
                    <button class="ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="showAdd=false">✕</button>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}" class="px-5 py-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input name="name" type="text" value="{{ old('name') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 @error('name') border-red-500 @enderror" required>
                        @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input name="email" type="email" value="{{ old('email') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 @error('email') border-red-500 @enderror" required>
                        @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Password</label>
                            <input name="password" type="password" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 @error('password') border-red-500 @enderror" required>
                            @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Confirm Password</label>
                            <input name="password_confirmation" type="password" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 @error('password') border-red-500 @enderror" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Role (optional)</label>
                        <select name="role" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 @error('role') border-red-500 @enderror">
                            <option value="">— Select role —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('role')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showAdd=false" class="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Create</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div x-show="showEdit" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" @click="showEdit=false"></div>
            <div class="relative bg-white dark:bg-gray-800 w-full max-w-lg mx-auto rounded-lg shadow-lg">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center">
                    <h3 class="text-lg font-medium">Edit User</h3>
                    <button class="ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="showEdit=false">✕</button>
                </div>
                <form :action="`{{ route('admin.users.index') }}/${editUser.id}`" method="POST" class="px-5 py-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input name="name" type="text" x-model="editUser.name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input name="email" type="email" x-model="editUser.email" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">New Password (optional)</label>
                        <input name="password" type="password" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Role (optional)</label>
                        <select name="role" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                            <option value="">— Select role —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showEdit=false" class="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete User Modal -->
        <div x-show="showDelete" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" @click="showDelete=false"></div>
            <div class="relative bg-white dark:bg-gray-800 w-full max-w-md mx-auto rounded-lg shadow-lg">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center">
                    <h3 class="text-lg font-medium text-red-600">Delete User</h3>
                    <button class="ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="showDelete=false">✕</button>
                </div>
                <div class="px-5 py-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Are you sure you want to delete <strong x-text="deleteUser.name"></strong>? This action cannot be undone.
                    </p>
                    <form :action="`{{ route('admin.users.index') }}/${deleteUser.id}`" method="POST" class="flex items-center justify-end gap-3">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="showDelete=false" class="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('#users-table').DataTable({
                    pageLength: 10,
                    order: [[0, 'asc']],
                    responsive: true,
                    language: {
                        search: "Search users:",
                        lengthMenu: "Show _MENU_ users per page",
                        info: "Showing _START_ to _END_ of _TOTAL_ users",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
            });

            // Modal functions are now handled directly by Alpine.js directives
        </script>
    @endpush
</x-app-layout>
