<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('social_login', 'socialLogin');
    Route::post('send_otp', 'sendOtp');
    Route::post('verify_otp', 'verifyOtp');
});

Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::get('/test', function (){
        
        $user = Auth::user();

        return $user;
    });
});