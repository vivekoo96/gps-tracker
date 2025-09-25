<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - Analogue IT Solutions' : 'GPS Tracker - Analogue IT Solutions' }}</title>
        
        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTYiIGN5PSIxNiIgcj0iMTYiIGZpbGw9InVybCgjZ3JhZGllbnQwX2xpbmVhcl8xXzEpIi8+CjxwYXRoIGQ9Ik0xMC41IDIyTDE2IDEwTDIxLjUgMjJIMTkuNUwxOC41IDE5LjVIMTMuNUwxMi41IDIySDEwLjVaTTE0LjUgMTcuNUgxNy41TDE2IDEzLjVMMTQuNSAxNy41WiIgZmlsbD0id2hpdGUiLz4KPGRlZnM+CjxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZGllbnQwX2xpbmVhcl8xXzEiIHgxPSIwIiB5MT0iMCIgeDI9IjMyIiB5Mj0iMzIiIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIj4KPHN0b3Agc3RvcC1jb2xvcj0iIzI1NjNFQiIvPgo8c3RvcCBvZmZzZXQ9IjEiIHN0b3AtY29sb3I9IiMxRDRFRDgiLz4KPC9saW5lYXJHcmFkaWVudD4KPC9kZWZzPgo8L3N2Zz4K">
        
        <!-- Meta Description -->
        <meta name="description" content="Professional GPS Tracking System by Analogue IT Solutions - Real-time vehicle tracking, fleet management, and comprehensive reporting.">
        <meta name="keywords" content="GPS tracking, vehicle tracking, fleet management, Analogue IT Solutions">
        <meta name="author" content="Analogue IT Solutions">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900" x-data="{ mobileSidebar: false, sidebarCollapsed: false }" @toggle-mobile-sidebar.window="mobileSidebar = !mobileSidebar" @toggle-sidebar.window="sidebarCollapsed = !sidebarCollapsed">
            @include('layouts.navigation')

            <!-- Mobile off-canvas sidebar -->
            <div class="md:hidden" x-show="mobileSidebar" x-transition.opacity aria-hidden="true">
                <div class="fixed inset-0 z-40 flex">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black/30" @click="mobileSidebar=false"></div>
                    <!-- Drawer -->
                    <div class="relative ml-0 w-72 max-w-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-xl z-50" x-transition:enter="transform transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                        <div class="h-16 flex items-center px-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-xs">A</span>
                                </div>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Analogue IT</span>
                            </div>
                            <button class="ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="mobileSidebar=false">✕</button>
                        </div>
                        @include('layouts.sidebar')
                    </div>
                </div>
            </div>

            <div class="flex">
                <!-- Sidebar -->
                <aside class="bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-sm min-h-[calc(100vh-4rem)] sticky top-16 transition-all duration-300" :class="sidebarCollapsed ? 'w-16' : 'w-64'">
                    @include('layouts.sidebar')
                </aside>

                <div class="flex-1 bg-gray-50 dark:bg-gray-900">
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white dark:bg-gray-800 shadow border-b border-gray-200 dark:border-gray-700">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main class="p-4 md:p-6 min-h-[calc(100vh-12rem)]">
                        {{ $slot }}
                    </main>
                    
                    <!-- Footer -->
                    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-5 h-5 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-xs">A</span>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        © {{ date('Y') }} <span class="font-semibold">Analogue IT Solutions</span>. All rights reserved.
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-500">
                                    GPS Tracking System v1.0
                                </div>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
