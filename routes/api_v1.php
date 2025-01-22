<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\Auth\AuthorizationController;
use App\Http\Controllers\Api\v1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\v1\ConversationController;
use App\Http\Controllers\Api\v1\GeneralController;
use App\Http\Controllers\Api\v1\MessageController;
use App\Http\Controllers\Api\v1\PostController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\PortfolioController;
use App\Http\Controllers\Api\v1\CertificationController;
use App\Http\Controllers\Api\v1\ExperienceController;
use App\Http\Controllers\Api\v1\EducationController;
use App\Http\Controllers\Api\v1\JobController;
use App\Http\Controllers\Api\v1\CommunityController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});
Route::controller(ForgotPasswordController::class)->group(function () {
    Route::post('password/email', 'sendResetCodeEmail');
    Route::post('password/verify-code', 'verifyCode');
    Route::post('password/reset', 'reset');
});
Route::controller(GeneralController::class)->group(function () {
    Route::get('categories', 'categories');
});

Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::post('logout', AuthController::class . '@logout');
    //authorization
    Route::controller(AuthorizationController::class)->group(function () {
        Route::get('authorization', 'authorization');
        Route::get('resend-verify/{type}', 'sendVerifyCode');
        Route::post('verify-email', 'emailVerification');
        Route::post('verify-mobile', 'mobileVerification');
    });
    Route::apiResource('user', UserController::class);
    Route::apiResource('portfolio', PortfolioController::class);
    Route::apiResource('certification', CertificationController::class);
    Route::apiResource('experience', ExperienceController::class);
    Route::apiResource('education', EducationController::class);
    Route::apiResource('job', JobController::class);
    //community
    Route::prefix('community')->group(function () {
        Route::apiResource('/', CommunityController::class);
        Route::apiResource('post', PostController::class);
        Route::get('member/{community}', CommunityController::class . '@member');
        Route::post('join', CommunityController::class . '@joinCommunity');
    });
    Route::controller(JobController::class)->group(function () {
        Route::post('job-application', 'jobApplication');
        Route::get('job-application/{id}', 'viewJobApplication');
    });
    //conversation
    Route::apiResource('conversation', ConversationController::class);
    Route::prefix('conversation')->group(function () {
        Route::post('message', MessageController::class . '@sendMessage');
    });
    Route::controller(MessageController::class)->group(function () {
        Route::post('message', 'createMessage');
        Route::get('message', 'viewMessage');
    });
});

