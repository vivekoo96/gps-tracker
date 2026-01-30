<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GpsDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// GPS Data API Routes (for device communication and testing)
Route::post('/gps/store', [GpsDataController::class, 'store'])->name('api.gps.store');
Route::post('/gps/data', [GpsDataController::class, 'receiveData'])->name('api.gps.receive');
