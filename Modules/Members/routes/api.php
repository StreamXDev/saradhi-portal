<?php

use Illuminate\Support\Facades\Route;
use Modules\Members\Http\Controllers\Api\AddressController;
use Modules\Members\Http\Controllers\Api\DependentController;
use Modules\Members\Http\Controllers\Api\MembersController;
use Modules\Members\Http\Controllers\Api\ProfileController;
use Modules\Members\Http\Controllers\Api\SearchController;
use Modules\Members\Http\Controllers\Api\UnitController;
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
        Route::post('proof', 'uploadProof');
        Route::get('units', 'getUnits');
    });
    Route::controller(AddressController::class)->prefix('address')->group(function(){
        Route::middleware(VerifyProfileStatus::class)->group(function () {
            Route::get('add', 'createMemberAddress');
            Route::post('add', 'storeMemberAddress');
        });
        Route::post('update', 'updateAddress');
    });
    Route::controller(ProfileController::class)->prefix('profile')->group(function(){
        Route::get('/', 'showProfile');
        Route::post('update', 'updateProfile');
        Route::get('get_dependent/{id?}', 'getDependent');
        Route::get('create_dependent', 'createDependent');
        Route::post('create_dependent', 'storeDependent');
        Route::post('update_dependent', 'updateDependent');
        Route::post('delete_dependent', 'deleteDependent');
    });
    Route::controller(SearchController::class)->prefix('search')->group(function(){
        Route::get('/', 'index');
        Route::post('/', 'search');
    });
    Route::controller(UnitController::class)->prefix('unit')->group(function(){
        Route::post('/change', 'cuRequest');
    });
});
