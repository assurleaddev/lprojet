<?php

namespace App\Services;

use App\Events\AuctionClosed;
use App\Models\Live;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Chat\Services\ChatService;
use Modules\Wallet\Services\WalletService;

/**
 * Domain logic for the live-auction lifecycle, shared by the web
 * (App\Http\Controllers\LiveController) and the mobile API
 * (App\Http\Controllers\Api\Mobile\LiveController) so the money-handling path
 * can never diverge between the two clients.
 */
class LiveService
{
    /**
     * Close the currently active auction: settle the winner's payment into
     * escrow, create the order, mark the product sold, and notify both parties.
     * Broadcasts AuctionClosed and resets the live back to idle. Returns the
     * winning bidder (null when the auction had no bids).
     */
    public function closeAuction(Live $live): ?User
    {
        $winner = $live->currentBidder;
        $product = $live->product;

        if ($product && $winner) {
            DB::transaction(function () use ($live, $winner, $product) {
                $bidAmount = (float) $live->current_bid;

                // --- Fee calculation (mirrors CheckoutController) ---
                $shippingCost = (float) config('settings.delivery_fee_fixed', 25.00);
                $buyerProtectionPct = (float) config('settings.buyer_protection_fee_percentage', 5);
                $buyerProtectionFixed = (float) config('settings.buyer_protection_fee_fixed', 0.70);
                $platformCommissionPct = (float) config('settings.platform_commission_percentage', 0);

                $buyerProtectionFee = ($bidAmount * ($buyerProtectionPct / 100)) + $buyerProtectionFixed;
                $platformCommission = $bidAmount * ($platformCommissionPct / 100);
                $totalAmount = $bidAmount + $shippingCost + $buyerProtectionFee;
                $platformRevenue = $buyerProtectionFee + $platformCommission;
                $vendorPayout = $bidAmount - $platformCommission;

                // --- Wallet: move funds to escrow ---
                app(WalletService::class)->payToEscrow(
                    $winner,
                    $product->vendor,
                    $totalAmount,
                    $vendorPayout,
                    "Live auction #{$live->id}",
                    $platformRevenue
                );

                // --- Create Order ---
                $winnerAddress = $winner->addresses()->first();

                $order = Order::create([
                    'user_id' => $winner->id,
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'amount' => $bidAmount,
                    'shipping_cost' => $shippingCost,
                    'buyer_protection_fee' => $buyerProtectionFee,
                    'platform_commission' => $platformCommission,
                    'total_amount' => $totalAmount,
                    'status' => 'processing',
                    'payment_method' => 'wallet',
                    'address_id' => $winnerAddress?->id,
                    'wants_verification' => false,
                    'verification_fee' => 0,
                    'source' => 'live',
                ]);

                // --- Order item + mark product sold ---
                $order->items()->create([
                    'product_id' => $product->id,
                    'price' => $bidAmount,
                ]);

                $product->update(['status' => 'sold']);

                // --- Chat: notify both parties ---
                $chatService = app(ChatService::class);
                $conversation = $chatService->getOrCreateConversation($winner, $product->vendor, $product);

                $chatService->sendItemSoldMessage($conversation, $winner, $order);
                $chatService->sendOrderPlacedMessage($conversation, $winner, $order);
            });
        }

        $live->update(['auction_status' => 'idle']);

        broadcast(new AuctionClosed($live));

        return $winner;
    }
}
