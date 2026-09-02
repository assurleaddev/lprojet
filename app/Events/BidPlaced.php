<?php

namespace App\Events;

use App\Models\Live;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Live $live,
        public User $bidder,
        public float $amount,
        public string $countdownEndsAt,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('live.' . $this->live->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'current_bid' => $this->amount,
            'bidder_username' => $this->bidder->username,
            'countdown_ends_at' => $this->countdownEndsAt,
            'min_next_bid' => $this->amount + 10,
        ];
    }
}
