<?php

use App\Http\Controllers\Api\v1\AccountDetailController;
use App\Http\Controllers\Api\v1\DepositController;
use App\Http\Controllers\Api\v1\GigController;
use App\Http\Controllers\Api\v1\MilestoneController;
use App\Http\Controllers\Api\v1\PaystackController;
use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\Auth\AuthorizationController;
use App\Http\Controllers\Api\v1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\v1\ConversationController;
use App\Http\Controllers\Api\v1\FinanceController;
use App\Http\Controllers\Api\v1\GeneralController;
use App\Http\Controllers\Api\v1\MessageController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\PostController;
use App\Http\Controllers\Api\v1\ReviewController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\PortfolioController;
use App\Http\Controllers\Api\v1\CertificationController;
use App\Http\Controllers\Api\v1\ExperienceController;
use App\Http\Controllers\Api\v1\EducationController;
use App\Http\Controllers\Api\v1\JobController;
use App\Http\Controllers\Api\v1\CommunityController;
use App\Http\Controllers\Api\v1\WishlistController;
use App\Http\Controllers\Api\v1\WithdrawController;
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
Route::post('verify-email', [AuthorizationController::class, 'emailVerification']);
Route::post('verify-mobile', [AuthorizationController::class, 'mobileVerification']);

//authorization
Route::post('resend-verify/{type}', [AuthorizationController::class, 'sendVerifyCode']);

Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::get('authorization', [AuthorizationController::class, 'authorization']);
    Route::post('logout', AuthController::class . '@logout');
    //User Account
    Route::apiResource('user', UserController::class);
    Route::post('change-password', AuthController::class . '@changePassword');
    Route::post('change-email', AuthController::class . '@changeEmail');
    Route::controller(NotificationController::class)->group(function () {
        Route::get('notifications', 'index');
        Route::post('notifications/read/{id}', 'read');
        Route::post('notifications/all', 'readAll');
    });
    Route::apiResource('portfolio', PortfolioController::class);
    Route::apiResource('certification', CertificationController::class);
    Route::apiResource('experience', ExperienceController::class);
    Route::apiResource('education', EducationController::class);
    Route::post('/user-switch', [UserController::class, 'switch']);

    //Jobs
    Route::apiResource('job', JobController::class);
    Route::prefix('jobs')->group(function () {
        Route::controller(JobController::class)->group(function () {
            Route::get('/', 'allJobs');
        });
    });

    //community
    Route::prefix('community')->group(function () {
        Route::apiResource('', CommunityController::class)->names([
            'index' => 'community.index',  // Avoid conflict
            'store' => 'community.store',
            'show' => 'community.show',
            'update' => 'community.update',
            'destroy' => 'community.destroy',
        ]);
        //post
        Route::apiResource('post', PostController::class);

        Route::get('member/{community}', [CommunityController::class, 'member'])->name('community.member');
        Route::post('join', [CommunityController::class, 'joinCommunity'])->name('community.join');
        Route::get('leave/{id}', [CommunityController::class, 'leaveCommunity'])->name('community.leave');
        Route::get('all', [CommunityController::class, 'viewAllCommunities'])->name('community.all');
        Route::get('suggested', [CommunityController::class, 'suggestedCommunities'])->name('community.suggested');
        Route::get('joined', [CommunityController::class, 'joinedCommunities'])->name('community.joined');
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

    //Generals
    Route::controller(GeneralController::class)->group(function () {
        Route::get('talents', 'talents');
        Route::get('posts', 'posts');
        Route::post('posts/{postId}/like', 'toggleLike');
        Route::post('posts/comments', 'postComment');
    });

    //Finance
    Route::controller(AccountDetailController::class)->group(function () {
        Route::get('bank-account', 'show');
        Route::post('add-account', 'store');
        Route::put('update-account', 'update');
        Route::delete('delete-account', 'destroy');
    });
    //
    Route::controller(WithdrawController::class)->group(function () {
        Route::get('withdraw', 'index');
        Route::post('withdraw', 'store');
    });
    //Review
    Route::apiResource('reviews', ReviewController::class);
    //Wishlist
    Route::apiResource('wishlists', WishlistController::class);
    //Deposit
    Route::post('deposit', [DepositController::class, 'initiate']);
    Route::get('transactions', [DepositController::class, 'transactions']);
    Route::post('buy-griftis', [DepositController::class, 'buyGriftis']);

    //Gigs
    Route::apiResource('gigs', GigController::class);

    //Job Milestone
    Route::apiResource('milestones', MilestoneController::class);
    // Talent
    Route::prefix('talent')->group(function () {
            Route::get('milestone/{milestoneId}', [MilestoneController::class, 'markAsCompleteByTalent']);
    });
    // Client
    Route::prefix('client')->group(function () {
            Route::get('milestone/{milestoneId}', [MilestoneController::class, 'markAsCompleteByClient']);
    });
});
Route::get('payment/verify/{gateway}', [DepositController::class, 'verify']);

