<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                {{ __('Create Vendor') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Admin Information</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Enter the details to register a new Admin (Tenant) in the system.</p>
                </div>
                
                <div class="p-8">
                    <form method="POST" action="{{ route('super_admin.vendors.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Company Name -->
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. Acme Logistics Pvt Ltd" required autofocus>
                                </div>
                                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                            </div>

                            <!-- Subdomain -->
                            <div>
                                <label for="subdomain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subdomain</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                        </svg>
                                    </div>
                                    <input type="text" name="subdomain" id="subdomain" value="{{ old('subdomain') }}" class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="company-name">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Will be: subdomain.gps-tracker.com</p>
                                <x-input-error :messages="$errors->get('subdomain')" class="mt-2" />
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                    </div>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="admin@company.com" required>
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="+1 (555) 000-0000">
                                </div>
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <!-- Logo Upload -->
                            <div class="md:col-span-2">
                                <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branding Logo</label>
                                <input type="file" name="logo" id="logo" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-indigo-300">
                                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                            </div>

                            <!-- Primary Brand Color -->
                            <div>
                                <label for="primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Brand Color</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', '#051643') }}" class="h-10 w-20 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                                    <input type="text" id="color_hex" value="{{ old('primary_color', '#051643') }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm uppercase" maxlength="7">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">This color will be used for the login page buttons and background.</p>
                                <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
                            </div>

                            <!-- Subscription Plan -->
                            <div class="md:col-span-2">
                                <label for="subscription_plan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription Plan</label>
                                <div class="relative">
                                     <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <select id="subscription_plan_id" name="subscription_plan_id" class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <option value="">Select a Plan</option>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->max_devices }} devices) - ${{ $plan->price }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Choose the initial billing plan for this vendor.</p>
                                <x-input-error :messages="$errors->get('subscription_plan_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end">
                            <a href="{{ route('super_admin.vendors.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4 transition-colors">Cancel</a>
                            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                                Create Vendor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('primary_color').addEventListener('input', function(e) {
            document.getElementById('color_hex').value = e.target.value;
        });
        document.getElementById('color_hex').addEventListener('input', function(e) {
            if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                document.getElementById('primary_color').value = e.target.value;
            }
        });
    </script>
</x-app-layout>
