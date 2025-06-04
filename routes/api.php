<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\BookmarkReadController;

Route::post('/signupOrLogin', [UserController::class, 'signupOrLogin']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/verify-otp', [UserController::class, 'verifyOtp']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'list']);
        Route::get('/news-list-mobile', [NewsController::class, 'index']);
        Route::get('bookmark', [BookmarkReadController::class, 'toggleBookmark']);
Route::get('read', [BookmarkReadController::class, 'logRead']);


});
