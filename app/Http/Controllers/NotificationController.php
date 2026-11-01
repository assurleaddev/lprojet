<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\Chat\Models\Offer;

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

        // A notification about a product opens that product's page (likes,
        // offers, new items, price drops…). Older offer notifications only
        // stored the offer, so resolve the product through it.
        $productId = $data['product_id']
            ?? (isset($data['offer_id']) ? Offer::whereKey($data['offer_id'])->value('product_id') : null);
        $product = $productId ? Product::find($productId) : null;

        if ($product && $this->canViewProductPage($product)) {
            return redirect()->route('products.show', $product);
        }

        // Product gone or no longer viewable (e.g. sold): keep the stored link (the chat).
        return redirect($data['url'] ?? route('home'));
    }

    /** Same rule as HomeController::show — sold/pending items are only visible to their seller. */
    private function canViewProductPage(Product $product): bool
    {
        return ! in_array($product->status, ['sold', 'pending'], true)
            || Auth::id() === (int) $product->vendor_id;
    }
}
