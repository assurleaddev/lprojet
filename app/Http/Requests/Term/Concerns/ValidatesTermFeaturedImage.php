<?php

declare(strict_types=1);

namespace App\Http\Requests\Term\Concerns;

use App\Services\Content\ContentService;
use Closure;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait ValidatesTermFeaturedImage
{
    /**
     * Append the featured image validation rule when the request's taxonomy
     * supports featured images.
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    protected function withFeaturedImageRule(array $rules): array
    {
        $taxonomyName = $this->route('taxonomy');
        $taxonomyModel = app(ContentService::class)->getTaxonomies()->where('name', $taxonomyName)->first();

        if ($taxonomyModel && $taxonomyModel->show_featured_image) {
            /** @example null */
            $rules['featured_image'] = [
                'nullable',
                $this->featuredImageValidator(),
            ];
        }

        return $rules;
    }

    /**
     * Build the closure validating an uploaded featured image or media reference.
     */
    protected function featuredImageValidator(): Closure
    {
        return function ($attribute, $value, $fail) {
            // Allow either file upload or media ID.
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                // Validate as image file.
                if (! in_array($value->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                    $fail('The featured image must be a valid image file (JPEG, PNG, GIF, or WebP).');
                }
                if ($value->getSize() > 2048 * 1024) { // 2MB in bytes
                    $fail('The featured image must not be larger than 2MB.');
                }
            } elseif (is_string($value)) {
                // Validate as media ID or URL.
                if (! is_numeric($value)) {
                    // If it's not numeric, check if it's a valid URL.
                    if (! filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('The featured image must be a valid media ID or URL.');
                    }
                } else {
                    // If it's numeric, verify the media exists.
                    $mediaExists = Media::find($value);
                    if (! $mediaExists) {
                        $fail('The selected media does not exist.');
                    }
                }
            }
        };
    }
}
