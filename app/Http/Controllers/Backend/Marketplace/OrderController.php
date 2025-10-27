<?php

namespace App\Http\Controllers\Backend\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function __construct()
    {
        // Add constructor to apply permissions to all methods
        $this->authorizeResource(Order::class, 'order');
    }
    
    public function index()
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::with(['user', 'product'])->latest();

        // If the user is a vendor (can view 'own' but not 'all'), scope the query
        if (auth()->user()->can('order.view.own') && !auth()->user()->can('order.view.all')) {
            // This is the simplified query
            $query->where('vendor_id', auth()->id());
        }

        $orders = $query->paginate(20);

        return view('backend.marketplace.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        // Load the single product and related users
        $order->load('product', 'user', 'vendor'); 
        return view('backend.marketplace.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $request->validate(['status' => 'required|in:pending,processing,shipped,delivered,cancelled']);
        
        $order->update(['status' => $request->status]);

        return back()->with('success', 'Order status updated successfully.');
    }

    public function uploadReceipt(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        if (!in_array($order->status, ['shipped', 'delivered'])) {
            return back()->with('error', 'You can only upload a receipt for shipped or delivered orders.');
        }

        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($order->delivery_receipt_path) {
            Storage::disk('public')->delete($order->delivery_receipt_path);
        }

        $path = $request->file('receipt')->store('receipts', 'public');
        $order->update(['delivery_receipt_path' => $path]);
        
        return back()->with('success', 'Delivery receipt uploaded successfully.');
    }
}