<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Notifications\Concerns\ResolvesNotificationChannels;

class NewMessageNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;
    use ResolvesNotificationChannels;

    public $message;
    public $sender;

    public function __construct($message, $sender)
    {
        $this->message = $message;
        $this->sender = $sender;
    }

    public function via($notifiable)
    {
        return $this->resolveChannels($notifiable, preferenceKey: 'notify_high_priority_messages');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'sender_id' => $this->sender->id,
            'message' => "{$this->sender->full_name} sent you a message.",
            'url' => route('chat.dashboard', ['id' => $this->message->conversation_id]),
        ]);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('New message from ' . $this->sender->full_name)
            ->line($this->sender->full_name . ' sent you a message:')
            ->line('"' . \Illuminate\Support\Str::limit($this->message->body, 100) . '"')
            ->action('View Message', route('chat.dashboard', ['id' => $this->message->conversation_id]));
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'sender_id' => $this->sender->id,
            'message' => "{$this->sender->full_name} sent you a message.",
            'url' => route('chat.dashboard', ['id' => $this->message->conversation_id]),
        ];
    }
}
