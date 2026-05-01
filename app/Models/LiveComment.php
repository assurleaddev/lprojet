<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveComment extends Model
{
    protected $fillable = ['live_id', 'user_id', 'content'];

    public function live()
    {
        return $this->belongsTo(Live::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
