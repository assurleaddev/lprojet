<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Modules\Wallet\Services\WalletService;
use Modules\Chat\Services\ChatService;
use Illuminate\Http\Request;

class OrderLifecycleController extends Controller
{
    protected $walletService;
    protected $chatService;

    public function __construct(WalletService $walletService, ChatService $chatService)
    {
        $this->walletService = $walletService;
        $this->chatService = $chatService;
    }

    public function markAsShipped(Order $order)
    {
        if (auth()->id() !== $order->vendor_id) {
            abort(403);
        }

        if ($order->status !== 'processing') {
            return back()->with('error', 'Order cannot be marked as shipped.');
        }

        $order->update(['status' => 'shipped']);

        // Notify Buyer via Chat
        $conversation = $this->chatService->getOrCreateConversation($order->user, $order->vendor, $order->product);
        $this->chatService->sendMessage($conversation, auth()->user(), "I have shipped your item! You can track it here: [Tracking Link Placeholder]");

        // Notification
        $order->user->notify(new \App\Notifications\OrderUpdateNotification($order, 'shipped'));

        return back()->with('success', 'Order marked as shipped.');
    }

    public function markAsReceived(Order $order)
    {
        if (auth()->id() !== $order->user_id) {
            abort(403);
        }

        if ($order->status !== 'shipped') {
            return back()->with('error', 'Order cannot be marked as received yet.');
        }

        // 1. Release Funds to Vendor
        try {
            $this->walletService->releasePendingFunds($order->vendor, $order->amount, 'Order #' . $order->id);

            // 2. Update Order Status
            $order->update(['status' => 'delivered']); // or 'completed'

            // 3. Notify Vendor via Chat
            $conversation = $this->chatService->getOrCreateConversation($order->user, $order->vendor, $order->product);
            $this->chatService->sendMessage($conversation, auth()->user(), "I have received the item! Everything is great. Funds have been released.");

            // Notification
            $order->vendor->notify(new \App\Notifications\OrderUpdateNotification($order, 'delivered'));

            return back()->with('success', 'Order completed and funds released!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
