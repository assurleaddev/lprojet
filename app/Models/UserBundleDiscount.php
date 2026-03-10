<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBundleDiscount extends Model
{
    protected $fillable = [
        'user_id',
        'min_items',
        'discount_percentage',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
