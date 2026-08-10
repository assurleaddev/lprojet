<?php

namespace Modules\Wallet\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingOption;
use App\Models\User;
use Modules\Chat\Models\Offer;
use Modules\Chat\Services\ChatService;
use RuntimeException;

/**
 * Shared checkout logic for both the web checkout and the mobile API.
 *
 * Resolves the offer/product, calculates fees, moves funds into escrow,
 * creates the order + items, marks products sold and notifies the parties via
 * chat. Business failures throw RuntimeException so each caller can present
 * them appropriately (web redirect vs JSON response).
 */
class CheckoutService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly ChatService $chatService,
    ) {
    }

    /**
     * @param  array  $data  payment_method, offer_id|product_id, shipping_option_id?, address_id?, wants_verification?
     */
    public function checkout(User $buyer, array $data): Order
    {
        $paymentMethod = $data['payment_method'] ?? 'wallet';

        $products = collect();
        $amount = 0;
        $vendor = null;
        $offer = null;
        $parcelSize = null;

        if (! empty($data['offer_id'])) {
            $offer = Offer::with(['items.product', 'product', 'seller'])->find($data['offer_id']);
            $amount = $offer->offer_price;
            $vendor = $offer->seller;
            $parcelSize = $offer->parcel_size;

            $offerItems = collect($offer->items);
            if ($offerItems->isNotEmpty()) {
                $products = $offerItems->pluck('product');
            } elseif ($offer->product) {
                $products->push($offer->product);
            }
        } else {
            $product = Product::with('vendor')->find($data['product_id']);
            $products->push($product);
            $amount = $product->price;
            $vendor = $product->vendor;
        }

        // Ensure all products are still available.
        foreach ($products as $p) {
            if (! $p || in_array($p->status, ['sold', 'pending'])) {
                throw new RuntimeException('Sorry, one or more items are no longer available.');
            }
        }

        $amount = (float) $amount;

        // --- Fee calculation ---
        $shippingCost = (float) config('settings.delivery_fee_fixed', 25.00);

        if (! empty($data['shipping_option_id'])) {
            $shippingOption = ShippingOption::find($data['shipping_option_id']);
            if ($shippingOption && $shippingOption->is_active) {
                $vendorPref = $vendor->getMeta($shippingOption->key, '1');
                if ($vendorPref !== '0') {
                    $shippingCost = (float) $shippingOption->price;
                } else {
                    throw new RuntimeException('This shipping option is not supported by the seller.');
                }
            } else {
                throw new RuntimeException('Invalid shipping option.');
            }
        }

        $buyerProtectionPercentage = (float) config('settings.buyer_protection_fee_percentage', 5);
        $buyerProtectionFixed = (float) config('settings.buyer_protection_fee_fixed', 0.70);
        $platformCommissionPercentage = (float) config('settings.platform_commission_percentage', 0);

        $buyerProtectionFee = ($amount * ($buyerProtectionPercentage / 100)) + $buyerProtectionFixed;
        $platformCommission = $amount * ($platformCommissionPercentage / 100);

        $wantsVerification = (bool) ($data['wants_verification'] ?? false);
        $verificationFee = $wantsVerification
            ? (float) config('settings.product_verification_fee', 50)
            : 0.0;

        $totalAmount = $amount + $shippingCost + $buyerProtectionFee + $verificationFee;
        $platformRevenue = $buyerProtectionFee + $platformCommission + $verificationFee;
        $vendorPayout = $amount - $platformCommission;

        if ($paymentMethod === 'wallet') {
            $this->walletService->payToEscrow($buyer, $vendor, $totalAmount, $vendorPayout, 'Order #'.time(), $platformRevenue);
        } elseif ($paymentMethod === 'card') {
            $vendorWallet = $this->walletService->getWallet($vendor);
            $vendorWallet->pending_balance += $vendorPayout;
            $vendorWallet->save();
        }

        $order = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $products->count() === 1 ? $products->first()->id : null,
            'vendor_id' => $vendor->id,
            'amount' => $amount,
            'shipping_cost' => $shippingCost,
            'buyer_protection_fee' => $buyerProtectionFee,
            'platform_commission' => $platformCommission,
            'total_amount' => $totalAmount,
            'status' => $paymentMethod === 'cod' ? 'pending' : 'processing',
            'parcel_size' => $parcelSize,
            'delivery_receipt_path' => null,
            'payment_method' => $paymentMethod,
            'address_id' => $data['address_id'] ?? null,
            'shipping_option_id' => $data['shipping_option_id'] ?? null,
            'offer_id' => $offer?->id,
            'wants_verification' => $wantsVerification,
            'verification_fee' => $verificationFee,
            'source' => $offer ? 'offer' : 'direct',
        ]);

        foreach ($products as $p) {
            $order->items()->create([
                'product_id' => $p->id,
                'price' => $products->count() === 1 ? $amount : $p->price,
            ]);
            $p->update(['status' => 'sold']);
        }

        // Notify the vendor via chat (item sold + order placed).
        $mainProduct = $products->first();
        $conversation = $this->chatService->getOrCreateConversation($buyer, $vendor, $mainProduct);
        $this->chatService->sendItemSoldMessage($conversation, $buyer, $order, $offer?->id);
        $this->chatService->sendOrderPlacedMessage($conversation, $buyer, $order, $offer?->id);

        return $order;
    }
}
