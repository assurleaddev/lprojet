<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Concerns\ResolvesNotificationChannels;

class ReviewReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use ResolvesNotificationChannels;

    /**
     * Create a new notification instance.
     */
    public $reviewer;
    public $rating;

    public function __construct($reviewer, $rating)
    {
        $this->reviewer = $reviewer;
        $this->rating = $rating;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return $this->resolveChannels($notifiable, ['database'], 'notify_high_priority_feedback');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'review_received',
            'reviewer_id' => $this->reviewer->id,
            'rating' => $this->rating,
            'message' => "{$this->reviewer->full_name} left you a {$this->rating} star review.",
            'url' => '#', // Or link to reviews section
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
