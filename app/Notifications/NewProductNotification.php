<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Concerns\ResolvesNotificationChannels;

class NewProductNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;
    use ResolvesNotificationChannels;

    /**
     * Create a new notification instance.
     */
    public $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return $this->resolveChannels($notifiable, preferenceKey: 'notify_new_items');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'new_product',
            'product_id' => $this->product->id,
            'vendor_id' => $this->product->vendor_id,
            'product_image' => $this->product->getFeaturedImageUrl('thumb'),
            'message' => "New product from {$this->product->vendor->full_name}: {$this->product->name}",
            'url' => route('products.show', $this->product),
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'new_product',
            'product_id' => $this->product->id,
            'vendor_id' => $this->product->vendor_id,
            'product_image' => $this->product->getFeaturedImageUrl('thumb'),
            'message' => "New product from {$this->product->vendor->full_name}: {$this->product->name}",
            'url' => route('products.show', $this->product),
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject("New Product from " . $this->product->vendor->full_name)
            ->line("{$this->product->vendor->full_name} has just uploaded a new item: {$this->product->name}")
            ->action('View Product', route('products.show', $this->product))
            ->line('Check it out before it\'s gone!');
    }
}
