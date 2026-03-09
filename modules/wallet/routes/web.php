<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallet\Http\Controllers\WalletController;
use Modules\Wallet\Http\Controllers\CheckoutController;
use Modules\Wallet\Http\Controllers\ShippingLabelController;

Route::middleware(['auth'])->group(function () {
    Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('wallet/withdraw', [WalletController::class, 'requestWithdrawal'])->name('wallet.withdraw');
    Route::post('/checkout/process', [CheckoutController::class, 'processPayment'])->name('checkout.process');
    Route::get('/checkout/thank-you', function () {
        return view('wallet::thank-you');
    })->name('checkout.thank-you');
    Route::get('/shipping-label/{order}', [ShippingLabelController::class, 'download'])->name('shipping-label.download');

    Route::post('/order/{order}/shipped', [Modules\Wallet\Http\Controllers\OrderLifecycleController::class, 'markAsShipped'])->name('order.shipped');
    Route::post('/order/{order}/received', [Modules\Wallet\Http\Controllers\OrderLifecycleController::class, 'received'])->name('order.received');

    // Admin Routes
    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('withdrawals', [Modules\Wallet\Http\Controllers\AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('withdrawals/{withdrawal}/approve', [Modules\Wallet\Http\Controllers\AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('withdrawals/{withdrawal}/reject', [Modules\Wallet\Http\Controllers\AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
    });
});
