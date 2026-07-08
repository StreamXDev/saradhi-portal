<?php

use Illuminate\Support\Facades\Route;
use Modules\Imports\Http\Controllers\ImportsController;
use Modules\Imports\Http\Controllers\ReceiveMemberController;

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

Route::middleware(['auth:sanctum'])->prefix('imports')->group(function () {
    //Route::apiResource('imports', ImportsController::class)->names('imports');
});
Route::controller(ReceiveMemberController::class)->prefix('import')->group(function(){
    Route::post('user/create', 'createUser');
    Route::post('member/init', 'initMember');
});
