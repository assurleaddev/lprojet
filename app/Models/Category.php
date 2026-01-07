<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'name_fr', 'slug', 'image', 'parent_id', 'order', 'vinted_id'];


    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function assignedAttributes()
    {
        return $this->belongsToMany(\App\Models\Attribute::class, 'attribute_category');
    }

    /**
     * Get all attributes for this category AND its parents.
     */
    public function getInheritedAttributesAttribute()
    {
        $attributes = collect();
        $category = $this;

        while ($category) {
            // Eager load attributes for the current level
            $category->loadMissing('assignedAttributes.options');

            $current = $category->assignedAttributes;
            $attributes = $attributes->merge($current);
            $category = $category->parent;
        }

        return $attributes->unique('id')->values();
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
