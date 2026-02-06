<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GpsDataController;
use App\Http\Controllers\RealTimeController;
use App\Http\Controllers\HighTrafficGpsController;
use App\Http\Controllers\TestGpsController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\GeofenceController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// GPS Data Reception Routes (No authentication required for devices)
Route::post('/gps/data', [GpsDataController::class, 'receiveData'])->name('gps.receive');
Route::get('/gps/data', [GpsDataController::class, 'receiveData'])->name('gps.receive.get');
Route::any('/gps/{deviceId}', [GpsDataController::class, 'receiveData'])->name('gps.device.receive');

// Test routes removed for production
// Route::any('/gps/test', [TestGpsController::class, 'receiveTestData'])->name('gps.test');
// Route::get('/gps/health', [TestGpsController::class, 'healthCheck'])->name('gps.health');
// Route::get('/gps/test-data', [TestGpsController::class, 'getTestData'])->name('gps.test.data');

// Simple debug route removed
// Route::get('/debug/test', function() { ... });

// High-Traffic GPS Routes (for production with high load)
Route::post('/gps/high-traffic', [HighTrafficGpsController::class, 'receiveData'])->name('gps.high.traffic');
Route::get('/api/devices/cached-locations', [HighTrafficGpsController::class, 'getDeviceLocationsFromCache'])->name('api.devices.cached');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // GPS Tracking routes
    Route::get('/tracking/live', [TrackingController::class, 'liveTracking'])->name('tracking.live');
    Route::get('/tracking/live-data', [TrackingController::class, 'liveData'])->name('tracking.live-data'); // AJAX Endpoint
    Route::get('/tracking/reports', [TrackingController::class, 'reports'])->name('tracking.reports');
    Route::get('/tracking/history', [TrackingController::class, 'history'])->name('tracking.history');
    Route::get('/tracking/vehicle-details/{device}', [TrackingController::class, 'vehicleDetails'])->name('tracking.vehicle-details');
    
    // Device setup and testing
    Route::get('/device-setup', function () {
        return view('device-setup');
    })->name('device.setup');
    
    // High-traffic dashboard
    Route::get('/high-traffic-dashboard', function () {
        $stats = [
            'total_devices' => \App\Models\Device::count(),
            'online_devices' => \App\Models\Device::where('status', 'active')->count(),
            'moving_devices' => \App\Models\Device::where('is_moving', true)->count(),
            'positions_today' => \App\Models\Position::whereDate('fix_time', today())->count(),
        ];
        return view('high-traffic-dashboard', compact('stats'));
    })->name('dashboard.high.traffic');
    
    // Device APIs
    Route::get('/api/devices/{deviceId}/history', [GpsDataController::class, 'getDeviceHistory'])->name('api.device.history');
    Route::get('/api/devices/locations', [GpsDataController::class, 'getDeviceLocations'])->name('api.devices.locations');
    
    // Real-time Server-Sent Events
    Route::get('/stream/gps', [RealTimeController::class, 'gpsStream'])->name('stream.gps');
    Route::get('/stream/dashboard', [RealTimeController::class, 'dashboardStream'])->name('stream.dashboard');
});

require __DIR__.'/auth.php';

// --------------------------------------------------------------------------
// Admin Panel Group (Unified Prefix)
// --------------------------------------------------------------------------
Route::middleware(['auth', 'role:admin|super_admin|vendor_admin'])->prefix('admin')->as('admin.')->group(function () {
    
    // --- Dashboard & Home ---
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // --- Master Management (Accessible by All Admins) ---
    Route::resource('zones', \App\Http\Controllers\Admin\ZoneController::class);
    Route::resource('circles', \App\Http\Controllers\Admin\CircleController::class);
    Route::resource('wards', \App\Http\Controllers\Admin\WardController::class);
    Route::resource('transfer-stations', \App\Http\Controllers\Admin\TransferStationController::class);
    Route::resource('landmarks', \App\Http\Controllers\Admin\LandmarkController::class);
    Route::resource('routes', \App\Http\Controllers\Admin\RouteController::class);

    // --- Fleet & GPS Management (Accessible by All Admins) ---
    Route::resource('devices', DeviceController::class);
    Route::resource('geofences', GeofenceController::class);
    Route::get('geofences/{geofence}/events', [GeofenceController::class, 'events'])->name('geofences.events');
    Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class);
    
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/daily-distance', [\App\Http\Controllers\Admin\ReportController::class, 'dailyDistance'])->name('daily-distance');
        Route::get('/trips', [\App\Http\Controllers\Admin\ReportController::class, 'trips'])->name('trips');
        Route::get('/geofences', [\App\Http\Controllers\Admin\ReportController::class, 'geofences'])->name('geofences');
        Route::get('/engine-utilization', [\App\Http\Controllers\Admin\ReportController::class, 'engineUtilization'])->name('engine-utilization');
    });
    
    Route::get('/ranking', [\App\Http\Controllers\Admin\RankingController::class, 'index'])->name('ranking.index');
    Route::get('/supervisor', [\App\Http\Controllers\Admin\SupervisorController::class, 'dashboard'])->name('supervisor.dashboard');
    
    Route::prefix('gps')->name('gps.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\GpsTrackingController::class, 'dashboard'])->name('dashboard');
        Route::get('/device/{device}/map', [App\Http\Controllers\Admin\GpsTrackingController::class, 'deviceMap'])->name('device-map');
        Route::get('/device/{device}/history', [App\Http\Controllers\Admin\GpsTrackingController::class, 'deviceHistory'])->name('device-history');
        Route::get('/live-data/{device?}', [App\Http\Controllers\Admin\GpsTrackingController::class, 'liveData'])->name('live-data');
        // Route::get('/add-test-data', [App\Http\Controllers\Admin\GpsTrackingController::class, 'addTestData'])->name('add-test-data');
    });

    // --- Restricted System Management (Super Admin & System Admin Only) ---
    Route::middleware('role:admin|super_admin')->group(function() {
        Route::resource('roles', AdminRoleController::class);
        Route::get('users/roles', [UserRoleController::class, 'index'])->name('users.roles.index');
        Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
        Route::resource('users', AdminUserController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});

// Super Admin Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->as('super_admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('vendors', \App\Http\Controllers\SuperAdmin\VendorController::class);
    
    // Global Settings
    Route::get('/settings', [\App\Http\Controllers\SuperAdmin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SuperAdmin\SettingController::class, 'update'])->name('settings.update');
});

// Vendor Routes
Route::middleware(['auth', 'role:vendor_admin|super_admin|admin'])->prefix('vendor')->as('vendor.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('dashboard');
    
    // Subscription Management
    Route::get('/subscription', [\App\Http\Controllers\Vendor\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/upgrade', [\App\Http\Controllers\Vendor\SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
    Route::post('/subscription/verify', [\App\Http\Controllers\Vendor\SubscriptionController::class, 'verify'])->name('subscription.verify');

    // Fuel Sensors
    Route::resource('fuel', \App\Http\Controllers\Vendor\FuelSensorController::class);

    // Dashcams
    Route::resource('dashcam', \App\Http\Controllers\Vendor\DashcamController::class);
});
