<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Chat\Models\Conversation;

class InboxController extends Controller
{
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

    public function markNotificationsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'ok']);
    }
}
