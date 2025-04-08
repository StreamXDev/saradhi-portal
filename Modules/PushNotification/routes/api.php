<?php

use Illuminate\Support\Facades\Route;
use Modules\PushNotification\Http\Controllers\Api\DeviceApiController;
use Modules\PushNotification\Http\Controllers\PushNotificationController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware(['auth:sanctum','verified_email'])->prefix('notification')->group(function () {
    Route::controller(DeviceApiController::class)->group(function(){
        Route::put('device', 'storeDevice');
    });
});
