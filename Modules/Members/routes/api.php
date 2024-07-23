<?php

use Illuminate\Support\Facades\Route;
use Modules\Members\Http\Controllers\Api\MembersController;
use Modules\Members\Http\Middleware\VerifyProfileStatus;

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

Route::middleware(['auth:sanctum','verified_email'])->prefix('member')->group(function () {
    Route::controller(MembersController::class)->group(function(){
        Route::get('details', 'createDetails');
        Route::post('details', 'storeDetails');

        Route::middleware(VerifyProfileStatus::class)->group(function () {
            Route::get('profile', 'showProfile');
        });
    });
    
});
