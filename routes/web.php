<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // GPS Tracking routes
    Route::get('/tracking/live', [TrackingController::class, 'liveTracking'])->name('tracking.live');
    Route::get('/tracking/reports', [TrackingController::class, 'reports'])->name('tracking.reports');
    Route::get('/tracking/history', [TrackingController::class, 'history'])->name('tracking.history');
});

require __DIR__.'/auth.php';

// Admin panel routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // Role management
    Route::resource('roles', AdminRoleController::class)->except(['show']);

    // Assign roles to users
    Route::get('users/roles', [UserRoleController::class, 'index'])->name('users.roles.index');
    Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');

    // Device management
    Route::resource('devices', DeviceController::class)->except(['show']);

    // Users management (Tailwind-based)
    Route::resource('users', AdminUserController::class)->only(['index', 'store', 'update', 'destroy']);
});
