<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Modules\Wallet\Services\WalletService;
use Modules\Chat\Services\ChatService;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, WalletService $walletService, ChatService $chatService)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:5|max:1000',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'delivered') {
            return back()->with('error', 'Order not eligible for review.');
        }

        // Prevent duplicates
        $exists = Review::where('author_id', Auth::id())
            ->where('model_id', $order->vendor_id)
            ->where('model_type', User::class)
            ->where('created_at', '>', $order->created_at)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You have already reviewed this order.');
        }

        // Create Review
        Review::create([
            'rating' => $request->rating,
            'review' => $request->review,
            'model_id' => $order->vendor_id,
            'model_type' => User::class,
            'author_id' => Auth::id(),
            'author_type' => User::class,
        ]);

        // Complete Order & Release Funds
        $walletService->releasePendingFunds($order->vendor, $order->amount, 'Order #' . $order->id);
        $order->update(['status' => 'completed']);

        // Notify via Chat
        // We need the conversation.
        $conversation = \Modules\Chat\Models\Conversation::where('product_id', $order->product_id)
            ->where(function ($q) {
                $q->where('user_one_id', Auth::id())->orWhere('user_two_id', Auth::id());
            })
            ->first();

        if ($conversation) {
            $chatService->sendOrderCompletedMessage($conversation, Auth::user(), $order);
        }

        return back()->with('success', 'Review submitted successfully!');
    }
}
