<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FcmController;

Auth::routes(['verify' => true]);

Route::prefix('auth')->group(function(){
    Route::controller(AuthController::class)->group(function(){
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::post('social_login', 'socialLogin');
        Route::post('resend_otp', 'resendOtp');
        Route::post('verify_otp', 'verifyOtp');
    });
});

Route::controller(EmailVerificationController::class)->group(function(){
    Route::get('email/verify/{id}', 'verify')->name('verificationapi.verify');
    Route::get('email/resent', 'resend')->name('verificationapi.resend');
});

Route::group(['middleware' => ['auth:sanctum', 'verified']], function() {
    Route::put('device_token', [FcmController::class, 'updateDeviceToken']);
    Route::post('send_fcm_notification', [FcmController::class, 'sendFcmNotification']);
});

