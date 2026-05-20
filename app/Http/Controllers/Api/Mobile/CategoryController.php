<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('order')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show(int $id): CategoryResource
    {
        $category = Category::with('allChildren')->findOrFail($id);

        return new CategoryResource($category);
    }

    public function attributes(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $attributes = $category->getInheritedAttributesAttribute();

        return response()->json($attributes->map(fn ($attr) => [
            'id' => $attr->id,
            'name' => $attr->translated_name,
            'code' => $attr->code,
            'type' => $attr->type,
            'options' => $attr->options->map(fn ($opt) => [
                'id' => $opt->id,
                'value' => $opt->value,
            ])->values(),
        ])->values());
    }
}
