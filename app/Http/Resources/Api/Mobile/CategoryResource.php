<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->translated_name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'image_url' => $this->getFirstMediaUrl('default') ?: null,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
