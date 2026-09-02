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
     * Mark the products as sold when an order is created.
     */
    public function created(Order $order): void
    {
        // Mark the products as sold
        if ($order->items()->exists()) {
            foreach ($order->items as $item) {
                $item->product->update(['status' => 'sold']);
            }
        } elseif ($order->product) {
            // Backward compatibility for single product orders
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
        // Check if status was changed to 'delivered' or 'completed'.
        // This observer is the SINGLE place seller payout happens — callers must
        // only change the order status and never credit/release wallets themselves
        // (doing both used to double-pay sellers).
        if ($order->isDirty('status') && in_array($order->status, ['delivered', 'completed'])) {
            $originalStatus = $order->getOriginal('status');

            // Only release if moving FROM a non-final status
            if (! in_array($originalStatus, ['delivered', 'completed'])) {
                try {
                    if ($order->payment_method === 'cod') {
                        // Cash was collected by the courier — nothing sits in escrow;
                        // credit the seller's available balance directly.
                        $this->walletService->credit($order->vendor, $order->payout_amount, 'sale', 'COD sale for Order #' . $order->id, 'order_' . $order->id);
                    } else {
                        // Wallet/card — move the escrowed payout from pending to available.
                        $this->walletService->releasePendingFunds($order->vendor, $order->payout_amount, 'Order #' . $order->id);
                    }
                } catch (\Exception $e) {
                    \Log::error("OrderObserver: Error releasing funds for Order #{$order->id}: " . $e->getMessage());
                }
            }
        }

        // Check if status was changed to 'cancelled'
        if ($order->isDirty('status') && $order->status === 'cancelled') {
            $originalStatus = $order->getOriginal('status');

            if ($originalStatus !== 'cancelled') {
                // Handle refunds via WalletService
                if ($order->payment_method === 'wallet') {
                    try {
                        $this->walletService->refundOrder($order);
                    } catch (\Exception $e) {
                        \Log::error("OrderObserver: Error refunding Order #{$order->id}: " . $e->getMessage());
                    }
                }

                // Set products back to available
                if ($order->items()->exists()) {
                    foreach ($order->items as $item) {
                        $item->product->update(['status' => 'approved', 'buyer_id' => null]);
                    }
                } elseif ($order->product) {
                    $order->product->update(['status' => 'approved', 'buyer_id' => null]);
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
