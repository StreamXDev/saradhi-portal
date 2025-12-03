<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\MemberTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FcmController;
use Modules\PushNotification\Http\Controllers\Api\DeviceApiController;

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
    //Route::put('device_token', [FcmController::class, 'updateDeviceToken']);
    /* 
    Route::put('device_token', function(){
        return redirect('/notification/store');
    });
    */
    Route::post('send_fcm_notification', [FcmController::class, 'sendFcmNotification']);
});

Route::middleware(['auth:sanctum','verified_email'])->group(function () {
    Route::controller(DeviceApiController::class)->group(function(){
        Route::put('device_token', 'storeDevice');
    });
});

Route::group(['middleware' => ['auth:sanctum', 'verified_email', 'is_admin']], function() {
    Route::controller(MemberTransfer::class)->prefix('transfer')->group(function(){
        Route::get('user/{id}', 'getUser');
        Route::get('users/{id}', 'getUsersAfterId');
        Route::get('member/{id}', 'getMember');
    });
});
