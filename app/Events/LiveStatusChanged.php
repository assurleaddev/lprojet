<?php

namespace App\Events;

use App\Models\Live;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Live $live)
    {
    }

    public function broadcastOn(): array
    {
        $channels = [new Channel('live.' . $this->live->id)];

        if ($this->live->status === 'live') {
            $channels[] = new Channel('lives-feed');
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'live_id' => $this->live->id,
            'status' => $this->live->status,
            'winner_username' => $this->live->currentBidder?->username,
            'winning_bid' => $this->live->current_bid,
        ];
    }
}
