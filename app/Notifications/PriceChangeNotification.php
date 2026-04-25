<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $product;
    public $oldPrice;
    public $newPrice;

    public function __construct($product, $oldPrice, $newPrice)
    {
        $this->product = $product;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        $channels = ['database'];

        // Check if user wants notifications for price reductions
        if ($notifiable->getMeta('notify_high_priority_reduced_items', '1') !== '1') {
            return [];
        }

        // Check if user wants email notifications globally
        if ($notifiable->getMeta('enable_email_notifications', '1') === '1') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $discount = round((1 - $this->newPrice / $this->oldPrice) * 100);

        return (new MailMessage())
            ->subject("Price drop on {$this->product->name}")
            ->greeting("Hello {$notifiable->username},")
            ->line("Good news! A product you liked just dropped in price.")
            ->line("**{$this->product->name}** is now **" . number_format($this->newPrice, 2) . " MAD** (was " . number_format($this->oldPrice, 2) . " MAD — {$discount}% off).")
            ->action('View Product', route('products.show', $this->product))
            ->line('Thank you for using our application!');
    }

    public function toDatabase($notifiable)
    {
        $discount = round((1 - $this->newPrice / $this->oldPrice) * 100);

        return [
            'type' => 'price_change',
            'product_id' => $this->product->id,
            'product_image' => $this->product->getFeaturedImageUrl('thumb'),
            'old_price' => $this->oldPrice,
            'new_price' => $this->newPrice,
            'message' => "Price drop! {$this->product->name} is now " . number_format($this->newPrice, 2) . " MAD (-{$discount}%)",
            'url' => route('products.show', $this->product),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
