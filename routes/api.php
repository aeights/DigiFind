<?php

use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Content\AboutUsController;
use App\Http\Controllers\API\Content\ContactUsController;
use App\Http\Controllers\API\Content\OnboardingController;
use App\Http\Controllers\API\Home\HomeController;
use App\Http\Controllers\API\Home\ProfileController;
use App\Http\Controllers\API\Location\LocationController;
use App\Http\Controllers\API\LostReport\LostReportController;
use App\Http\Controllers\API\LostReport\MyReportController as MyLostReportController;
use App\Http\Controllers\API\PublicReport\PublicReportController;
use App\Http\Controllers\API\Transaction\LostReportTransactionController;
use App\Http\Controllers\API\User\UserController;
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
        Route::prefix('profile')->group(function () {
            Route::get('/', 'profile')->name('api.profile');
            Route::post('upload-photo', 'uploadPhoto')->name('api.profile.upload-photo');
            Route::post('update', 'updateProfile')->name('api.profile.update');
            Route::post('change-password', 'changePassword')->name('api.profile.change-password');
        });
        Route::get('logout', 'logout')->name('api.logout');
    });

    Route::controller(UserController::class)->group(function () {
        Route::prefix('user')->group(function () {
            Route::post('report','report')->name('api.user.report');
        });
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
            Route::get('comments/{id}','getComments')->name('api.public-report.comments');
            Route::post('report-comment','reportComment')->name('api.public-report.report-comment');
            Route::post('report','report')->name('api.public-report.report');

            Route::get('categories','categories')->name('api.public-report.categories');
            Route::get('related-report/{id}','relatedReport')->name('api.public-report.related-report');
        });
        Route::get('user/public-report','userReports')->name('api.user.public-report');
        Route::get('user/public-report/saved','userSavedReports')->name('api.user.public-report.saved');
    });

    Route::controller(LostReportController::class)->group(function () {
        Route::prefix('lost-report')->group(function () {
            Route::get('/','index')->name('api.lost-report');
            Route::get('read/{id}','show')->name('api.lost-report');
            Route::post('store','store')->name('api.lost-report.store');
            Route::post('update/{id}','update')->name('api.lost-report.update');
            Route::get('delete/{id}','delete')->name('api.lost-report.delete');
            Route::get('save/{id}','save')->name('api.lost-report.save');
            Route::post('report','report')->name('api.lost-report.report');
            Route::get('search','search')->name('api.lost-report.search');
            Route::get('related-report/{id}','relatedReport')->name('api.lost-report.related-report');

            Route::get('report-summary/{id}', 'reportSummary')->name('api.lost-report.summary');
            Route::get('publication-package', 'publicationPackage')->name('api.lost-report.package');

            Route::get('categories','categories')->name('api.lost-report.categories');
        });
    });

    Route::controller(MyLostReportController::class)->group(function () {
        Route::prefix('lost-report')->group(function () {
            Route::get('status/{id}','getByStatus')->name('api.lost-report.profile');
            Route::get('saved-report','savedReports')->name('api.lost-report.saved-report');
        });
    });

    Route::controller(LocationController::class)->group(function () {
        Route::prefix('location')->group(function () {
            Route::get('provinces','provinces')->name('api.location.provinces');
            Route::get('cities/{id}','cities')->name('api.location.cities');
            Route::get('districts/{id}','districts')->name('api.location.districts');
            Route::get('villages/{id}','villages')->name('api.location.villages');
        });
    });

    Route::controller(HomeController::class)->group(function () {
        Route::prefix('home')->group(function () {
            Route::get('trend-public-report','trendPublicReport')->name('api.home.trend-public-report');
        });
    });

    Route::controller(LostReportTransactionController::class)->group(function () {
        Route::prefix('transaction/lost-report')->group(function () {
            Route::post('create','createLostTransaction')->name('api.transaction.lost-report.create');
        });
    });
});