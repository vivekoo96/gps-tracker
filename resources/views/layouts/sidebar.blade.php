<nav class="px-2 py-4 space-y-1 text-sm h-full overflow-y-auto" x-data="{ collapsed: false }" @toggle-sidebar.window="collapsed = !collapsed">
    <!-- Sidebar Branding -->
    <div class="mb-6 px-3" x-show="!collapsed">
        <div class="flex items-center space-x-2 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center shadow-md">
                <span class="text-white font-bold text-sm">A</span>
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-bold text-blue-900 dark:text-blue-100 leading-none">ANALOGUE</span>
                <span class="text-xs text-blue-700 dark:text-blue-300 leading-none">IT Solutions</span>
            </div>
        </div>
    </div>
    <!-- Dashboard -->
    <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}" :title="collapsed ? 'Dashboard' : ''">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
        </svg>
        <span x-show="!collapsed" class="transition-opacity duration-300">Dashboard</span>
    </a>

    <!-- GPS Tracking Section -->
    <div class="mt-6" x-show="!collapsed">
        <div class="px-3 py-2">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">GPS Tracking</h3>
        </div>
        
        <a href="{{ route('admin.gps.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.gps.dashboard') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">GPS Dashboard</span>
        </a>
        
        <a href="{{ route('admin.gps.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.gps.device-map') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Live Map</span>
        </a>
        
        <a href="{{ route('admin.geofences.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.geofences.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Geofences</span>
        </a>
        
        <a href="{{ route('admin.gps.add-test-data') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-green-500" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">🧪 Test Data</span>
        </a>
    </div>

    <!-- Device Management -->
    <div class="mt-6" x-show="!collapsed">
        <div class="px-3 py-2">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Device Management</h3>
        </div>
        
        <a href="{{ route('admin.devices.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.devices.index') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Devices
        </a>
        
        <a href="{{ route('admin.devices.create') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.devices.create') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Add Device</span>
        </a>
        
    </div>

    @role('admin')
    <!-- Admin Section -->
    <div class="mt-6" x-show="!collapsed">
        <div class="px-3 py-2">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Administration</h3>
        </div>
        
        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12m-12 3.75h12m-12 3.75h12M3.75 6.75h.008v.008H3.75V6.75zm0 3.75h.008v.008H3.75V10.5zm0 3.75h.008v.008H3.75v-.008z"/>
            </svg>
            Admin Home
        </a>
        
        <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">Users</span>
        </a>
        
        <a href="{{ route('admin.users.roles.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('admin.users.roles.*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 20a6 6 0 10-12 0M12 14a4 4 0 100-8 4 4 0 000 8z"/>
            </svg>
            <span x-show="!collapsed" class="transition-opacity duration-300">User Roles</span>
        </a>
    </div>
    @endrole

    <!-- Account Section -->
    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4" x-show="!collapsed">
        <div class="px-3 py-2">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Account</h3>
        </div>
        
        <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs('profile.edit') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 text-gray-400" :class="collapsed ? 'mr-0' : 'mr-3'">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232a3 3 0 114.243 4.243L7.5 21.45 3 22.5l1.05-4.5L15.232 5.232z"/>
            </svg>
            Profile
        </a>
        
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="flex items-center w-full px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4 mr-3">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</nav>
