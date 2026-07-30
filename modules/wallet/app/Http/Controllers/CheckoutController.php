<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Wallet\Services\WalletService;
use App\Models\Product;
use App\Models\Order;
use Modules\Chat\Models\Offer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Wallet\Exceptions\InsufficientFundsException;

class CheckoutController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:wallet,card,cod',
            'product_id' => 'required_without:offer_id|exists:products,id',
            'offer_id' => 'nullable|exists:chat_offers,id',
        ]);

        $user = Auth::user();
        $paymentMethod = $request->payment_method;

        $products = collect();
        $amount = 0;
        $vendor = null;
        $offer = null;
        $parcelSize = null;

        if ($request->offer_id) {
            $offer = Offer::with(['items.product', 'product', 'seller'])->find($request->offer_id);
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
            $product = Product::with('vendor')->find($request->product_id);
            $products->push($product);
            $amount = $product->price;
            $vendor = $product->vendor;
        }

        // --- Safeguard: Ensure all products are still available ---
        foreach ($products as $p) {
            if (! $p || in_array($p->status, ['sold', 'pending'])) {
                return back()->with('error', "Sorry, one or more items are no longer available.");
            }
        }

        $amount = (float) $amount;

        // --- Fee Calculation ---
        $shippingCost = (float) config('settings.delivery_fee_fixed', 25.00);

        // If a valid shipping option is provided, use its price
        if ($request->has('shipping_option_id')) {
            $shippingOption = \App\Models\ShippingOption::find($request->shipping_option_id);
            if ($shippingOption && $shippingOption->is_active) {
                // Check if vendor has enabled this option
                $vendorPref = $vendor->getMeta($shippingOption->key, '1');

                if ($vendorPref !== '0') {
                    $shippingCost = (float) $shippingOption->price;
                } else {
                    return back()->with('error', 'This shipping option is not supported by the seller.');
                }
            } else {
                return back()->with('error', 'Invalid shipping option.');
            }
        }

        $buyerProtectionPercentage = (float) config('settings.buyer_protection_fee_percentage', 5);
        $buyerProtectionFixed = (float) config('settings.buyer_protection_fee_fixed', 0.70);
        $platformCommissionPercentage = (float) config('settings.platform_commission_percentage', 0);

        $buyerProtectionFee = ($amount * ($buyerProtectionPercentage / 100)) + $buyerProtectionFixed;
        $platformCommission = $amount * ($platformCommissionPercentage / 100);

        $wantsVerification = $request->boolean('wants_verification');
        $verificationFee = $wantsVerification
            ? (float) config('settings.product_verification_fee', 50)
            : 0.0;

        $totalAmount = $amount + $shippingCost + $buyerProtectionFee + $verificationFee;
        $platformRevenue = $buyerProtectionFee + $platformCommission + $verificationFee;

        // Vendor Payout = Item Price - Commission
        $vendorPayout = $amount - $platformCommission;

        // Money movement, order creation and stock updates must succeed or fail together,
        // otherwise a buyer can be debited for an order that was never created.
        try {
            $order = DB::transaction(function () use (
                $user,
                $vendor,
                $products,
                $offer,
                $request,
                $paymentMethod,
                $amount,
                $shippingCost,
                $buyerProtectionFee,
                $platformCommission,
                $totalAmount,
                $platformRevenue,
                $vendorPayout,
                $parcelSize,
                $wantsVerification,
                $verificationFee
            ) {
                if ($paymentMethod === 'wallet') {
                    $this->walletService->payToEscrow($user, $vendor, $totalAmount, $vendorPayout, 'Order #' . time(), $platformRevenue);
                } elseif ($paymentMethod === 'card') {
                    $vendorWallet = $this->walletService->getWallet($vendor);
                    $vendorWallet->pending_balance += $vendorPayout;
                    $vendorWallet->save();
                }

                // Create Order
                $order = Order::create([
                    'user_id' => $user->id,
                    'product_id' => $products->count() === 1 ? $products->first()->id : null,
                    'vendor_id' => $vendor->id,
                    'amount' => $amount, // Item price
                    'shipping_cost' => $shippingCost,
                    'buyer_protection_fee' => $buyerProtectionFee,
                    'platform_commission' => $platformCommission,
                    'total_amount' => $totalAmount,
                    'status' => $paymentMethod === 'cod' ? 'pending' : 'processing',
                    'parcel_size' => $parcelSize,
                    'delivery_receipt_path' => null,
                    'payment_method' => $paymentMethod,
                    'address_id' => $request->address_id,
                    'shipping_option_id' => $request->shipping_option_id ?? null,
                    'offer_id' => $offer?->id,
                    'wants_verification' => $wantsVerification,
                    'verification_fee' => $verificationFee,
                    'source' => $offer ? 'offer' : 'direct',
                ]);

                // Create Order Items
                foreach ($products as $p) {
                    $order->items()->create([
                        'product_id' => $p->id,
                        'price' => $products->count() === 1 ? $amount : $p->price, // For bundles, we use product price (total maps to offer)
                    ]);

                    // Mark product as sold
                    $p->update(['status' => 'sold']);
                }

                return $order;
            });
        } catch (InsufficientFundsException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Checkout failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'payment_method' => $paymentMethod,
                'offer_id' => $offer?->id,
                'exception' => $e,
            ]);

            return back()->with('error', 'We could not complete your order. No payment was taken, please try again.');
        }

        // 1. Notify Vendor via Chat. The order is already paid for at this point, so a
        // messaging failure must not fail the checkout.
        try {
            $chatService = app(\Modules\Chat\Services\ChatService::class);

            // Ensure conversation exists (use the first product for conversation context if bundle)
            $mainProduct = $products->first();
            $conversation = $chatService->getOrCreateConversation($user, $vendor, $mainProduct);

            // Send "Item Sold" message
            $chatService->sendItemSoldMessage($conversation, $user, $order, $offer?->id);

            // Send "Order Placed" message
            $chatService->sendOrderPlacedMessage($conversation, $user, $order, $offer?->id);
        } catch (\Throwable $e) {
            Log::error("Checkout chat notifications failed for Order #{$order->id}: " . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e,
            ]);
        }

        // 3. Redirect to Thank You Page
        return redirect()->route('checkout.thank-you');
    }
}
