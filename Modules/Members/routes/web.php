<?php

use Illuminate\Support\Facades\Route;
use Modules\Members\Http\Controllers\Admin\MemberController;
use Modules\Members\Http\Controllers\Admin\MembershipController;
use Modules\Members\Http\Controllers\MembersController;
use Modules\Members\Http\Middleware\VerifyProfileStatus;

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

Route::prefix('member')->group(function () {
    Route::controller(MembersController::class)->group(function () {
        Route::get('register', 'create');
        Route::post('register', 'store')->name('member.register');
        Route::get('verify_email_otp', 'createEmailOtpForm')->name('member.verify_email_otp');
        Route::post('verify_email_otp', 'verifyEmailOtp')->name('member.verify_email_otp');
        Route::post('resend_email_otp', 'resendEmailOtp')->name('member.resend_email_otp');
    });
});


Route::middleware(['auth:sanctum','verified_email'])->prefix('member')->group(function () {
    Route::controller(MembersController::class)->group(function(){
        Route::get('detail', 'createDetails')->name('member.detail');
        Route::post('detail', 'storeDetails')->name('member.detail');
        Route::get('profile/pending', 'profilePending')->name('member.profile.pending');

        Route::middleware(VerifyProfileStatus::class)->group(function () {
            Route::get('/', 'showProfile')->name('member.profile');
            Route::get('profile', 'showProfile')->name('member.profile');
        });
    });
});

Route::prefix('admin/members')->middleware(['auth:sanctum', 'verified_email'])->group(function() {
    Route::controller(MembershipController::class)->group(function(){
        Route::get('/requests', 'requestsForVerification');
    });
    Route::controller(MemberController::class)->group(function(){
        Route::get('/member/view/{id}', 'show');
        Route::get('/member/pdf/{id}', 'generatePDF');
    });
});