<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewProductNotification extends Notification
{
    use Queueable;

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
        $channels = ['database'];

        // Check if user wants notifications for new items
        if ($notifiable->getMeta('notify_new_items', '1') !== '1') {
            return [];
        }

        // Check if user wants email notifications globally
        if ($notifiable->getMeta('enable_email_notifications', '1') === '1') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'new_product',
            'product_id' => $this->product->id,
            'vendor_id' => $this->product->vendor_id,
            'message' => "New product from {$this->product->vendor->full_name}: {$this->product->name}",
            'url' => route('products.show', $this->product->slug), // Assuming route exists
        ];
    }
}
