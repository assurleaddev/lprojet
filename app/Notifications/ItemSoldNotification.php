<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemSoldNotification extends Notification
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
        $channels = ['database'];

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
            ->subject('Item Sold: ' . $this->order->product->name)
            ->line('Great news! ' . $this->buyer->full_name . ' has bought your item: ' . $this->order->product->name)
            ->line('Please download the shipping label and ship the item.')
            ->action('View Order', route('chat.dashboard', ['id' => $this->order->product->conversations->where('user_two_id', $this->buyer->id)->first()->id ?? null]));
        // Note: Linking to chat is tricky if conversation ID isn't handy. 
        // Ideally we pass conversation or find it. 
        // For now, let's assume the user can find it in their inbox or we link to a "My Sales" page if it existed.
        // Let's try to link to the chat dashboard generally or the specific conversation if we can resolve it easily.
        // In CheckoutController we create the conversation. We should probably pass it or the URL.
        // But for now, let's just link to the chat dashboard.
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'item_sold',
            'order_id' => $this->order->id,
            'product_id' => $this->order->product_id,
            'buyer_id' => $this->buyer->id,
            'message' => "Item sold! {$this->buyer->full_name} bought {$this->order->product->name}.",
            'url' => route('chat.dashboard'), // Ideally deep link
        ];
    }
}
