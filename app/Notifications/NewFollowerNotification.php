<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Notifications\Concerns\ResolvesNotificationChannels;

class NewFollowerNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;
    use ResolvesNotificationChannels;

    /**
     * Create a new notification instance.
     */
    public $follower;

    public function __construct($follower)
    {
        $this->follower = $follower;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return $this->resolveChannels($notifiable);
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'new_follower',
            'follower_id' => $this->follower->id,
            'message' => "{$this->follower->full_name} started following you.",
            'url' => route('vendor.show', $this->follower),
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('You have a new follower!')
            ->line("{$this->follower->full_name} started following you.")
            ->action('View Profile', route('vendor.show', $this->follower))
            ->line('Thank you for using our application!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'new_follower',
            'follower_id' => $this->follower->id,
            'message' => "{$this->follower->full_name} started following you.",
            'url' => route('vendor.show', $this->follower), // Correct route name
        ];
    }
}
