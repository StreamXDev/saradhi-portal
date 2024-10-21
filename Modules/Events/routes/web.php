<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\Admin\Events\EventController;

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

Route::prefix('admin/events')->middleware(['auth:sanctum', 'verified_email', 'is_admin'])->group(function() {
    Route::controller(EventController::class)->group(function(){
        Route::get('/', 'index');
        Route::get('/create', 'create')->name('admin.events.create');
    });
});