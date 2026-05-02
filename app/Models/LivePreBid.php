<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivePreBid extends Model
{
    protected $fillable = ['live_id', 'product_id', 'user_id', 'max_amount'];
}
