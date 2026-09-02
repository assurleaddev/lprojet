<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Chat\Enums\OfferStatus;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Models\Message;
use Modules\Chat\Models\Offer;
use Modules\Chat\Services\ChatService;
use Modules\Wallet\Services\CheckoutService;

class OfferOrderController extends Controller
{
    public function __construct(
        private readonly ChatService $chat,
        private readonly CheckoutService $checkoutService,
    ) {
    }

    /**
     * Buyer completes checkout for an accepted offer (or a direct product buy).
     * Reuses the shared CheckoutService that also backs the web checkout:
     * escrow payment, order creation, and item_sold / order_placed messages.
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'payment_method' => ['nullable', 'in:wallet,card,cod'],
            'offer_id' => ['required_without:product_id', 'nullable', 'exists:chat_offers,id'],
            'product_id' => ['required_without:offer_id', 'nullable', 'exists:products,id'],
            'shipping_option_id' => ['nullable', 'integer'],
            'address_id' => ['nullable', 'integer'],
            'wants_verification' => ['nullable', 'boolean'],
        ]);

        // Paying for an offer: the buyer must own it and it must be accepted.
        if (! empty($validated['offer_id'])) {
            $offer = Offer::findOrFail($validated['offer_id']);
            if ($offer->buyer_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            if ($offer->status !== OfferStatus::Accepted) {
                return response()->json(['message' => 'This offer must be accepted before checkout'], 422);
            }
        }

        // Direct "buy now": can't buy your own product; must be available.
        if (! empty($validated['product_id'])) {
            $product = Product::findOrFail($validated['product_id']);
            if ((int) $product->vendor_id === $user->id) {
                return response()->json(['message' => 'Vous ne pouvez pas acheter votre propre article.'], 403);
            }
            if ($product->status !== 'approved') {
                return response()->json(['message' => 'Cet article n\'est plus disponible.'], 422);
            }
        }

        $data = $validated;
        $data['payment_method'] = $validated['payment_method'] ?? 'wallet';

        try {
            $order = $this->checkoutService->checkout($user, $data);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        Log::info('Mobile checkout completed', [
            'order_id' => $order->id,
            'buyer_id' => $user->id,
            'vendor_id' => $order->vendor_id,
            'offer_id' => $order->offer_id,
            'total' => $order->total_amount,
            'payment_method' => $order->payment_method,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Order placed',
            'order_id' => $order->id,
            'status' => $order->status,
            'total' => (float) $order->total_amount,
        ], 201);
    }

    /**
     * Buyer makes (or updates) an offer on a product.
     *
     * Mirrors the web MakeOfferModal::submitOffer() for the buyer path:
     * validates price <= listing, enforces the 5/day limit, reuses an existing
     * pending offer if present, and otherwise creates a new pending offer. The
     * offer_made message (with 24h expiry, broadcast, and seller notification)
     * is sent via ChatService.
     */
    public function createOffer(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'offer_price' => ['required', 'numeric', 'min:0.01'],
        ]);

        $product = Product::with('vendor')->findOrFail($validated['product_id']);

        if ((int) $product->vendor_id === $user->id) {
            return response()->json(['message' => 'You cannot make an offer on your own product'], 403);
        }

        if ($product->status !== 'approved') {
            return response()->json(['message' => 'This product is not available for offers'], 422);
        }

        if ((float) $validated['offer_price'] > (float) $product->price) {
            return response()->json(['message' => 'Offer cannot exceed the listing price'], 422);
        }

        $seller = $product->vendor;
        if (! $seller) {
            return response()->json(['message' => 'Seller not found'], 422);
        }

        $todayCount = Offer::where('buyer_id', $user->id)
            ->where('product_id', $product->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= 5) {
            return response()->json(['message' => 'You have reached your daily limit of 5 offers on this item.'], 429);
        }

        $conversation = $this->chat->getOrCreateConversation($user, $seller, $product);

        $existing = Offer::where('product_id', $product->id)
            ->where('buyer_id', $user->id)
            ->where('seller_id', $seller->id)
            ->where('status', OfferStatus::Pending)
            ->first();

        if ($existing) {
            // Reuse the pending offer: update the price and bump the existing card.
            $existing->update([
                'offer_price' => $validated['offer_price'],
                'created_at' => now(),
            ]);
            $offer = $existing;

            $message = Message::where('offer_id', $offer->id)
                ->where('type', 'offer_made')
                ->first();

            if ($message) {
                $message->update([
                    'body' => sprintf(
                        '%s made an offer of %s MAD for %s.',
                        $user->name,
                        number_format((float) $offer->offer_price, 2),
                        $product->name
                    ),
                    'created_at' => now(),
                ]);
                $conversation->update(['last_message_at' => now()]);
                MessageSent::dispatch($message->load('user'));
            } else {
                $this->chat->sendOfferMadeMessage($conversation, $user, $offer);
            }
        } else {
            $offer = Offer::create([
                'conversation_id' => $conversation->id,
                'product_id' => $product->id,
                'buyer_id' => $user->id,
                'seller_id' => $seller->id,
                'offer_price' => $validated['offer_price'],
                'status' => OfferStatus::Pending,
            ]);

            $this->chat->sendOfferMadeMessage($conversation, $user, $offer);
        }

        Log::info('Offer created', [
            'offer_id' => $offer->id,
            'product_id' => $product->id,
            'buyer_id' => $user->id,
            'seller_id' => $seller->id,
            'offer_price' => $validated['offer_price'],
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Offer sent',
            'offer_id' => $offer->id,
            'conversation_id' => $conversation->id,
        ], 201);
    }

    /**
     * Seller sends a counter offer against a buyer's pending offer.
     *
     * Mirrors the web CounterOfferModal::submitCounterOffer(): rejects the
     * original pending offer ("Counter offer made"), then reuses or creates an
     * AwaitingBuyer counter offer and sends the offer_countered message.
     */
    public function counterOffer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'offer_price' => ['required', 'numeric', 'min:0.01'],
        ]);

        $original = Offer::with(['conversation', 'product'])->findOrFail($id);

        if ($original->seller_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($original->status !== OfferStatus::Pending) {
            return response()->json(['message' => 'Only a pending offer can be countered'], 422);
        }

        $product = $original->product;
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 422);
        }

        if ((float) $validated['offer_price'] > (float) $product->price) {
            return response()->json(['message' => 'Counter offer cannot exceed the listing price'], 422);
        }

        $conversation = $original->conversation;

        // Reject the original pending offer, then raise the counter.
        $original->update([
            'status' => OfferStatus::Rejected,
            'rejection_reason' => 'Counter offer made',
            'responded_at' => now(),
        ]);
        $this->chat->sendOfferResponseMessage($conversation, $user, $original, false, 'Counter offer made');

        $counter = Offer::where('product_id', $product->id)
            ->where('buyer_id', $original->buyer_id)
            ->where('seller_id', $user->id)
            ->where('status', OfferStatus::AwaitingBuyer)
            ->first();

        if ($counter) {
            $counter->update([
                'offer_price' => $validated['offer_price'],
                'created_at' => now(),
            ]);

            $message = Message::where('offer_id', $counter->id)
                ->where('type', 'offer_countered')
                ->first();

            if ($message) {
                $message->update([
                    'body' => sprintf(
                        '%s made a counter offer of %s MAD for %s.',
                        $user->name,
                        number_format((float) $counter->offer_price, 2),
                        $product->name
                    ),
                    'created_at' => now(),
                ]);
                $conversation->update(['last_message_at' => now()]);
                MessageSent::dispatch($message->load('user'));
            } else {
                $this->chat->sendOfferCounteredMessage($conversation, $user, $counter);
            }
        } else {
            $counter = Offer::create([
                'conversation_id' => $conversation->id,
                'product_id' => $product->id,
                'buyer_id' => $original->buyer_id,
                'seller_id' => $user->id,
                'offer_price' => $validated['offer_price'],
                'status' => OfferStatus::AwaitingBuyer,
            ]);

            $this->chat->sendOfferCounteredMessage($conversation, $user, $counter);
        }

        Log::info('Offer countered', [
            'original_offer_id' => $original->id,
            'counter_offer_id' => $counter->id,
            'seller_id' => $user->id,
            'buyer_id' => $original->buyer_id,
            'offer_price' => $validated['offer_price'],
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Counter offer sent',
            'offer_id' => $counter->id,
            'conversation_id' => $conversation->id,
        ], 201);
    }

    public function acceptOffer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $offer = Offer::with('conversation')->findOrFail($id);

        $isSeller = $offer->seller_id === $user->id;
        $isBuyer = $offer->buyer_id === $user->id;

        if (! $isSeller && ! $isBuyer) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // The seller accepts a buyer's pending offer; the buyer accepts the
        // seller's counter offer (awaiting_buyer).
        $canAccept = ($isSeller && $offer->status === OfferStatus::Pending)
            || ($isBuyer && $offer->status === OfferStatus::AwaitingBuyer);

        if (! $canAccept) {
            return response()->json(['message' => 'This offer cannot be accepted in its current state'], 422);
        }

        // Expired offers can no longer be accepted (the sweep may not have run yet).
        if ($offer->expires_at && $offer->expires_at->isPast()) {
            $offer->update(['status' => OfferStatus::Expired, 'responded_at' => now()]);

            return response()->json(['message' => 'This offer has expired'], 422);
        }

        $offer->update([
            'status' => OfferStatus::Accepted,
            'responded_at' => now(),
        ]);

        $this->chat->sendOfferResponseMessage($offer->conversation, $user, $offer, true);

        Log::info('Offer accepted', [
            'offer_id' => $offer->id,
            'amount' => $offer->amount,
            'seller_id' => $user->id,
            'buyer_id' => $offer->buyer_id,
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => 'Offer accepted']);
    }

    public function rejectOffer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $offer = Offer::with('conversation')->findOrFail($id);

        $isSeller = $offer->seller_id === $user->id;
        $isBuyer = $offer->buyer_id === $user->id;

        if (! $isSeller && ! $isBuyer) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $allowedStatuses = $isSeller
            ? [OfferStatus::Pending]
            : [OfferStatus::AwaitingBuyer];

        if (! in_array($offer->status, $allowedStatuses)) {
            return response()->json(['message' => 'This offer cannot be rejected in its current state'], 422);
        }

        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $offer->update([
            'status' => OfferStatus::Rejected,
            'rejection_reason' => $request->reason,
            'responded_at' => now(),
        ]);

        $this->chat->sendOfferResponseMessage($offer->conversation, $user, $offer, false, $request->reason);

        Log::info('Offer rejected', [
            'offer_id' => $offer->id,
            'amount' => $offer->amount,
            'actor_id' => $user->id,
            'role' => $isSeller ? 'seller' : 'buyer',
            'reason' => $request->reason,
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => 'Offer rejected']);
    }

    public function withdrawOffer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $offer = Offer::with('conversation')->findOrFail($id);

        if ($offer->buyer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($offer->status !== OfferStatus::Pending) {
            return response()->json(['message' => 'Only pending offers can be withdrawn'], 422);
        }

        $this->chat->withdrawOffer($offer, $user);

        return response()->json(['message' => 'Offer withdrawn']);
    }

    public function shipOrder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $order = Order::with(['offer.conversation'])->findOrFail($id);

        if ($order->vendor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['message' => 'Order cannot be marked as shipped in its current state'], 422);
        }

        $request->validate([
            'carrier' => ['required', 'string', 'max:100'],
            'tracking_code' => ['nullable', 'string', 'max:200'],
        ]);

        $order->update([
            'status' => 'shipped',
            'carrier' => $request->carrier,
            'tracking_code' => $request->tracking_code,
        ]);

        $conversation = $order->offer?->conversation;
        if ($conversation) {
            $this->chat->sendItemShippedMessage($conversation, $user, $order);
        }

        Log::info('Order shipped', [
            'order_id' => $order->id,
            'vendor_id' => $user->id,
            'buyer_id' => $order->user_id,
            'carrier' => $request->carrier,
            'tracking_code' => $request->tracking_code,
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => 'Order marked as shipped']);
    }

    public function receiveOrder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $order = Order::with(['offer.conversation', 'vendor'])->findOrFail($id);

        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'shipped') {
            return response()->json(['message' => 'Order must be shipped before confirming receipt'], 422);
        }

        // The status change triggers OrderObserver, which is the single place the
        // seller payout happens (escrow release, or direct credit for COD). Never
        // credit wallets here — the old manual increment double-paid the seller
        // and wrote to a users.wallet_balance column that doesn't exist.
        $order->update([
            'status' => 'completed',
            'received_at' => now(),
        ]);

        $conversation = $order->offer?->conversation;
        if ($conversation) {
            $this->chat->sendOrderCompletedMessage($conversation, $user, $order);
        }

        Log::info('Order completed', [
            'order_id' => $order->id,
            'buyer_id' => $user->id,
            'vendor_id' => $order->vendor_id,
            'payout_amount' => $order->payout_amount,
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => 'Order marked as received']);
    }
}
