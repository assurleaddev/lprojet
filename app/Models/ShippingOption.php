<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingOption extends Model
{
    protected $fillable = [
        'key',
        'label',
        'logo_path',
        'description',
        'icon_class',
        'type',
        'is_active',
    ];
    //
}
