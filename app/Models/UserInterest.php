<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'category_id', 'brand_id', 'interest_score', 'updated_at'];

    protected function casts(): array
    {
        return ['interest_score' => 'decimal:4'];
    }
}
