<?php

namespace App\Events;

use App\Models\Live;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveLiked implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Live $live)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('live.' . $this->live->id)];
    }

    public function broadcastWith(): array
    {
        return ['likes_count' => $this->live->likes_count];
    }
}
