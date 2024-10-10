<?php

use Illuminate\Support\Facades\Route;
use Modules\Members\Http\Controllers\Api\AddressController;
use Modules\Members\Http\Controllers\Api\DependentController;
use Modules\Members\Http\Controllers\Api\MembersController;
use Modules\Members\Http\Controllers\Api\ProfileController;
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
        Route::get('profile', 'showProfile');
        Route::get('details', 'createDetails');
        Route::post('details', 'storeDetails');
    });
    Route::controller(AddressController::class)->group(function(){
        Route::middleware(VerifyProfileStatus::class)->group(function () {
            Route::get('add_address', 'createMemberAddress');
            Route::post('add_address', 'storeMemberAddress');
        });
    });
    Route::controller(DependentController::class)->group(function(){
        Route::middleware(VerifyProfileStatus::class)->group(function () {
            Route::get('add_dependent', 'showProfile');
        });
    });
    Route::controller(ProfileController::class)->group(function(){
        Route::middleware(VerifyProfileStatus::class)->group(function () {
            Route::get('profile', 'showProfile');
        });
    });
});
