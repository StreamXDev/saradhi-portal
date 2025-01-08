<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FcmController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/linkstorage', function () {
    Artisan::call('storage:link') ;// this will do the command line job
});

Auth::routes(['verify' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/register', [App\Http\Controllers\HomeController::class, 'index'])->name('register'); // overriding default register link

Route::controller(SocialAuthController::class)->group(function(){
    Route::get('auth/google', 'googleRedirect')->name('auth.google');
    Route::get('auth/google/callback', 'googleCallback');
});


Route::group(['middleware' => ['auth', 'verified', 'is_admin']], function() {
    Route::prefix('/admin')->group(function() {
        Route::resource('/roles', RoleController::class);
        Route::resource('/users', UserController::class);
        Route::controller(DashboardController::class)->group(function(){
            Route::get('/' , 'index');
            Route::get('/dashboard' , 'index');
            Route::post('/result' , 'sargaResult')->name('admin.result');
        });
        Route::post('send_fcm_notification', [FcmController::class, 'sendFcmNotification']);
    });
});




