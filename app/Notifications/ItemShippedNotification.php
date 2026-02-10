<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ItemShippedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $order;
    public $vendor;

    public function __construct($order, $vendor)
    {
        $this->order = $order;
        $this->vendor = $vendor;
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
            ->subject('Item Shipped: ' . $this->order->product->name)
            ->line('Good news! ' . $this->vendor->full_name . ' has shipped your item: ' . $this->order->product->name)
            ->line('You can track the status in the chat.')
            ->action('View Order', route('chat.dashboard'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'item_shipped',
            'order_id' => $this->order->id,
            'product_id' => $this->order->product_id,
            'vendor_id' => $this->vendor->id,
            'message' => "Item shipped! {$this->vendor->full_name} has shipped your item.",
            'url' => route('chat.dashboard'),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'item_shipped',
            'order_id' => $this->order->id,
            'message' => "Item shipped! {$this->vendor->full_name} has shipped your item.",
            'url' => route('chat.dashboard'),
        ]);
    }
}
