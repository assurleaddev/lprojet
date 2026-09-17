<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Notifications\Concerns\ResolvesNotificationChannels;

class OfferNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;
    use ResolvesNotificationChannels;

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
        return $this->resolveChannels($notifiable, preferenceKey: 'notify_high_priority_messages');
    }

    public function toMail($notifiable)
    {
        $subject = match ($this->type) {
            'received' => 'New Offer Received',
            'accepted' => 'Offer Accepted!',
            'rejected' => 'Offer Rejected',
            default => 'Offer Update'
        };

        $line = match ($this->type) {
            'received' => "You've received a new offer of $" . number_format($this->offer->offer_price, 2) . " on {$this->offer->product->name}.",
            'accepted' => "Your offer for {$this->offer->product->name} was accepted!",
            'rejected' => "Your offer for {$this->offer->product->name} was rejected.",
            default => "There is an update on your offer for {$this->offer->product->name}."
        };

        return (new MailMessage())
            ->subject($subject)
            ->line($line)
            ->action('View in Chat', route('chat.dashboard', ['id' => $this->offer->conversation_id]));
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'offer_' . $this->type,
            'offer_id' => $this->offer->id,
            'message' => $this->notificationMessage(),
            'url' => route('chat.dashboard', ['id' => $this->offer->conversation_id]),
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'offer_' . $this->type,
            'offer_id' => $this->offer->id,
            'message' => $this->notificationMessage(),
            'url' => route('chat.dashboard', ['id' => $this->offer->conversation_id]),
        ];
    }

    private function notificationMessage(): string
    {
        return match ($this->type) {
            'received' => "New offer of $" . number_format($this->offer->offer_price, 2) . " on {$this->offer->product->name}",
            'accepted' => "Your offer for {$this->offer->product->name} was accepted!",
            'rejected' => "Your offer for {$this->offer->product->name} was rejected.",
            default => "Offer update on {$this->offer->product->name}"
        };
    }
}
