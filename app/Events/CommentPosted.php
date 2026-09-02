<?php

namespace App\Events;

use App\Models\LiveComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentPosted implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public LiveComment $comment)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('live.' . $this->comment->live_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->comment->id,
            'content' => $this->comment->content,
            'username' => $this->comment->user->username,
            'avatar_url' => $this->comment->user->avatar_url,
            'created_at' => $this->comment->created_at->toISOString(),
        ];
    }
}
