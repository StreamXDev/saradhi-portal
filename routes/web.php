<?php

use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::controller(SocialAuthController::class)->group(function(){
    Route::get('auth/google', 'googleRedirect')->name('auth.google');
    Route::get('auth/google/callback', 'googleCallback');
});

