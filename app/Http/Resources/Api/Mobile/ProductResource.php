<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'condition' => $this->condition,
            'size' => $this->size,
            'brand' => $this->brand?->name,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
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
            'images' => $this->getMedia('products')->map(fn ($m) => [
                'url' => $m->getUrl(),
                'preview_url' => $m->getUrl('preview'),
            ])->values(),
            'featured_image' => $this->getFeaturedImageUrl('preview'),
            'is_favorited' => $this->when(
                $request->user(),
                fn () => $this->isFavoritedBy($request->user())
            ),
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
