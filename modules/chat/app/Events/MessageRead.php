<?php

namespace Modules\Chat\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Modules\Chat\Models\Message;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;
    public int $userId; // The user who read the messages

    public function __construct(int $conversationId, int $userId)
    {
        $this->conversationId = $conversationId;
        $this->userId = $userId;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('conversations.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'messages-read';
    }
}
