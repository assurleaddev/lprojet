<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->where('status', 'approved')
            ->with(['vendor', 'category', 'brand', 'media']);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($minPrice = $request->query('min_price')) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice = $request->query('max_price')) {
            $query->where('price', '<=', $maxPrice);
        }

        if ($condition = $request->query('condition')) {
            $query->where('condition', $condition);
        }

        $sortBy = $request->query('sort', 'newest');
        match ($sortBy) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $products = $query->paginate($request->query('per_page', 20));

        return ProductResource::collection($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::where('status', 'approved')
            ->with(['vendor', 'category', 'brand', 'media'])
            ->findOrFail($id);

        return response()->json(new ProductResource($product));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'condition' => ['required', 'string', 'in:new_with_tags,new_without_tags,very_good,good,satisfactory,heavily_worn'],
            'size' => ['nullable', 'string'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $product = Product::create([
            ...$validated,
            'vendor_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMedia($image)->toMediaCollection('products');
            }
        }

        $product->load(['vendor', 'category', 'brand']);

        return response()->json(new ProductResource($product), 201);
    }
}
