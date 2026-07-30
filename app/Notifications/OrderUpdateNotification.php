<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Concerns\ResolvesNotificationChannels;

class OrderUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use ResolvesNotificationChannels;

    /**
     * Create a new notification instance.
     */
    public $order;
    public $status;

    public function __construct($order, $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return $this->resolveChannels($notifiable, ['database']);
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'order_update',
            'order_id' => $this->order->id,
            'status' => $this->status,
            'message' => "Your order #{$this->order->id} has been {$this->status}.",
            'url' => route('chat.dashboard'), // Or order details page if exists
        ];
    }
}
