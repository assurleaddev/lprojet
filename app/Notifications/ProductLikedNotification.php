<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductLikedNotification extends Notification
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
        $channels = ['database'];

        // Check if user wants email notifications globally
        if ($notifiable->getMeta('enable_email_notifications', '1') === '1') {
            // Check if user wants notifications for favourited items
            if ($notifiable->getMeta('notify_favourited_items', '1') === '1') {
                $channels[] = 'mail';
            }
        }

        // If user disabled this specific notification type, we might want to stop even database?
        // Usually "Notification Settings" control PUSH/EMAIL. Database notifications (bell icon) are often always on or controlled separately.
        // The Used UI screenshot shows "Push notifications" (implied by the toggles) and "Email notifications" (top toggle).
        // However, the prompt says "Enable email notifications" is a top toggle.
        // Let's assume the specific toggles control BOTH or at least the "noisy" parts.
        // If I look at the UI, it says "High-priority notifications" -> "New messages" toggle.
        // If I turn off "New messages", I probably don't want them at all?
        // But for "Favourited items", maybe I still want them in the app?
        // Let's assume the toggles control the generation of the notification entirely for now, OR just the email/push channels.
        // Since we only have 'database' and 'mail' currently:
        // If `notify_favourited_items` is OFF, do we send database?
        // Let's assume if the specific toggle is OFF, we send NOTHING.

        if ($notifiable->getMeta('notify_favourited_items', '1') !== '1') {
            return [];
        }

        // If global email is OFF, remove mail from channels (if it was added or if we default to it)
        // But wait, I constructed channels above.

        // Let's refine:
        // 1. If specific type is OFF -> return []
        // 2. If specific type is ON -> return ['database']
        // 3. If specific type is ON AND Global Email is ON -> return ['database', 'mail']

        return $channels;
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'product_liked',
            'liker_id' => $this->liker->id,
            'product_id' => $this->product->id,
            'message' => "{$this->liker->full_name} liked your product {$this->product->name}.",
            'url' => route('products.show', $this->product->slug),
        ];
    }
}
