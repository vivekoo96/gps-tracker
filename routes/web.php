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
    
    // Driver Behavior Monitoring
    Route::prefix('driver-behavior')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DriverBehaviorController::class, 'index'])->name('admin.driver-behavior.index');
        Route::get('/violations', [\App\Http\Controllers\Admin\DriverBehaviorController::class, 'violations'])->name('admin.driver-behavior.violations');
        Route::get('/violations/{id}', [\App\Http\Controllers\Admin\DriverBehaviorController::class, 'violationDetails'])->name('admin.driver-behavior.violations.show');
        Route::post('/violations/{id}/acknowledge', [\App\Http\Controllers\Admin\DriverBehaviorController::class, 'acknowledgeViolation'])->name('admin.driver-behavior.violations.acknowledge');
        Route::get('/leaderboard', [\App\Http\Controllers\Admin\DriverBehaviorController::class, 'leaderboard'])->name('admin.driver-behavior.leaderboard');
        Route::get('/drivers/{id}', [\App\Http\Controllers\Admin\DriverBehaviorController::class, 'driverProfile'])->name('admin.driver-behavior.driver-profile');
        Route::get('/alerts', [\App\Http\Controllers\Admin\DriverBehaviorController::class, 'alerts'])->name('admin.driver-behavior.alerts');
        Route::post('/alerts/{id}/read', [\App\Http\Controllers\Admin\DriverBehaviorController::class, 'markAlertRead'])->name('admin.driver-behavior.alerts.read');
    });

    // Fuel Management Routes
    Route::get('/fuel', [\App\Http\Controllers\Admin\FuelManagementController::class, 'index'])->name('admin.fuel.index');
    Route::get('/fuel/transactions', [\App\Http\Controllers\Admin\FuelManagementController::class, 'transactions'])->name('admin.fuel.transactions');
    Route::get('/fuel/transactions/{transaction}', [\App\Http\Controllers\Admin\FuelManagementController::class, 'show'])->name('admin.fuel.transactions.show');
    Route::post('/fuel/transactions/{transaction}/confirm', [\App\Http\Controllers\Admin\FuelManagementController::class, 'confirm'])->name('admin.fuel.transactions.confirm');
    Route::get('/fuel/efficiency', [\App\Http\Controllers\Admin\FuelManagementController::class, 'efficiency'])->name('admin.fuel.efficiency');
    Route::get('/fuel/alerts', [\App\Http\Controllers\Admin\FuelManagementController::class, 'alerts'])->name('admin.fuel.alerts');
    Route::get('/fuel/analytics', [\App\Http\Controllers\Admin\FuelManagementController::class, 'analytics'])->name('admin.fuel.analytics');

    // Maintenance Management Routes
    Route::get('/maintenance', [\App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('admin.maintenance.index');
    Route::get('/maintenance/schedules', [\App\Http\Controllers\Admin\MaintenanceController::class, 'schedules'])->name('admin.maintenance.schedules');
    Route::get('/maintenance/history', [\App\Http\Controllers\Admin\MaintenanceController::class, 'history'])->name('admin.maintenance.history');
    Route::get('/maintenance/parts', [\App\Http\Controllers\Admin\MaintenanceController::class, 'parts'])->name('admin.maintenance.parts');
    Route::get('/maintenance/reminders', [\App\Http\Controllers\Admin\MaintenanceController::class, 'reminders'])->name('admin.maintenance.reminders');
    // Developer Portal Routes
    Route::prefix('developer')->name('developer.')->group(function () {
        Route::get('/portal', [\App\Http\Controllers\Developer\PortalController::class, 'index'])->name('portal.index');
        Route::post('/portal/key', [\App\Http\Controllers\Developer\PortalController::class, 'generateKey'])->name('portal.generate-key');
        Route::delete('/portal/key/{id}', [\App\Http\Controllers\Developer\PortalController::class, 'revokeKey'])->name('portal.revoke-key');
        Route::post('/portal/webhook', [\App\Http\Controllers\Developer\PortalController::class, 'storeWebhook'])->name('portal.store-webhook');
        Route::get('/portal/webhook/{id}/logs', [\App\Http\Controllers\Developer\PortalController::class, 'webhookLogs'])->name('portal.webhook-logs');
        Route::get('/portal/docs', [\App\Http\Controllers\Developer\PortalController::class, 'documentation'])->name('portal.docs');
        Route::get('/portal/sdk/download/{type}', [\App\Http\Controllers\Developer\PortalController::class, 'downloadSdk'])->name('portal.sdk-download');
    });
});

// Admin Protocol Logs Routes (with admin prefix)
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/protocol-logs', [\App\Http\Controllers\Admin\ProtocolLogsController::class, 'index'])->name('admin.protocol-logs.index');
    Route::get('/protocol-logs/{id}', [\App\Http\Controllers\Admin\ProtocolLogsController::class, 'show'])->name('admin.protocol-logs.show');
    Route::delete('/protocol-logs/{id}', [\App\Http\Controllers\Admin\ProtocolLogsController::class, 'destroy'])->name('admin.protocol-logs.destroy');
    Route::post('/protocol-logs/clear', [\App\Http\Controllers\Admin\ProtocolLogsController::class, 'clear'])->name('admin.protocol-logs.clear');
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
    
    // Device Commands (Cut-off/Restore)
    Route::post('devices/{device}/cut-off', [\App\Http\Controllers\Admin\DeviceCommandController::class, 'cutOff'])->name('devices.cut-off');
    Route::post('devices/{device}/restore', [\App\Http\Controllers\Admin\DeviceCommandController::class, 'restore'])->name('devices.restore');
    Route::get('devices/{device}/commands', [\App\Http\Controllers\Admin\DeviceCommandController::class, 'history'])->name('devices.commands');
    
    Route::resource('geofences', GeofenceController::class);
    Route::get('geofences/{geofence}/events', [GeofenceController::class, 'events'])->name('geofences.events');
    
    // SOS Alerts
    Route::get('sos-alerts', [\App\Http\Controllers\Admin\SosAlertController::class, 'index'])->name('sos-alerts.index');
    Route::post('sos-alerts/{id}/acknowledge', [\App\Http\Controllers\Admin\SosAlertController::class, 'acknowledge'])->name('sos-alerts.acknowledge');
    Route::post('sos-alerts/{id}/resolve', [\App\Http\Controllers\Admin\SosAlertController::class, 'resolve'])->name('sos-alerts.resolve');
    Route::post('sos-alerts/{id}/false-alarm', [\App\Http\Controllers\Admin\SosAlertController::class, 'falseAlarm'])->name('sos-alerts.false-alarm');
    Route::post('sos-alerts/{id}/resend', [\App\Http\Controllers\Admin\SosAlertController::class, 'resendNotifications'])->name('sos-alerts.resend');
    
    // Emergency Contacts
    Route::get('devices/{device}/emergency-contacts', [\App\Http\Controllers\Admin\EmergencyContactController::class, 'index'])->name('devices.emergency-contacts.index');
    Route::post('devices/{device}/emergency-contacts', [\App\Http\Controllers\Admin\EmergencyContactController::class, 'store'])->name('devices.emergency-contacts.store');
    Route::put('devices/{device}/emergency-contacts/{contact}', [\App\Http\Controllers\Admin\EmergencyContactController::class, 'update'])->name('devices.emergency-contacts.update');
    Route::delete('devices/{device}/emergency-contacts/{contact}', [\App\Http\Controllers\Admin\EmergencyContactController::class, 'destroy'])->name('devices.emergency-contacts.destroy');
    
    Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class);
    
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/builder', [\App\Http\Controllers\Admin\ReportController::class, 'builder'])->name('builder');
        Route::post('/generate', [\App\Http\Controllers\Admin\ReportController::class, 'generate'])->name('generate');
        Route::post('/templates', [\App\Http\Controllers\Admin\ReportController::class, 'store'])->name('templates.store');
        Route::delete('/templates/{id}', [\App\Http\Controllers\Admin\ReportController::class, 'destroy'])->name('templates.destroy');
        Route::post('/templates/{id}/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('export');
        Route::get('/generated/{id}', [\App\Http\Controllers\Admin\ReportController::class, 'view'])->name('view');
        Route::get('/generated/{id}/download', [\App\Http\Controllers\Admin\ReportController::class, 'download'])->name('download');
        Route::get('/route-replay', [\App\Http\Controllers\Admin\RouteReplayController::class, 'index'])->name('route-replay');
        Route::post('/route-replay/data', [\App\Http\Controllers\Admin\RouteReplayController::class, 'getRouteData'])->name('route-replay.data');
    });
    
    // Current Status Dashboard
    Route::prefix('status')->name('status.')->group(function () {
        Route::get('/current', [\App\Http\Controllers\Admin\CurrentStatusController::class, 'index'])->name('current');
        Route::post('/data', [\App\Http\Controllers\Admin\CurrentStatusController::class, 'getStatusData'])->name('data');
    });
    
    // Live Vehicle View
    Route::prefix('live-vehicle')->name('live-vehicle.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LiveVehicleController::class, 'index'])->name('index');
        Route::post('/info', [\App\Http\Controllers\Admin\LiveVehicleController::class, 'getVehicleInfo'])->name('info');
        Route::post('/path', [\App\Http\Controllers\Admin\LiveVehicleController::class, 'getVehiclePath'])->name('path');
    });
    
    // Fleet Dashboard
    Route::prefix('fleet-dashboard')->name('fleet-dashboard.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FleetDashboardController::class, 'index'])->name('index');
        Route::post('/data', [\App\Http\Controllers\Admin\FleetDashboardController::class, 'getFleetData'])->name('data');
        Route::get('/vehicle/{id}', [\App\Http\Controllers\Admin\FleetDashboardController::class, 'getVehicleLocation'])->name('vehicle');
    });
    
    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\UserManagementController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\UserManagementController::class, 'show'])->name('show');
        Route::put('/{id}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])->name('destroy');
        Route::get('/vehicle-assignments/manage', [\App\Http\Controllers\Admin\UserManagementController::class, 'vehicleAssignments'])->name('vehicle-assignments');
        Route::put('/vehicle-assignment/{id}', [\App\Http\Controllers\Admin\UserManagementController::class, 'updateVehicleAssignment'])->name('vehicle-assignment.update');
    });
    
    // Ticket Management
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('index');
        Route::get('/analytics', [\App\Http\Controllers\Admin\TicketController::class, 'analytics'])->name('analytics');
        Route::get('/{id}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('show');
        Route::put('/{id}/status', [\App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/comment', [\App\Http\Controllers\Admin\TicketController::class, 'addComment'])->name('add-comment');
        Route::post('/{id}/close', [\App\Http\Controllers\Admin\TicketController::class, 'close'])->name('close');
    });
    
    // Supervisor's Citizen Complaint View
    Route::prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/citizen-complaints', [\App\Http\Controllers\Admin\SupervisorViewController::class, 'index'])->name('citizen-complaints');
        Route::post('/search-location', [\App\Http\Controllers\Admin\SupervisorViewController::class, 'searchLocation'])->name('search-location');
        Route::get('/collection-details/{id}', [\App\Http\Controllers\Admin\SupervisorViewController::class, 'getCollectionDetails'])->name('collection-details');
        Route::get('/locations-by-area', [\App\Http\Controllers\Admin\SupervisorViewController::class, 'getLocationsByArea'])->name('locations-by-area');
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
