<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OfferNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $offer;
    public $type; // received, accepted, rejected

    public function __construct($offer, $type)
    {
        $this->offer = $offer;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        $channels = ['database', 'broadcast'];

        // Check if user wants notifications for offers (treating as high priority messages)
        if ($notifiable->getMeta('notify_high_priority_messages', '1') !== '1') {
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
        $message = match ($this->type) {
            'received' => "New offer of $" . number_format($this->offer->offer_price, 2) . " on {$this->offer->product->name}",
            'accepted' => "Your offer for {$this->offer->product->name} was accepted!",
            'rejected' => "Your offer for {$this->offer->product->name} was rejected.",
            default => "Offer update on {$this->offer->product->name}"
        };

        return new BroadcastMessage([
            'type' => 'offer_' . $this->type,
            'offer_id' => $this->offer->id,
            'message' => $message,
            'url' => route('chat.dashboard', ['id' => $this->offer->conversation_id]),
        ]);
    }

    public function toDatabase($notifiable)
    {
        $message = match ($this->type) {
            'received' => "New offer of $" . number_format($this->offer->offer_price, 2) . " on {$this->offer->product->name}",
            'accepted' => "Your offer for {$this->offer->product->name} was accepted!",
            'rejected' => "Your offer for {$this->offer->product->name} was rejected.",
            default => "Offer update on {$this->offer->product->name}"
        };

        return [
            'type' => 'offer_' . $this->type,
            'offer_id' => $this->offer->id,
            'message' => $message,
            'url' => route('chat.dashboard', ['id' => $this->offer->conversation_id]),
        ];
    }
}
