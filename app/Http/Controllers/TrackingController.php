<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\TrackingService;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function show(Order $order, TrackingService $trackingService)
    {
        if ($order->user_id !== Auth::id() && $order->vendor_id !== Auth::id()) {
            abort(403);
        }

        if (! $order->carrier || ! $order->tracking_code) {
            return view('frontend.tracking.show', [
                'order' => $order,
                'events' => [],
                'info' => [],
                'error' => __('No tracking information available yet.'),
                'carrier' => null,
            ]);
        }

        $result = $trackingService->track($order->carrier, $order->tracking_code);

        return view('frontend.tracking.show', [
            'order' => $order,
            'events' => $result['events'],
            'info' => $result['info'],
            'error' => $result['error'],
            'carrier' => $order->carrier,
        ]);
    }
}
