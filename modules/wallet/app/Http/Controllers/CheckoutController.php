<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Wallet\Services\WalletService;
use App\Models\Product;
use App\Models\Order;
use Modules\Chat\Models\Offer;
use Modules\Chat\Enums\OfferStatus;
use Illuminate\Support\Facades\Auth;

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
            if (!$p || in_array($p->status, ['sold', 'pending'])) {
                return back()->with('error', "Sorry, one or more items are no longer available.");
            }
        }

        // --- Fee Calculation ---
        $shippingCost = config('settings.delivery_fee_fixed', 5.00);

        // If a valid shipping option is provided, use its price
        if ($request->has('shipping_option_id')) {
            $shippingOption = \App\Models\ShippingOption::find($request->shipping_option_id);
            if ($shippingOption && $shippingOption->is_active) {
                // Check if vendor has enabled this option
                $vendorPref = $vendor->getMeta($shippingOption->key, '1');

                if ($vendorPref !== '0') {
                    $shippingCost = $shippingOption->price;
                } else {
                    return back()->with('error', 'This shipping option is not supported by the seller.');
                }
            } else {
                return back()->with('error', 'Invalid shipping option.');
            }
        }

        $buyerProtectionPercentage = config('settings.buyer_protection_fee_percentage', 5);
        $buyerProtectionFixed = config('settings.buyer_protection_fee_fixed', 0.70);
        $platformCommissionPercentage = config('settings.platform_commission_percentage', 0);

        $buyerProtectionFee = ($amount * ($buyerProtectionPercentage / 100)) + $buyerProtectionFixed;
        $platformCommission = $amount * ($platformCommissionPercentage / 100);

        $totalAmount = $amount + $shippingCost + $buyerProtectionFee;

        // Vendor Payout = Item Price - Commission
        $vendorPayout = $amount - $platformCommission;

        if ($paymentMethod === 'wallet') {
            try {
                $this->walletService->payToEscrow($user, $vendor, $totalAmount, $vendorPayout, 'Order #' . time());
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
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
            'status' => 'processing',
            'parcel_size' => $parcelSize,
            'delivery_receipt_path' => null,
            'payment_method' => $paymentMethod,
            'address_id' => $request->address_id,
            'shipping_option_id' => $request->shipping_option_id ?? null,
        ]);

        if ($paymentMethod === 'cod') {
            $order->status = 'pending';
            $order->save();
        }

        // Create Order Items
        foreach ($products as $p) {
            $order->items()->create([
                'product_id' => $p->id,
                'price' => $products->count() === 1 ? $amount : $p->price, // For bundles, we use product price (total maps to offer)
            ]);

            // Mark product as sold
            $p->update(['status' => 'sold']);
        }

        // 1. Notify Vendor via Chat
        $chatService = app(\Modules\Chat\Services\ChatService::class);

        // Ensure conversation exists (use the first product for conversation context if bundle)
        $mainProduct = $products->first();
        $conversation = $chatService->getOrCreateConversation($user, $vendor, $mainProduct);

        // Send "Item Sold" message
        $chatService->sendItemSoldMessage($conversation, $user, $order, $offer?->id);

        // Send "Order Placed" message
        $chatService->sendOrderPlacedMessage($conversation, $user, $order, $offer?->id);

        // 3. Redirect to Thank You Page
        return redirect()->route('checkout.thank-you');
    }
}
