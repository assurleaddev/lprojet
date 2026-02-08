<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User; // Your main User model
use App\Models\Product; // Your main Product model
use Modules\Chat\Enums\OfferStatus; // Import Enum

class Offer extends Model
{
    protected $table = 'chat_offers';

    protected $fillable = [
        'conversation_id',
        'product_id',
        'buyer_id',
        'seller_id',
        'offer_price',
        'status',
        'rejection_reason',
        'responded_at',
        'expires_at',
    ];

    // Cast status to the Enum and price to float/decimal
    protected $casts = [
        'status' => OfferStatus::class,
        'offer_price' => 'decimal:2',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}