<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = ['name', 'type', 'code', 'icon'];
    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'attribute_category');
    }
}
