<?php

namespace App\Events;

use App\Models\Live;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionProductChanged implements ShouldBroadcastNow
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
        $product = $this->live->product;

        return [
            'product_id' => $product?->id,
            'product_name' => $product?->name,
            'product_image' => $product?->getFeaturedImageUrl('preview'),
            'starting_bid' => (float) $this->live->starting_bid,
            'current_bid' => null,
            'auction_status' => $this->live->auction_status,
        ];
    }
}
