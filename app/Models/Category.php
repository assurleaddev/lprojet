<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'slug', 'parent_id', 'icon'];


    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_category');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')
            ->singleFile()
            ->useDisk('public');
    }

    public function getIconUrlAttribute()
    {
        return $this->getFirstMediaUrl('icon');
    }
}
