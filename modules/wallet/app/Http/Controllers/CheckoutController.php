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

        $product = null;
        $amount = 0;
        $vendor = null;
        $offer = null;

        if ($request->offer_id) {
            $offer = Offer::find($request->offer_id);
            $product = $offer->product;
            $amount = $offer->offer_price;
            $vendor = $offer->seller;
        } else {
            $product = Product::find($request->product_id);
            $amount = $product->price;
            $vendor = $product->vendor;
        }

        // --- Safeguard: Ensure product is still available ---
        if (in_array($product->status, ['sold', 'pending'])) {
            return back()->with('error', 'Sorry, this item is no longer available for purchase.');
        }

        // --- Fee Calculation ---
        $shippingCost = config('settings.delivery_fee_fixed', 5.00);

        // If a valid shipping option is provided, use its price
        if ($request->has('shipping_option_id')) {
            $shippingOption = \App\Models\ShippingOption::find($request->shipping_option_id);
            if ($shippingOption && $shippingOption->is_active) {
                // Check if vendor has enabled this option
                // Default to true ('1') if not set
                $vendorPref = $vendor->getMeta($shippingOption->key, '1');

                if ($vendorPref !== '0') {
                    $shippingCost = $shippingOption->price;
                } else {
                    // Option disabled by vendor
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
                // Use Escrow Payment
                // Pay to Escrow: User pays Total, Vendor gets Pending Payout
                $this->walletService->payToEscrow($user, $vendor, $totalAmount, $vendorPayout, 'Order #' . time());
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        } elseif ($paymentMethod === 'card') {
            // Mock Card Payment
            // Charge user card $totalAmount... success

            // Credit Vendor Wallet Pending Balance
            $vendorWallet = $this->walletService->getWallet($vendor);
            $vendorWallet->pending_balance += $vendorPayout;
            $vendorWallet->save();
        } elseif ($paymentMethod === 'cod') {
            // Cash on Delivery
            // No wallet changes yet
        }

        // Create Order
        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'amount' => $amount, // Item price
            'shipping_cost' => $shippingCost,
            'buyer_protection_fee' => $buyerProtectionFee,
            'platform_commission' => $platformCommission,
            'total_amount' => $totalAmount,
            'status' => 'processing', // Paid via wallet, so processing
            'delivery_receipt_path' => null,
            'payment_method' => $paymentMethod,
            'address_id' => $request->address_id,
            'shipping_option_id' => $request->shipping_option_id ?? null,
        ]);

        if ($paymentMethod === 'cod') {
            $order->status = 'pending'; // COD orders start as pending
            $order->save();
        }


        // --- Vinted-like Flow Updates ---

        // Product status is now automatically set to 'sold' by OrderObserver

        // 1. Notify Vendor via Chat
        $chatService = app(\Modules\Chat\Services\ChatService::class);

        // Ensure conversation exists
        $conversation = $chatService->getOrCreateConversation($user, $vendor, $product);

        // Send "Item Sold" message with shipping label link (to Seller)
        $chatService->sendItemSoldMessage($conversation, $user, $order);

        // Send "Order Placed" message with deadline (to Buyer)
        $chatService->sendOrderPlacedMessage($conversation, $user, $order);

        // 3. Redirect to Thank You Page
        return redirect()->route('checkout.thank-you');
    }
}
