<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth.v2'])->group(function () {
    // Auth
    Route::post('/login', [\App\Http\Controllers\Api\V2\AuthV2Controller::class, 'login'])->withoutMiddleware(['auth.v2']);
    Route::get('/me', [\App\Http\Controllers\Api\V2\AuthV2Controller::class, 'me']);

    // Devices
    Route::get('/devices', [\App\Http\Controllers\Api\V2\DeviceV2Controller::class, 'index']);
    Route::get('/devices/{id}', [\App\Http\Controllers\Api\V2\DeviceV2Controller::class, 'show']);

    // GPS Data
    Route::get('/gps-data/latest', [\App\Http\Controllers\Api\V2\GpsDataV2Controller::class, 'latest']);
    Route::get('/gps-data/stream', [\App\Http\Controllers\Api\V2\GpsStreamV2Controller::class, 'stream']); // Real-time
    Route::get('/gps-data/{deviceId}/history', [\App\Http\Controllers\Api\V2\GpsDataV2Controller::class, 'history']);
    Route::post('/gps-data', [\App\Http\Controllers\Api\V2\GpsDataV2Controller::class, 'store']);

    // User Management (Admin only)
    Route::get('/users', [\App\Http\Controllers\Api\V2\UserV2Controller::class, 'index']);
    Route::get('/users/{id}', [\App\Http\Controllers\Api\V2\UserV2Controller::class, 'show']);
    Route::post('/users', [\App\Http\Controllers\Api\V2\UserV2Controller::class, 'store']);

    // Landmarks & Routes
    Route::get('/landmarks', [\App\Http\Controllers\Api\V2\LandmarkV2Controller::class, 'index']);
    Route::get('/landmarks/{id}', [\App\Http\Controllers\Api\V2\LandmarkV2Controller::class, 'show']);
    Route::get('/routes', [\App\Http\Controllers\Api\V2\RouteV2Controller::class, 'index']);
    Route::get('/routes/{id}', [\App\Http\Controllers\Api\V2\RouteV2Controller::class, 'show']);

    // Alerts
    Route::get('/alerts', [\App\Http\Controllers\Api\V2\AlertV2Controller::class, 'index']);
    Route::patch('/alerts/{id}/acknowledge', [\App\Http\Controllers\Api\V2\AlertV2Controller::class, 'acknowledge']);
});
