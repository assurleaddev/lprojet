<?php

namespace App\Models;
use Spatie\Image\Enums\Fit;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as PMedia;
use ChristianKuri\LaravelFavorite\Traits\Favoriteable;

class Product extends Model implements HasMedia

{
    use InteractsWithMedia;
    use Favoriteable; 

    protected $fillable = ['name', 'description', 'price', 'vendor_id', 'category_id', 'status'];
    
    public function options() {
        return $this->belongsToMany(Option::class, 'product_option');
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }


    public function vendor() {
        return $this->belongsTo(User::class, 'vendor_id');
    }
    public function hasFeaturedImage(): bool
    {
        return $this->hasMedia('featured');
    }
    /**
     * Register media collections for the product.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('products')
            // You can add rules here if needed, e.g., ->singleFile() for a single image
            // For multiple images, you just don't add that rule.
            ->useDisk('public');

        $this->addMediaCollection('featured')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(PMedia $media = null): void
    {
        $this->addMediaConversion('preview')
              ->fit(Fit::Contain, 300, 300)
              ->nonQueued();
              // Thumbnail for featured images
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10);

        // Medium size for content display
        $this->addMediaConversion('medium')
            ->width(500)
            ->height(500);

        // Large size for detailed view
        $this->addMediaConversion('large')
            ->width(1000)
            ->height(1000);
    }

    public function getFeaturedImageUrl(string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia('featured');

        if (! $media) {
            return null;
        }

        return $conversion ? $media->getUrl($conversion) : $media->getUrl();
    }


    public function getBrandAttribute(): ?string
    {
        $brandOption = $this->options->firstWhere('attribute.name', 'brand');
        return $brandOption ? $brandOption->value : null;
    }

    public function getConditionAttribute(): ?string
    {
        $conditionOption = $this->options->firstWhere('attribute.name', 'condition');
        return $conditionOption ? $conditionOption->value : null;
    }

    public function getColorAttribute(): ?string
    {
        $colorOption = $this->options->firstWhere('attribute.name', 'Colors');
        return $colorOption ? $colorOption->value : null;
    }

    public function getSizeAttribute(): ?string
    {
        
        $colorOption = $this->options->firstWhere('attribute.name', 'Size');
        return $colorOption ? $colorOption->value : null;
    }
    public function getOptionsSummaryAttribute(): string
    {
        $grouped = $this->options->groupBy('attribute_id');

        // Optional: enforce a display order by attribute slug/name
        $orderedSlugs = ['size', 'condition', 'color', 'brand'];

        // Need the attribute relation on options for ordering by slug:
        $this->options->loadMissing('attribute');

        $bySlug = $this->options
            ->groupBy(fn($o) => optional($o->attribute)->slug ?? (string)$o->attribute_id)
            ->map(fn($grp) => $grp->pluck('value')->implode(' / '));

        $parts = collect($orderedSlugs)
            ->map(fn($slug) => $bySlug[$slug] ?? null)
            ->filter()
            ->values();

        // add any attributes not in the ordered list
        $rest = $bySlug->except($orderedSlugs)->values();

        return $parts->merge($rest)->implode(' · ');
    }
}
