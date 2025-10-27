<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = ['attribute_id', 'value'];
    public function attribute() {
        return $this->belongsTo(Attribute::class);
    }
}
