<?php

use Illuminate\Support\Facades\Route;
use Modules\Posts\Http\Controllers\Admin\AdsController;
use Modules\Posts\Http\Controllers\Admin\PostsController;
use Modules\Posts\Http\Controllers\Admin\ArticlesController;

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

Route::prefix('admin')->middleware(['auth:sanctum', 'verified_email', 'is_admin'])->group(function() {
    Route::resource('posts', PostsController::class)->names('admin.posts');
    Route::resource('ads', AdsController::class)->names('admin.ads');
    Route::resource('articles', ArticlesController::class)->names('admin.articles');
});
