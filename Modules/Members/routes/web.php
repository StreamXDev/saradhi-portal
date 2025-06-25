<?php

use App\Http\Controllers\admin\DashboardController;
use Illuminate\Support\Facades\Route;
use Modules\Members\Http\Controllers\Admin\CommitteeController;
use Modules\Members\Http\Controllers\Admin\MemberController;
use Modules\Members\Http\Controllers\Admin\MembershipController;
use Modules\Members\Http\Controllers\Admin\TrusteeController;
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
        Route::post('verify_email_otp', 'verifyEmailOtp');
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


/**
 * Admin Routes
 */
Route::prefix('admin')->middleware(['auth:sanctum', 'verified_email', 'is_admin'])->group(function() {
    Route::controller(DashboardController::class)->group(function() {
        Route::get('/', 'index');
    });
});

Route::prefix('admin/members')->middleware(['auth:sanctum', 'verified_email', 'is_admin'])->group(function() {
    Route::controller(MembershipController::class)->group(function(){
        Route::get('/requests', 'requests');
        Route::post('/change_status', 'changeStatus')->name('admin.member.change_status');
        Route::post('/confirm_membership_request', 'confirmMembershipRequest')->name('admin.member.confirm_membership_request');
    });
    Route::controller(MemberController::class)->group(function(){
        Route::get('/', 'index');
        Route::get('/member/view/{id}/{prevPage?}', 'show');
        Route::get('/member/pdf/{id}', 'exportViewToPDF');
        Route::get('/member/excel/{id}', 'exportViewToExcel');
        Route::get('/member/edit/{id}', 'edit');
        Route::post('/member/update', 'update')->name('admin.member.update');
        Route::get('/member/create','create');
        Route::post('/member/create','store')->name('admin.member.create');
        Route::post('/member/merge', 'merge')->name('admin.member.merge');
        Route::post('/member/delete', 'deleteMember')->name('admin.member.delete');
        Route::get('/member/family/create/{id?}','createFamilyMember');
        Route::post('/member/family/create/','storeFamilyMember')->name('admin.member.family');
        Route::get('/member/family/edit/{id?}','editFamilyMember');
        Route::post('/member/family/update','updateFamilyMember')->name('admin.member.family.update');
        Route::post('/member/family/delete/{id?}','deleteFamilyMember')->name('admin.member.family.delete');
        Route::post('/member/notes/add', 'add_note')->name('admin.member.notes.add');
        Route::get('/member/notes/delete/{id?}', 'delete_note');

        //Route::get('/status/update', 'updateMemberStatus');
    });
});

Route::prefix('admin/committee')->middleware(['auth:sanctum', 'verified_email', 'is_admin'])->group(function() {
    Route::controller(CommitteeController::class)->group(function(){
        Route::get('/', 'index');
        Route::get('/show/{id}/{prevPage?}', 'show');
        Route::get('/create', 'create');
        Route::get('autocomplete', 'autocomplete')->name('admin.committee.autocomplete');
        Route::post('/create', 'store')->name('admin.committee.create');
        Route::get('/create_member/{id}', 'createCommitteeMember');
        Route::post('/create_member', 'storeCommitteeMember')->name('admin.committee.create.member');
        Route::get('/edit/{id}', 'edit');
        Route::post('/update', 'update')->name('admin.committee.update');
    });
});

Route::prefix('admin/trustees')->middleware(['auth:sanctum', 'verified_email', 'is_admin'])->group(function() {
    Route::controller(TrusteeController::class)->group(function(){
        Route::get('/', 'index');
        Route::get('/delete/{id?}', 'delete');
    });
});