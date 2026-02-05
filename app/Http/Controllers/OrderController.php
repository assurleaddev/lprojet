<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Orders where the user is the buyer
        $purchases = Order::where('user_id', $userId)
            ->with(['product', 'vendor'])
            ->latest()
            ->get();

        // Orders where the user is the vendor
        $sales = Order::where('vendor_id', $userId)
            ->with(['product', 'user'])
            ->latest()
            ->get();

        return view('frontend.orders.index', compact('purchases', 'sales'));
    }
}
