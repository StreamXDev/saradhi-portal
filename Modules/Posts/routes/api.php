<?php

use Illuminate\Support\Facades\Route;
use Modules\Posts\Http\Controllers\Api\AdsController;
use Modules\Posts\Http\Controllers\Api\ArticlesController;
use Modules\Posts\Http\Controllers\Api\PostsController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/
Route::controller(PostsController::class)->prefix('posts')->group(function(){
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
});
Route::controller(AdsController::class)->prefix('ads')->group(function(){
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
});
Route::controller(ArticlesController::class)->prefix('articles')->group(function(){
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
});