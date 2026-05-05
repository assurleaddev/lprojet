<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\TrackingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshOrderTracking implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 45;

    public function __construct(public readonly int $orderId)
    {
    }

    public function handle(TrackingService $trackingService): void
    {
        $order = Order::find($this->orderId);

        if (! $order) {
            return;
        }

        // Race condition guard: skip if already refreshed within the last hour
        if ($order->tracking_checked_at && $order->tracking_checked_at->gt(now()->subHour())) {
            return;
        }

        if ($order->status !== 'shipped') {
            return;
        }

        $result = $trackingService->track($order->carrier, $order->tracking_code);

        if (empty($result['error'])) {
            $order->update([
                'tracking_events' => $result['events'],
                'tracking_info' => $result['info'],
                'tracking_checked_at' => now(),
            ]);
        } else {
            // Still update the timestamp so we don't hammer a broken code every minute
            $order->update(['tracking_checked_at' => now()]);
        }
    }
}
