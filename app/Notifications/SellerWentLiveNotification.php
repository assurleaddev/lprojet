<?php

namespace App\Notifications;

use App\Models\Live;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\Concerns\ResolvesNotificationChannels;

class SellerWentLiveNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;
    use ResolvesNotificationChannels;

    public function __construct(public Live $live)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->resolveChannels($notifiable);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->live->seller->full_name . ' ' . __('is live now!'))
            ->line($this->live->seller->full_name . ' ' . __('just started a live auction:') . ' ' . $this->live->title)
            ->action(__('Watch Now'), route('lives.show', $this->live))
            ->line(__('Don\'t miss out — join the live auction now!'));
    }

    private function payload(): array
    {
        return [
            'type' => 'seller_went_live',
            'live_id' => $this->live->id,
            'seller_id' => $this->live->seller_id,
            'seller_name' => $this->live->seller->full_name,
            'title' => $this->live->title,
            'thumbnail' => $this->live->thumbnail
                ? asset('storage/' . $this->live->thumbnail)
                : null,
            'message' => $this->live->seller->full_name . ' ' . __('is live now!') . ' — ' . $this->live->title,
            'url' => route('lives.show', $this->live),
        ];
    }
}
