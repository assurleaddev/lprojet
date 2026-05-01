<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Orders where the user is the buyer
        $purchases = Order::where('user_id', $userId)
            ->with(['product', 'vendor', 'items.product'])
            ->latest()
            ->get();

        $sales = Order::where('vendor_id', $userId)
            ->with(['product', 'user', 'items.product'])
            ->latest()
            ->get();

        return view('frontend.orders.index', compact('purchases', 'sales'));
    }
}
