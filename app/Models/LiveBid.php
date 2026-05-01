<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveBid extends Model
{
    protected $fillable = ['live_id', 'user_id', 'amount'];

    public function live()
    {
        return $this->belongsTo(Live::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
