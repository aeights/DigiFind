<?php

use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Content\AboutUsController;
use App\Http\Controllers\API\Content\ContactUsController;
use App\Http\Controllers\API\Content\OnboardingController;
use App\Http\Controllers\API\Home\ProfileController;
use App\Http\Controllers\API\PublicReport\PublicReportController;
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

// Auth endpoints
Route::controller(LoginController::class)->group(function () {
    Route::post('login', 'login')->name('api.login');
    Route::get('new-token', 'generateNewToken')->name('api.new-token');
});

Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register')->name('api.register');
});

Route::controller(ForgotPasswordController::class)->group(function () {
    Route::post('send-otp', 'sendOtp')->name('api.send-otp');
    Route::post('verif-otp', 'verifOtp')->name('api.verif-otp');
    Route::post('reset-password', 'resetPassword')->name('api.reset-password');
});

// Content endpoints
Route::controller(OnboardingController::class)->group(function () {
    Route::get('content/onboarding','index')->name('api.onboarding');
});

Route::controller(ContactUsController::class)->group(function () {
    Route::get('content/contact-us','index')->name('api.contact-us');
});

Route::controller(AboutUsController::class)->group(function () {
    Route::get('content/about-us','index')->name('api.about-us');
});

// Home endpoints
Route::middleware(['api.auth'])->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'profile')->name('api.profile');
        Route::post('profile/update', 'updateProfile')->name('api.profile.update');
        Route::post('profile/change-password', 'changePassword')->name('api.profile.change-password');
        Route::get('logout', 'logout')->name('api.logout');
    });

    Route::controller(PublicReportController::class)->group(function () {
        Route::prefix('public-report')->group(function () {
            Route::get('/','index')->name('api.public-report');
            Route::get('read/{id}','show')->name('api.public-report');
            Route::post('store','store')->name('api.public-report.store');
            Route::post('update/{id}','update')->name('api.public-report.update');
            Route::get('delete/{id}','delete')->name('api.public-report.delete');
            
            Route::get('search','search')->name('api.public-report.search');
            Route::get('save/{id}','save')->name('api.public-report.save');
            Route::post('comment','comment')->name('api.public-report.comment');
            Route::post('report','report')->name('api.public-report.report');
        });
        Route::get('user/public-report','userReports')->name('api.user.public-report');
        Route::get('user/public-report/saved','userSavedReports')->name('api.user.public-report.saved');
    });
});