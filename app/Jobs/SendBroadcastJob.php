<?php

namespace App\Jobs;

use App\Models\Broadcast;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Message;

class SendBroadcastJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $broadcastId)
    {
    }

    public function handle(): void
    {
        $broadcast = Broadcast::find($this->broadcastId);
        if (! $broadcast || $broadcast->status === 'sent') {
            return;
        }

        $broadcast->update(['status' => 'sending']);

        $platformUser = User::where('is_system', true)->first();
        if (! $platformUser) {
            return;
        }

        $count = 0;

        User::where('is_system', false)
            ->whereNull('banned_at')
            ->orderBy('id')
            ->chunk(100, function ($users) use ($broadcast, $platformUser, &$count) {
                foreach ($users as $user) {
                    $conversation = Conversation::firstOrCreate(
                        [
                            'user_one_id' => $platformUser->id,
                            'user_two_id' => $user->id,
                            'product_id' => null,
                        ]
                    );

                    Message::create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $platformUser->id,
                        'body' => $broadcast->title,
                        'type' => 'platform_broadcast',
                        'metadata' => [
                            'broadcast_id' => $broadcast->id,
                            'pre_text' => $broadcast->pre_text,
                            'title' => $broadcast->title,
                            'body' => $broadcast->body,
                            'after_text' => $broadcast->after_text,
                            'image_path' => $broadcast->image_path,
                            'button_label' => $broadcast->button_label,
                            'button_url' => $broadcast->button_url,
                        ],
                    ]);

                    $conversation->update(['last_message_at' => now()]);
                    $count++;
                }
            });

        $broadcast->update([
            'status' => 'sent',
            'sent_at' => now(),
            'recipient_count' => $count,
        ]);
    }
}
