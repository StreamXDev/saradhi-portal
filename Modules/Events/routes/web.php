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
        Route::get('/past', 'pastEvents');
        Route::get('/view/{id?}', 'show')->name('admin.events.view');
        Route::get('/create', 'create')->name('admin.events.create');
        Route::get('autocomplete', 'autocomplete')->name('admin.events.autocomplete');
        Route::post('/create', 'store')->name('admin.events.create');
        Route::get('/{id?}/invitees', 'invitees')->name('admin.events.invitees');
        Route::get('/{id?}/invitee/add', 'createInvitee')->name('admin.events.invitee.add');
        Route::post('/invitee/add', 'storeInvitee')->name('admin.events.invitee.add');
        Route::get('/{id?}/volunteers', 'volunteers')->name('admin.events.volunteers');
        Route::get('/{id?}/volunteer/add', 'createVolunteer')->name('admin.events.volunteer.add');
        Route::post('/volunteer/add', 'storeVolunteer')->name('admin.events.volunteer.add');
    });
});