<?php

namespace App\Http\Resources\Api\Mobile;

use App\Models\ShippingOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /** Cheapest active shipping price, memoised for the whole request. */
    private static ?float $shippingFrom = null;

    private function shippingFrom(): float
    {
        return self::$shippingFrom ??= (float) (ShippingOption::where('is_active', true)->min('price')
            ?? config('settings.delivery_fee_fixed', 25));
    }

    private function buyerProtection(float $price): float
    {
        $pct = (float) config('settings.buyer_protection_fee_percentage', 5);
        $fixed = (float) config('settings.buyer_protection_fee_fixed', 0.70);

        return round($price * $pct / 100 + $fixed, 2);
    }

    /** Whether the authenticated viewer owns this product (via bearer token). */
    private function viewerOwns(Request $request): bool
    {
        $viewer = $request->user('sanctum') ?? $request->user();

        return $viewer ? (int) $this->vendor_id === (int) $viewer->id : false;
    }

    private function resolveFeaturedImage(): ?string
    {
        $featured = $this->getFirstMedia('featured');
        if ($featured) {
            return $featured->hasGeneratedConversion('preview')
                ? $featured->getUrl('preview')
                : $featured->getUrl();
        }

        $first = $this->getFirstMedia('products');

        return $first
            ? ($first->hasGeneratedConversion('preview') ? $first->getUrl('preview') : $first->getUrl())
            : null;
    }

    public function toArray(Request $request): array
    {
        return [
            // Cast ids to int — multipart/form creates leave these as strings
            // in-memory, which breaks strongly-typed mobile clients.
            'id' => (int) $this->id,
            'title' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'condition' => $this->condition,
            'size' => $this->size,
            'brand' => $this->brand?->name,
            'brand_id' => $this->brand_id !== null ? (int) $this->brand_id : null,
            'category_id' => $this->category_id !== null ? (int) $this->category_id : null,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->translated_name,
            ]),
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->full_name,
                'avatar_url' => $this->vendor->avatar_url ?? null,
                'member_since' => $this->vendor->created_at->year,
            ]),
            // Cover (featured) first, then the rest of the gallery.
            'images' => $this->getMedia('featured')->merge($this->getMedia('products'))->map(fn ($m) => [
                'url' => $m->getUrl(),
                'preview_url' => $m->hasGeneratedConversion('preview') ? $m->getUrl('preview') : $m->getUrl(),
            ])->values(),
            'featured_image' => $this->resolveFeaturedImage(),
            'price_incl_protection' => (float) $this->price + $this->buyerProtection((float) $this->price),
            'buyer_protection' => $this->buyerProtection((float) $this->price),
            'shipping_from' => $this->shippingFrom(),
            'favorites_count' => $this->whenCounted('favorites', fn () => (int) $this->favorites_count),
            'fabric' => $this->fabric ?? [],
            'option_ids' => $this->whenLoaded('options', fn () => $this->options->pluck('id')->map(fn ($id) => (int) $id)->values()),
            'is_favorited' => $this->when(
                (bool) $request->user(),
                fn () => $this->resource->isFavorited($request->user()->id)
            ),
            'status' => $this->status,
            'is_owner' => $this->viewerOwns($request),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
