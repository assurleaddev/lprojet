<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Chat\Enums\OfferStatus;
use Modules\Chat\Models\Offer;
use Modules\Chat\Services\ChatService;

class OfferOrderController extends Controller
{
    public function __construct(private readonly ChatService $chat)
    {
    }

    public function acceptOffer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $offer = Offer::with('conversation')->findOrFail($id);

        if ($offer->seller_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! in_array($offer->status, [OfferStatus::Pending, OfferStatus::AwaitingBuyer])) {
            return response()->json(['message' => 'This offer cannot be accepted in its current state'], 422);
        }

        $offer->update([
            'status' => OfferStatus::Accepted,
            'responded_at' => now(),
        ]);

        $this->chat->sendOfferResponseMessage($offer->conversation, $user, $offer, true);

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

        $order->update([
            'status' => 'completed',
            'received_at' => now(),
        ]);

        // Credit seller wallet
        if ($order->vendor && $order->payout_amount > 0) {
            $order->vendor->increment('wallet_balance', $order->payout_amount);
        }

        $conversation = $order->offer?->conversation;
        if ($conversation) {
            $this->chat->sendOrderCompletedMessage($conversation, $user, $order);
        }

        return response()->json(['message' => 'Order marked as received']);
    }
}
