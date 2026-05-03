<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = ['name', 'name_fr', 'type', 'code', 'icon'];

    public function getTranslatedNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'fr' && $this->name_fr) {
            return $this->name_fr;
        }

        return $this->name;
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'attribute_category');
    }
}
