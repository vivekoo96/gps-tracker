<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth.v2'])->group(function () {
    // Test endpoint
    Route::get('/me', function () {
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => auth()->user(),
                'api_key_id' => request()->attributes->get('api_key_id')
            ]
        ]);
    });

    // Devices
    Route::get('/devices', [\App\Http\Controllers\Api\V2\DeviceV2Controller::class, 'index']);
    Route::get('/devices/{id}', [\App\Http\Controllers\Api\V2\DeviceV2Controller::class, 'show']);

    // GPS Data
    Route::get('/gps-data/latest', [\App\Http\Controllers\Api\V2\GpsDataV2Controller::class, 'latest']);
    Route::get('/gps-data/{deviceId}/history', [\App\Http\Controllers\Api\V2\GpsDataV2Controller::class, 'history']);
    Route::post('/gps-data', [\App\Http\Controllers\Api\V2\GpsDataV2Controller::class, 'store']);

    // Alerts
    Route::get('/alerts', [\App\Http\Controllers\Api\V2\AlertV2Controller::class, 'index']);
    Route::patch('/alerts/{id}/acknowledge', [\App\Http\Controllers\Api\V2\AlertV2Controller::class, 'acknowledge']);
});
