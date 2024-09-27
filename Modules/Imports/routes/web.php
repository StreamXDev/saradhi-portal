<?php

use Illuminate\Support\Facades\Route;
use Modules\Imports\Http\Controllers\ImportMemberController;

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

Route::prefix('admin/import')->middleware(['auth:sanctum', 'verified_email', 'is_admin'])->group(function() {
    Route::controller(ImportMemberController::class)->group(function(){
        Route::get('/', 'import')->name('admin.import');
    });
});
