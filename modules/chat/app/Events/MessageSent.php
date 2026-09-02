<?php

namespace Modules\Chat\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Modules\Chat\Models\Message;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('user');
    }

    public function broadcastOn(): Channel
    {
        return new PresenceChannel('conversations.' . $this->message->conversation_id);
    }

    /**
     * Set the event name explicitly for client-side listening.
     */
    public function broadcastAs(): string
    {
        // Must be simple and non-namespaced!
        return 'new-message';
    }

    public function broadcastWith(): array
    {
        // ... existing data ...
        return [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
            ],
            'created_at_human' => $this->message->created_at->diffForHumans(),
        ];
    }
}
