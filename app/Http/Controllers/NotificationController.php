<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(10);
        return view('notifications.index', compact('notifications'));
    }

    public function markAllRead()
    {
        // Mass update — hydrating every unread model issued one UPDATE per row.
        Auth::user()->unreadNotifications()
            ->whereNotIn('data->type', User::getChatNotificationTypes())
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function read($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $data = $notification->data;

        // Product liked: open a chat with the liker instead of showing the product page
        if (($data['type'] ?? null) === 'product_liked' && ! empty($data['liker_id'])) {
            return redirect()->route('chat.start-with-user', [
                'user_id' => $data['liker_id'],
                'product_id' => $data['product_id'] ?? null,
            ]);
        }

        return redirect($data['url'] ?? route('home'));
    }
}
