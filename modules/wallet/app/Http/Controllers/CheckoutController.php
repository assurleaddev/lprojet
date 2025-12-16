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
            $vendor = $offer->product->vendor; // Or seller_id from offer?
            // Offer has seller_id, let's use that to be safe
            $vendor = $offer->seller;
        } else {
            $product = Product::find($request->product_id);
            $amount = $product->price; // Assuming product has price
            $vendor = $product->vendor;
        }

        // --- Fee Calculation ---
        $shippingCost = 5.00; // Fixed for now, or calculate based on product/location
        $buyerProtectionFee = ($amount * 0.05) + 0.70; // 5% + $0.70 fixed
        $totalAmount = $amount + $shippingCost + $buyerProtectionFee;

        if ($paymentMethod === 'wallet') {
            try {
                // Use Escrow Payment
                $this->walletService->payToEscrow($user, $vendor, $totalAmount, $amount, 'Order #' . time());
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        } elseif ($paymentMethod === 'card') {
            // Mock Card Payment
            // Charge user card $totalAmount... success

            // Credit Vendor Wallet Pending Balance
            $vendorWallet = $this->walletService->getWallet($vendor);
            $vendorWallet->pending_balance += $amount;
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
            'total_amount' => $totalAmount,
            'status' => 'processing', // Paid via wallet, so processing
            'delivery_receipt_path' => null, // or whatever
        ]);

        if ($paymentMethod === 'cod') {
            $order->status = 'pending_payment'; // Or whatever status for COD
            $order->save();
        }

        // --- Vinted-like Flow Updates ---

        // 1. Mark Product as Sold
        $product->update(['status' => 'sold']);

        // 2. Notify Vendor via Chat
        $chatService = app(\Modules\Chat\Services\ChatService::class);

        // Ensure conversation exists
        $conversation = $chatService->getOrCreateConversation($user, $vendor, $product);

        // Send "Item Sold" message with shipping label link
        $chatService->sendItemSoldMessage($conversation, $user, $order);

        // 3. Redirect to Thank You Page
        return redirect()->route('checkout.thank-you');
    }
}
