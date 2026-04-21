<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductLikedNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $liker;
    public $product;

    public function __construct($liker, $product)
    {
        $this->liker = $liker;
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        $channels = ['database', 'broadcast'];

        if ($notifiable->getMeta('notify_favourited_items', '1') !== '1') {
            return [];
        }

        // Check if user wants email notifications globally
        if ($notifiable->getMeta('enable_email_notifications', '1') === '1') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'product_liked',
            'liker_id' => $this->liker->id,
            'product_id' => $this->product->id,
            'product_image' => $this->product->getFeaturedImageUrl('thumb'),
            'message' => "{$this->liker->full_name} liked your product {$this->product->name}.",
            'url' => route('chat.start-with-user', ['user_id' => $this->liker->id, 'product_id' => $this->product->id]),
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'product_liked',
            'liker_id' => $this->liker->id,
            'product_id' => $this->product->id,
            'product_image' => $this->product->getFeaturedImageUrl('thumb'),
            'message' => "{$this->liker->full_name} liked your product {$this->product->name}.",
            'url' => route('chat.start-with-user', ['user_id' => $this->liker->id, 'product_id' => $this->product->id]),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('New Like on your product')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("{$this->liker->full_name} liked your product: {$this->product->name}.")
            ->action('View Product', route('products.show', $this->product))
            ->line('Thank you for using our application!');
    }
}
