<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_path',
        'button_label',
        'button_url',
        'status',
        'recipient_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }
}
