<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Live extends Model
{
    protected $fillable = [
        'seller_id',
        'product_id',
        'title',
        'thumbnail',
        'agora_channel',
        'status',
        'auction_status',
        'starting_bid',
        'current_bid',
        'current_bidder_id',
        'countdown_ends_at',
        'started_at',
        'ended_at',
        'likes_count',
    ];

    protected $casts = [
        'starting_bid' => 'decimal:2',
        'current_bid' => 'decimal:2',
        'countdown_ends_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currentBidder()
    {
        return $this->belongsTo(User::class, 'current_bidder_id');
    }

    public function bids()
    {
        return $this->hasMany(LiveBid::class);
    }

    public function comments()
    {
        return $this->hasMany(LiveComment::class)->latest();
    }

    public function likes()
    {
        return $this->hasMany(LiveLike::class);
    }

    public function getMinNextBidAttribute(): float
    {
        $current = $this->current_bid ?? $this->starting_bid;

        return (float) $current + 10;
    }
}
