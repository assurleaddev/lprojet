<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Services\ChatService;

class InboxController extends Controller
{
    /**
     * Start (or reuse) a conversation with a product's seller — the "Message"
     * button on the product page. Returns the conversation in list shape.
     */
    public function startConversation(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate(['product_id' => ['required', 'exists:products,id']]);

        $product = Product::with('vendor')->findOrFail($validated['product_id']);
        $seller = $product->vendor;

        if (! $seller || $seller->id === $user->id) {
            return response()->json(['message' => 'Impossible de démarrer cette conversation.'], 422);
        }

        $conv = app(ChatService::class)->getOrCreateConversation($user, $seller, $product);
        $conv->load('lastMessage.user');
        $last = $conv->lastMessage;

        return response()->json([
            'id' => $conv->id,
            'other_user' => [
                'id' => $seller->id,
                'name' => $seller->full_name,
                'avatar_url' => $seller->avatar_url,
            ],
            'product' => [
                'id' => $product->id,
                'title' => $product->name,
                'image' => $product->getFeaturedImageUrl('thumb'),
            ],
            'last_message' => $last ? [
                'body' => $last->body ?: '📎 Pièce jointe',
                'sent_at' => $last->created_at->diffForHumans(),
                'is_mine' => $last->user_id === $user->id,
            ] : null,
            'unread_count' => 0,
        ]);
    }

    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['product', 'userOne', 'userTwo', 'lastMessage.user'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) use ($user) {
                $other = $conv->user_one_id === $user->id ? $conv->userTwo : $conv->userOne;
                $last = $conv->lastMessage;
                $unread = $conv->messages()
                    ->where('user_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->count();

                return [
                    'id' => $conv->id,
                    'other_user' => [
                        'id' => $other->id,
                        'name' => $other->full_name,
                        'avatar_url' => $other->avatar_url,
                    ],
                    'product' => $conv->product ? [
                        'id' => $conv->product->id,
                        'title' => $conv->product->name,
                        'image' => $conv->product->getFeaturedImageUrl('thumb'),
                    ] : null,
                    'last_message' => $last ? [
                        'body' => $last->body ?: '📎 Pièce jointe',
                        'sent_at' => $last->created_at->diffForHumans(),
                        'is_mine' => $last->user_id === $user->id,
                    ] : null,
                    'unread_count' => $unread,
                ];
            });

        return response()->json(['data' => $conversations]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? 'notification',
                'message' => $n->data['message'] ?? '',
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        return response()->json(['data' => $notifications]);
    }

    public function messages(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::query()
            ->where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id);
            })
            ->firstOrFail();

        $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Find the active order for this conversation (for messages without offer_id)
        $activeOrder = Order::query()
            ->whereHas('offer', fn ($q) => $q->where('conversation_id', $conversation->id))
            ->latest()
            ->first();

        $messages = $conversation->messages()
            ->with(['offer.product.media', 'offer.order'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($m) use ($user, $activeOrder) {
                $offer = $m->offer;
                $order = $offer?->order ?? $activeOrder;

                $offerData = null;
                if ($offer) {
                    $product = $offer->product;
                    $image = $product?->getFirstMediaUrl('featured', 'preview')
                        ?: $product?->getFirstMediaUrl('products', 'preview')
                        ?: $product?->getFirstMediaUrl('featured')
                        ?: $product?->getFirstMediaUrl('products');

                    $offerData = [
                        'id' => $offer->id,
                        'price' => (float) $offer->offer_price,
                        'status' => $offer->status->value,
                        'is_buyer' => $offer->buyer_id === $user->id,
                        'is_seller' => $offer->seller_id === $user->id,
                        'product_title' => $product?->name,
                        'product_image' => $image,
                        'rejection_reason' => $offer->rejection_reason,
                        'expires_at' => $offer->expires_at?->toISOString(),
                    ];
                }

                $orderData = null;
                if ($order) {
                    $orderData = [
                        'id' => $order->id,
                        'status' => $order->status,
                        'carrier' => $order->carrier,
                        'tracking_code' => $order->tracking_code,
                        'amount' => (float) $order->amount,
                        'payout_amount' => (float) $order->payout_amount,
                        'is_buyer' => $order->user_id === $user->id,
                        'is_seller' => $order->vendor_id === $user->id,
                    ];
                }

                return [
                    'id' => $m->id,
                    'type' => $m->type ?? 'text',
                    'body' => $m->body,
                    'is_mine' => $m->user_id === $user->id,
                    'created_at' => $m->created_at->toISOString(),
                    'sent_at' => $m->created_at->diffForHumans(),
                    'metadata' => $m->metadata,
                    'offer' => $offerData,
                    'order' => $orderData,
                ];
            });

        return response()->json(['data' => $messages]);
    }

    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::query()
            ->where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id);
            })
            ->firstOrFail();

        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'body' => $request->body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'data' => [
                'id' => $message->id,
                'body' => $message->body,
                'is_mine' => true,
                'created_at' => $message->created_at->toISOString(),
                'sent_at' => $message->created_at->diffForHumans(),
            ],
        ], 201);
    }

    public function markNotificationsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'ok']);
    }
}
