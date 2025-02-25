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

    //User Management
    Route::get('/users', [ManageUserController::class, 'index']); // List users
    Route::post('/users/{user}/suspend', [ManageUserController::class, 'suspend']); // Suspend user
    Route::post('/users/{user}/send-email', [ManageUserController::class, 'sendEmail']); // Send email
});
