<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GPS Tracker') }} - Admin Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900" x-data="{ mobileSidebar: false, sidebarCollapsed: false }" @toggle-mobile-sidebar.window="mobileSidebar = !mobileSidebar" @toggle-sidebar.window="sidebarCollapsed = !sidebarCollapsed">
        <!-- Mobile off-canvas sidebar -->
        <div class="md:hidden" x-show="mobileSidebar" x-transition.opacity aria-hidden="true">
            <div class="fixed inset-0 z-40 flex">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/30" @click="mobileSidebar=false"></div>
                <!-- Drawer -->
                <div class="relative ml-0 w-72 max-w-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-xl z-50" x-transition:enter="transform transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                    <div class="h-16 flex items-center px-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-2">
                            @if(site_setting('logo'))
                                <img src="{{ site_setting('logo') }}" alt="Logo" class="h-8 w-auto">
                            @else
                                <div class="w-6 h-6 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ site_setting('site_name', 'GPS Tracker') }}</span>
                                </div>
                            @endif
                        </div>
                        <button class="ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="mobileSidebar=false">✕</button>
                    </div>
                    @include('layouts.sidebar')
                </div>
            </div>
        </div>

        <div class="flex">
            <!-- Sidebar -->
            <aside class="bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-sm h-screen sticky top-0 transition-all duration-300 overflow-y-auto hidden md:block" :class="sidebarCollapsed ? 'w-16' : 'w-64'">
                @include('layouts.sidebar')
            </aside>

            <div class="flex-1 bg-gray-50 dark:bg-gray-900 min-h-screen flex flex-col">
                @include('layouts.navigation')
                
                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white dark:bg-gray-800 shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="p-4 md:p-6 min-h-[calc(100vh-12rem)]">
                    @if (session('status'))
                        <div class="max-w-7xl mx-auto mb-4">
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('status') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="max-w-7xl mx-auto mb-4">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <!-- Leaflet JS for Maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    @stack('scripts')
</body>
</html>
