<?php

namespace App\Concerns;

use Spatie\Image\Enums\Fit;

trait HasFeaturedImage
{
    /**
     * Register the single-file "featured" media collection.
     */
    protected function addFeaturedImageCollection(): void
    {
        $this->addMediaCollection('featured')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Register the media conversions used for featured images.
     */
    protected function registerFeaturedImageConversions(): void
    {
        // Preview conversion for admin interface
        $this->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300);

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

    /**
     * Get the featured image URL
     */
    public function getFeaturedImageUrl(string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia('featured');

        if (! $media) {
            return null;
        }

        return $conversion ? $media->getUrl($conversion) : $media->getUrl();
    }

    /**
     * Check if the model has a featured image
     */
    public function hasFeaturedImage(): bool
    {
        return $this->hasMedia('featured');
    }
}
