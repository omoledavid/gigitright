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
use App\Http\Controllers\Api\v1\SupportTicketController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\PortfolioController;
use App\Http\Controllers\Api\v1\CertificationController;
use App\Http\Controllers\Api\v1\ExperienceController;
use App\Http\Controllers\Api\v1\EducationController;
use App\Http\Controllers\Api\v1\JobController;
use App\Http\Controllers\Api\v1\CommunityController;
use App\Http\Controllers\Api\v1\PusherController;
use App\Http\Controllers\Api\v1\WishlistController;
use App\Http\Controllers\Api\v1\WithdrawController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ClientJobInviteController;
use App\Http\Controllers\ClientOrderController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\GigPlanController;
use App\Http\Controllers\ManageClientController;
use App\Http\Controllers\ManageTalentController;
use App\Http\Controllers\TalentJobController;
use App\Http\Controllers\TalentOrderController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::get('/pusher-credentials', function () {
    return response()->json([
        'PUSHER_APP_KEY' => env('PUSHER_APP_KEY'),
        'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER'),
    ]);
});

Route::controller(ForgotPasswordController::class)->group(function () {
    Route::post('password/email', 'sendResetCodeEmail');
    Route::post('password/verify-code', 'verifyCode');
    Route::post('password/reset', 'reset');
});
Route::prefix('generals')->group(function () {
    Route::controller(GeneralController::class)->group(function () {
        Route::get('banks', 'banks');
        Route::post('verify-account-number', 'verifyAccountNumber');
        Route::get('categories', 'categories');
        Route::get('rates', 'rates');
        Route::get('site-info', 'siteInfo');
    });
});
Route::post('verify-email', [AuthorizationController::class, 'emailVerification']);
Route::post('verify-mobile', [AuthorizationController::class, 'mobileVerification']);

//authorization
Route::post('resend-verify/{type}', [AuthorizationController::class, 'sendVerifyCode']);

Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
    Route::get('authorization', [AuthorizationController::class, 'authorization']);
    Route::post('logout', AuthController::class . '@logout');
    //User Account
    Route::apiResource('bank', BankController::class);
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
    Route::get('community/suggested', [CommunityController::class, 'suggestedCommunities']);
    Route::apiResource('community', CommunityController::class);
    Route::prefix('community')->group(function () {
        //post
        Route::apiResource('post', PostController::class);

        Route::get('member/{community}', [CommunityController::class, 'member']);
        Route::post('join', [CommunityController::class, 'joinCommunity']);
        Route::get('leave/{id}', [CommunityController::class, 'leaveCommunity']);
        Route::get('all', [CommunityController::class, 'viewAllCommunities']);
        Route::get('joined', [CommunityController::class, 'joinedCommunities']);
    });


    Route::controller(JobController::class)->group(function () {
        Route::post('job-application', 'jobApplication');
        Route::get('job-application/{id}', 'viewJobApplication');
    });

    //conversation
    Route::apiResource('conversation', ConversationController::class);
    Route::prefix('conversation')->group(function () {
        Route::post('message', MessageController::class . '@sendMessage');
        Route::post('message/{id}/read', MessageController::class . '@readMessage');
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

    //Support tickets
    Route::prefix('support-tickets')->group(function () {
        Route::name('support-tickets.')->group(function () {
            Route::get('/', [SupportTicketController::class, 'index']);
            Route::post('/', [SupportTicketController::class, 'store']);
            Route::get('/{id}', [SupportTicketController::class, 'show']);
            Route::post('/{id}/message', [SupportTicketController::class, 'addMessage']);
            Route::post('/{id}/close', [SupportTicketController::class, 'close']);
        });
    });

    //Job Milestone
    Route::apiResource('milestones', MilestoneController::class);

    // Talent
    Route::prefix('talent')->group(function () {
        Route::name('talent.')->group(function () {
            Route::get('milestone/{milestoneId}', [MilestoneController::class, 'markAsCompleteByTalent']);
            Route::controller(ManageTalentController::class)->group(function () {
                Route::get('job-invite', 'jobInvites');
                Route::get('job-invite/{id}', 'viewJobInvite');
                Route::post('accept-invite', 'acceptInvite');
                Route::post('reject-invite', 'rejectInvite');
            });
            Route::apiResource('coupon', CouponController::class);
            Route::apiResource('gig-plan', GigPlanController::class);
            Route::get('orders', [TalentOrderController::class, 'orders']);
            Route::get('orders/{id}', [TalentOrderController::class, 'viewOrder']);
            Route::post('accept-order/{id}', [TalentOrderController::class, 'acceptOrder']);
            Route::post('reject-order/{id}', [TalentOrderController::class, 'rejectOrder']);
            Route::post('order-complete/{order}', [TalentOrderController::class, 'markAsComplete']);
            Route::get('on-going-job', [TalentJobController::class, 'onGoingJobs']);
            Route::get('on-going-job/{id}', [TalentJobController::class, 'viewOnGoingJob']);
        });
    });

    // Client
    Route::prefix('client')->group(function () {
        Route::name('client.')->group(function () {
            Route::post('apply-coupon', [CouponController::class, 'applyCoupon']);
            Route::controller(ManageClientController::class)->group(function () {
                Route::get('applications', 'jobApplications');
                Route::get('applications/{id}', 'viewApplication');
            });
            Route::get('milestone/{milestoneId}', [MilestoneController::class, 'markAsCompleteByClient']);
            Route::apiResource('job-invite', ClientJobInviteController::class);
            //checkout
            Route::post('checkout', CheckoutController::class);
            Route::get('orders', [ClientOrderController::class, 'orders']);
            Route::get('orders/{id}', [ClientOrderController::class, 'viewOrder']);
            Route::post('order-complete/{order}', [ClientOrderController::class, 'markAsComplete']);
        });
    });
    Route::get('testing', [GeneralController::class, 'testing']);
});
Route::get('payment/verify/{gateway}', [DepositController::class, 'verify']);
Broadcast::routes();
Route::post('/pusher/auth', [PusherController::class, 'authenticate'])->middleware('auth:sanctum');

Route::get('/debug-session', function () {
    dd([
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'config' => config('session'),
    ]);
});
