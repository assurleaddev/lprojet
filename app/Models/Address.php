<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'country',
        'full_name',
        'address_line_1',
        'address_line_2',
        'city',
        'postcode',
    ];
}
