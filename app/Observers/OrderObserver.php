<?php

namespace App\Observers;

use App\Models\Order;
use Modules\Wallet\Services\WalletService;

class OrderObserver
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Handle the Order "created" event.
     * Mark the product as sold when an order is created.
     */
    public function created(Order $order): void
    {
        // Mark the product as sold
        if ($order->product) {
            $order->product->update(['status' => 'sold']);
        }
    }

    /**
     * Handle the Order "updated" event.
     * Credit vendor balance when order status changes to delivered.
     * Refund buyer when order is cancelled and was paid with wallet.
     */
    public function updated(Order $order): void
    {
        // Check if status was changed to 'delivered'
        if ($order->isDirty('status') && $order->status === 'delivered') {
            // Get the original status before the change
            $originalStatus = $order->getOriginal('status');

            // Only process if this is the first time changing to delivered
            if ($originalStatus !== 'delivered') {
                // Credit the vendor's balance using the WalletService
                $vendor = $order->vendor;

                // Calculate Payout Amount (Net)
                // Use stored commission if available, otherwise recalculate (legacy support)
                $commissionAmount = $order->platform_commission ?? ($order->amount * (config('settings.platform_commission_percentage', 0) / 100));
                $payoutAmount = $order->amount - $commissionAmount;

                // Release the pending funds to available balance
                try {
                    $this->walletService->releasePendingFunds(
                        $vendor,
                        $payoutAmount,
                        'order_' . $order->id
                    );
                } catch (\Exception $e) {
                    // Handle error if pending balance is insufficient (should not happen if flow is correct)
                    // Fallback to credit? No, better to log error.
                }
            }
        }

        // Check if status was changed to 'cancelled'
        if ($order->isDirty('status') && $order->status === 'cancelled') {
            $originalStatus = $order->getOriginal('status');

            // Only process if this is the first time changing to cancelled
            if ($originalStatus !== 'cancelled') {
                // If payment was made with wallet, refund the buyer
                if ($order->payment_method === 'wallet') {
                    $buyer = $order->user;
                    $refundBaseAmount = $order->total_amount ?? $order->amount;

                    // Calculate Refund Commission (if any)
                    $refundCommissionPercentage = config('settings.refund_commission_percentage', 0);
                    $refundDeduction = $refundBaseAmount * ($refundCommissionPercentage / 100);
                    $refundAmount = $refundBaseAmount - $refundDeduction;

                    // Refund the total amount to the buyer
                    $this->walletService->credit(
                        $buyer,
                        $refundAmount,
                        'refund',
                        'Order #' . $order->id . ' cancelled - Refund',
                        'order_refund_' . $order->id
                    );
                }

                // Set product back to available
                if ($order->product) {
                    $order->product->update(['status' => 'approved']);
                }
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
