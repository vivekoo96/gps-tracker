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

// Test GPS Routes (simplified for debugging)
Route::any('/gps/test', [TestGpsController::class, 'receiveTestData'])->name('gps.test');
Route::get('/gps/health', [TestGpsController::class, 'healthCheck'])->name('gps.health');
Route::get('/gps/test-data', [TestGpsController::class, 'getTestData'])->name('gps.test.data');

// Simple debug route
Route::get('/debug/test', function() {
    return response()->json([
        'status' => 'working',
        'message' => 'Debug endpoint is working',
        'timestamp' => now()->toISOString(),
        'routes_working' => true
    ]);
})->name('debug.test');

// High-Traffic GPS Routes (for production with high load)
Route::post('/gps/high-traffic', [HighTrafficGpsController::class, 'receiveData'])->name('gps.high.traffic');
Route::get('/api/devices/cached-locations', [HighTrafficGpsController::class, 'getDeviceLocationsFromCache'])->name('api.devices.cached');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // GPS Tracking routes
    Route::get('/tracking/live', [TrackingController::class, 'liveTracking'])->name('tracking.live');
    Route::get('/tracking/reports', [TrackingController::class, 'reports'])->name('tracking.reports');
    Route::get('/tracking/history', [TrackingController::class, 'history'])->name('tracking.history');
    
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

// Admin panel routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // Role management

    // Assign roles to users
    Route::get('users/roles', [UserRoleController::class, 'index'])->name('users.roles.index');
    Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');

    // Device management routes
    Route::resource('devices', DeviceController::class);
    
    // GPS Tracking routes
    Route::prefix('gps')->name('gps.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\GpsTrackingController::class, 'dashboard'])->name('dashboard');
        Route::get('/device/{device}/map', [App\Http\Controllers\Admin\GpsTrackingController::class, 'deviceMap'])->name('device-map');
        Route::get('/device/{device}/history', [App\Http\Controllers\Admin\GpsTrackingController::class, 'deviceHistory'])->name('device-history');
        Route::get('/live-data/{device?}', [App\Http\Controllers\Admin\GpsTrackingController::class, 'liveData'])->name('live-data');
        Route::get('/add-test-data', [App\Http\Controllers\Admin\GpsTrackingController::class, 'addTestData'])->name('add-test-data');
    });

    // Users management (Tailwind-based)
    Route::resource('users', AdminUserController::class)->only(['index', 'store', 'update', 'destroy']);
});
