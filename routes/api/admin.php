<?php

use App\Http\Controllers\Api\v1\Admin\ManageUserController;
use App\Http\Controllers\Api\v1\Admin\PaymentMethodController;
use App\Http\Controllers\Api\v1\Admin\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->name('admin')->group(function () {
    Route::controller(ManageUserController::class)->group(function () {
        Route::get('dashboard', 'index')->name('index');
    });

    //Payment method
    Route::get('payment-methods-settings', [PaymentMethodController::class, 'index']);
    Route::post('payment-methods-settings', [PaymentMethodController::class, 'store']);
    Route::put('payment-methods-settings/{id}', [PaymentMethodController::class, 'update']);

    //Wallet Management
    Route::get('wallet', [WalletController::class, 'index']);
    Route::get('wallet/transaction/{id}', [WalletController::class, 'show']);
    Route::post('wallet/refund/{id}', [WalletController::class, 'refund']);
    Route::post('wallet/set-rates', [WalletController::class, 'setRates']);
    Route::get('wallet/financial-metrics', [WalletController::class, 'getFinancialMetrics']);

    //User Management
    Route::controller(ManageUserController::class)->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', 'index')->name('users.index');
            Route::get('stats', 'stats')->name('users.stats');
            Route::post('revenue-chart', 'getRevenueChart')->name('users.revenue-chart');
            Route::get('/{user}/suspend', 'suspend')->name('users.suspend');
            Route::get('/{user}/send-email', 'sendEmail')->name('users.send-email');
        });
    });
});
