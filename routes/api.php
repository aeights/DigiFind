<?php

use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Home\ProfileController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(LoginController::class)->group(function () {
    Route::post('login', 'login')->name('api.login');
    Route::get('new-token', 'generateNewToken')->name('api.new-token');
});

Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register')->name('api.register');
});

Route::controller(ForgotPasswordController::class)->group(function () {
    Route::post('send-otp', 'sendOtp')->name('api.send-otp');
    Route::post('reset-password', 'resetPassword')->name('api.reset-password');
});

Route::controller(ProfileController::class)->group(function () {
    Route::middleware(['api.auth'])->group(function () {
        Route::get('profile', 'profile')->name('api.profile');
        Route::get('logout', 'logout')->name('api.logout');
    });
});