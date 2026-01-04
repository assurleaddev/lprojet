<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'review',
        'rating',
        'model_id',
        'model_type',
        'author_id',
        'author_type',
        'is_auto',
    ];

    protected $casts = [
        'is_auto' => 'boolean',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function author()
    {
        return $this->morphTo();
    }
}
