<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class LiveResource extends JsonResource
{
    private function productImage($product): ?string
    {
        if (! $product) {
            return null;
        }
        $url = $product->getFirstMediaUrl('featured', 'preview')
            ?: $product->getFirstMediaUrl('products', 'preview')
            ?: $product->getFirstMediaUrl('featured')
            ?: $product->getFirstMediaUrl('products');

        return $url ?: null;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'title' => $this->title,
            'status' => $this->status,               // scheduled | live | ended
            'auction_status' => $this->auction_status, // idle | active
            'thumbnail_url' => $this->thumbnail ? Storage::disk('public')->url($this->thumbnail) : null,
            'likes_count' => (int) ($this->likes_count ?? 0),
            'starting_bid' => (float) $this->starting_bid,
            'current_bid' => $this->current_bid !== null ? (float) $this->current_bid : null,
            'min_next_bid' => $this->min_next_bid,
            'countdown_ends_at' => optional($this->countdown_ends_at)->toIso8601String(),
            'started_at' => optional($this->started_at)->toIso8601String(),
            'ended_at' => optional($this->ended_at)->toIso8601String(),
            'agora_channel' => $this->agora_channel,

            'seller' => $this->whenLoaded('seller', fn () => $this->seller ? [
                'id' => (int) $this->seller->id,
                'name' => $this->seller->full_name ?: $this->seller->username,
                'avatar_url' => $this->seller->avatar_url ?? null,
                'is_following' => $request->user() && $request->user()->id !== $this->seller->id
                    ? $request->user()->isFollowing($this->seller)
                    : false,
                'followers_count' => $this->seller->followers()->count(),
            ] : null),

            'current_product' => $this->whenLoaded('product', fn () => $this->product ? [
                'id' => (int) $this->product->id,
                'title' => $this->product->name,
                'image' => $this->productImage($this->product),
            ] : null),

            'current_bidder' => $this->whenLoaded('currentBidder', fn () => $this->currentBidder ? [
                'id' => (int) $this->currentBidder->id,
                'name' => $this->currentBidder->username,
            ] : null),

            'products' => $this->whenLoaded('liveProducts', fn () => $this->liveProducts->map(fn ($p) => [
                'id' => (int) $p->id,
                'title' => $p->name,
                'image' => $this->productImage($p),
                'pre_bid_min' => (float) $p->pivot->pre_bid_min,
            ])->values()),
        ];
    }
}
