<?php

use Illuminate\Support\Facades\Route;
use Modules\PushNotification\Http\Controllers\Backend\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {

});

Route::prefix('admin/push-notification')->middleware(['auth:sanctum', 'verified_email', 'is_admin'])->group(function() {
    Route::controller(NotificationController::class)->group(function(){
        Route::get('/', 'index');
        Route::post('/send', 'storeAndSend')->name('admin.pushnotification.send');
    });
});
