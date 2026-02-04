<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Global Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('super_admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Site Name -->
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Site Name</label>
                                <input type="text" name="site_name" value="{{ $settings['site_name'] ?? config('app.name') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Support Email -->
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Support Email</label>
                                <input type="email" name="support_email" value="{{ $settings['support_email'] ?? 'support@example.com' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Footer Text -->
                            <div class="md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Footer Text</label>
                                <input type="text" name="footer_text" value="{{ $settings['footer_text'] ?? 'All rights reserved.' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Logo -->
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Logo</label>
                                <input type="file" name="logo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @if(isset($settings['logo']))
                                    <div class="mt-2">
                                        <img src="{{ $settings['logo'] }}" alt="Logo" class="h-12">
                                    </div>
                                @endif
                            </div>

                            <!-- Favicon -->
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Favicon</label>
                                <input type="file" name="favicon" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @if(isset($settings['favicon']))
                                    <div class="mt-2">
                                        <img src="{{ $settings['favicon'] }}" alt="Favicon" class="h-8 w-8">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Gateway Settings -->
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Payment Gateway (Razorpay)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Key ID</label>
                                    <input type="text" name="razorpay_key" value="{{ $settings['razorpay_key'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="rzp_test_...">
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Key Secret</label>
                                    <input type="password" name="razorpay_secret" value="{{ $settings['razorpay_secret'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-500">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
