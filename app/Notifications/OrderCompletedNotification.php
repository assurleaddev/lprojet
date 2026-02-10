<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrderCompletedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $order;
    public $buyer;

    public function __construct($order, $buyer)
    {
        $this->order = $order;
        $this->buyer = $buyer;
    }

    public function via($notifiable)
    {
        $channels = ['database', 'broadcast'];

        // Transactional notification - always send database

        // Check if user wants email notifications globally
        if ($notifiable->getMeta('enable_email_notifications', '1') === '1') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order Completed: ' . $this->order->product->name)
            ->line('Success! ' . $this->buyer->full_name . ' has received the item and the order is complete.')
            ->line('The funds have been released to your wallet.')
            ->action('View Wallet', route('settings.profile')); // Or wallet page if exists
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'order_completed',
            'order_id' => $this->order->id,
            'product_id' => $this->order->product_id,
            'buyer_id' => $this->buyer->id,
            'message' => "Order completed! Funds released for {$this->order->product->name}.",
            'url' => route('chat.dashboard'),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'order_completed',
            'order_id' => $this->order->id,
            'message' => "Order completed! Funds released for {$this->order->product->name}.",
            'url' => route('chat.dashboard'),
        ]);
    }
}
