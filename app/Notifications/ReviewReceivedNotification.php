<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewReceivedNotification extends Notification
{
    use Queueable;

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
        $channels = ['database'];

        // Check if user wants notifications for new feedback
        if ($notifiable->getMeta('notify_high_priority_feedback', '1') !== '1') {
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
