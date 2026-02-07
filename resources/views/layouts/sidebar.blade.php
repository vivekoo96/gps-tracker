<nav class="px-2 py-4 space-y-1 text-sm h-full overflow-y-auto" x-data="{ collapsed: false }" @toggle-sidebar.window="collapsed = !collapsed">
    <!-- Sidebar Branding -->
    <div class="flex items-center justify-center p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
        @php
            $logoUrl = null;
            if (auth()->check() && !auth()->user()->isSuperAdmin() && auth()->user()->vendor_id) {
                $vendor = \App\Models\Vendor::find(auth()->user()->vendor_id);
                if ($vendor && $vendor->logo) {
                    $logoUrl = asset('storage/' . $vendor->logo);
                }
            }
            if (!$logoUrl) {
                $logoUrl = site_setting('logo');
            }
        @endphp

        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo" class="h-8 w-auto object-contain">
        @else
            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center shadow-md">
                <span class="text-white font-bold text-sm">A</span>
            </div>
        @endif
    </div>
    <!-- Dashboard -->
    <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Dashboard' : ''">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
        </svg>
        <span x-show="!collapsed" class="transition-opacity duration-300">Dashboard</span>
    </a>

    <!-- Super Admin Section -->
    @if(auth()->user()->isSuperAdmin())
    <div class="mt-6">
        <div class="px-3 py-2" x-show="!collapsed">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Super Admin</h3>
        </div>
        <a href="{{ route('super_admin.vendors.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('super_admin.vendors.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Manage Admins' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Manage Admins</span>
        </a>

        <a href="{{ route('super_admin.settings.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('super_admin.settings.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Global Settings' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Global Settings</span>
        </a>
    </div>
    @endif

    <!-- GPS Tracking Section -->
    <div class="mt-6">
        <div class="px-3 py-2" x-show="!collapsed">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">GPS Tracking</h3>
        </div>
        
        <a href="{{ route('admin.gps.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.gps.dashboard') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'GPS Dashboard' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">GPS Dashboard</span>
        </a>
        
        <a href="{{ route('tracking.live') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('tracking.live') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Live Map' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Live Map</span>
        </a>
        
        <a href="{{ route('admin.geofences.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.geofences.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Geofences' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.32 7.78l-6.38-4.2a2 2 0 00-2.26 0l-6.38 4.2a2 2 0 00-.7 2.44l2.5 6.4a2 2 0 003 1.07l4.72-2.36a2 2 0 000-3.58L9.1 9.4"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22v-6.5"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Geofences</span>
        </a>

    </div>

    <!-- Device Management -->
    <div class="mt-6">
        <div class="px-3 py-2" x-show="!collapsed">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Device Management</h3>
        </div>
        
        <a href="{{ route('admin.devices.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.devices.index') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Devices' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Devices</span>
        </a>
        
        <a href="{{ route('admin.devices.create') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.devices.create') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Add Device' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Add Device</span>
        </a>
        
    </div>

    <!-- Fleet Management Section -->
    <div class="mt-6">
        <div class="px-3 py-2" x-show="!collapsed">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fleet Management</h3>
        </div>
        
        <a href="{{ route('admin.driver-behavior.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.driver-behavior.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Driver Behavior' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Driver Behavior</span>
        </a>

        <a href="{{ route('admin.fuel.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.fuel.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Fuel Management' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Fuel Management</span>
        </a>

        <a href="{{ route('admin.maintenance.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.maintenance.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Maintenance' : ''">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Maintenance</span>
        </a>
    </div>

    @hasanyrole('super_admin|vendor_admin')
    <!-- Admin Section -->
    <div class="mt-6">
        <div class="px-3 py-2" x-show="!collapsed">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">GHMC Masters</h3>
        </div>

        <a href="{{ route('admin.zones.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.zones.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Zones</span>
        </a>

        <a href="{{ route('admin.circles.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.circles.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Circles</span>
        </a>

        <a href="{{ route('admin.wards.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.wards.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Wards</span>
        </a>

        <a href="{{ route('admin.transfer-stations.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.transfer-stations.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Transfer Stations</span>
        </a>

        <a href="{{ route('admin.landmarks.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.landmarks.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Landmarks</span>
        </a>

        <a href="{{ route('admin.routes.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.routes.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Routes</span>
        </a>

        <div class="px-3 py-2 mt-4" x-show="!collapsed">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alerts & Support</h3>
        </div>

        <a href="{{ route('admin.tickets.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.tickets.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Ticket Management</span>
        </a>

        <a href="{{ route('admin.reports.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.reports.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Reports & Analytics</span>
        </a>

        <a href="{{ route('admin.reports.route-replay') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.reports.route-replay') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Route Replay</span>
        </a>

        <a href="{{ route('admin.ranking.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.ranking.*') ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Performance Ranking</span>
        </a>

        <a href="{{ route('admin.supervisor.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.supervisor.*') ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Supervisor View</span>
        </a>

        <div class="px-3 py-2 mt-4" x-show="!collapsed">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Administration</h3>
        </div>
        
        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12m-12 3.75h12m-12 3.75h12M3.75 6.75h.008v.008H3.75V6.75zm0 3.75h.008v.008H3.75V10.5zm0 3.75h.008v.008H3.75v-.008z"/>
            </svg>
            Admin Home
        </a>
        
        <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Users</span>
        </a>
        
        <a href="{{ route('admin.users.roles.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.users.roles.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 20a6 6 0 10-12 0M12 14a4 4 0 100-8 4 4 0 000 8z"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">User Roles</span>
        </a>
    </div>
    @endhasanyrole

    @hasanyrole('super_admin|vendor_admin|admin')
    <!-- Integrations & API Section -->
    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
        <div class="px-3 py-2" x-show="!collapsed">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Integrations & API</h3>
        </div>
        
        <a href="{{ route('developer.portal.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('developer.portal.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Developer Portal</span>
        </a>
    </div>
    @endhasanyrole

    <!-- Account Section -->
    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4" x-show="!collapsed">
        <div class="px-3 py-2">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Account</h3>
        </div>
        
        <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('profile.edit') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232a3 3 0 114.243 4.243L7.5 21.45 3 22.5l1.05-4.5L15.232 5.232z"/>
            </svg>
            Profile
        </a>
        
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="flex items-center w-full px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-6 mr-3">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</nav>
