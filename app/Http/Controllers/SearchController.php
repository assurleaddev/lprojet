<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');
        $type = $request->input('type', 'product'); // Default to product
        $categoryIds = $request->input('categories', []);
        $attributeFilters = $request->input('attributes', []);
        $conditionsFilter = $request->input('conditions', []);

        $results = collect();
        $categories = [];
        $attributes = [];

        // Initialize filtered collection
        $results = collect();
        $categories = [];
        $attributes = [];

        if ($type === 'user') {
            // User search logic remains same (only runs if query is present, or maybe user wants to browse all vendors?)
            // For now, let's keep user search requiring a query as listing all users might not be desired default behavior
            if ($query) {
                $results = User::where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->paginate(20)
                    ->appends(['query' => $query, 'type' => $type]);
            }
        } else {
            // Default to product search
            // Start with base query for products
            $productsQuery = Product::query()->with(['category', 'options.attribute']);

            // OPTIONAL: Filter by status 'approved' or 'active' if that's your logic (copied from HomeController)
            $productsQuery->where('status', 'approved'); // or 'approved' depending on your DB

            // Text search
            if ($query) {
                $productsQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhereHas('brand', function ($q) use ($query) {
                            $q->where('name', 'like', "%{$query}%");
                        })
                        ->orWhereHas('category', function ($q) use ($query) {
                            $q->where('name', 'like', "%{$query}%");
                        });
                });
            }

            // Category filter
            if (!empty($categoryIds)) {
                $productsQuery->whereIn('category_id', $categoryIds);
            }

            // Attribute filters
            if (!empty($attributeFilters)) {
                foreach ($attributeFilters as $attributeId => $optionIds) {
                    if (!empty($optionIds)) {
                        $productsQuery->whereHas('options', function ($q) use ($attributeId, $optionIds) {
                            $q->where('attribute_id', $attributeId)
                                ->whereIn('id', $optionIds);
                        });
                    }
                }
            }

            // Condition Filter
            if (!empty($conditionsFilter)) {
                $productsQuery->whereIn('condition', $conditionsFilter);
            }

            // Execute query
            $results = $productsQuery->latest()->paginate(20)
                ->appends(['query' => $query, 'type' => $type, 'categories' => $categoryIds, 'attributes' => $attributeFilters]);

            // Get all categories for filter sidebar
            $categories = \App\Models\Category::with('children')->whereNull('parent_id')->get();

            // Get attributes logic
            // Get attributes logic
            $allAttributes = [];
            if (!empty($categoryIds)) {
                $allAttributes = \App\Models\Attribute::whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                })->with('options')->get();
            } else {
                $allAttributes = \App\Models\Attribute::with('options')->get();
            }

            // Group Attributes
            $sizeAttributes = $allAttributes->filter(function ($attr) {
                return \Illuminate\Support\Str::startsWith($attr->code, 'size_group_') || $attr->code === 'sizes';
            });

            $colorAttribute = $allAttributes->firstWhere('type', 'color') ?? $allAttributes->firstWhere('code', 'colors');

            // Fetch distinct product conditions for filter sidebar
            $conditions = \App\Models\Product::whereNotNull('condition')->distinct()->pluck('condition');

            // Brands - assuming Brand model is primary source, we fetch them separately
            $brands = \App\Models\Brand::orderBy('name')->get();

            // Other attributes (excluding sizes, color)
            // Note: condition is now handled separately, so it's implicitly excluded from otherAttributes
            $otherAttributes = $allAttributes->reject(function ($attr) use ($sizeAttributes, $colorAttribute) {
                return $sizeAttributes->contains('id', $attr->id)
                    || ($colorAttribute && $attr->id === $colorAttribute->id);
            });
        }

        return view('search.results', compact(
            'results',
            'query',
            'type',
            'categories',
            'categoryIds',
            'attributeFilters',
            'conditionsFilter', // Pass the applied condition filters to the view
            'sizeAttributes',
            'colorAttribute',
            'conditions', // Pass all available conditions to the view
            'brands',
            'otherAttributes'
        ));
    }
    public function suggestions(Request $request)
    {
        $query = $request->input('query');
        $type = $request->input('type', 'product');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        if ($type === 'product') {
            // 1. Suggest Categories
            $categories = \App\Models\Category::where('name', 'like', "%{$query}%")
                ->limit(3)
                ->get()
                ->map(function ($cat) {
                    return [
                        'type' => 'category',
                        'label' => 'In ' . $cat->name,
                        'value' => $cat->id,
                        'url' => route('search', ['categories' => [$cat->id]])
                    ];
                });

            // 2. Suggest Products
            $products = Product::where('status', 'approved')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhereHas('brand', function ($q) use ($query) {
                            $q->where('name', 'like', "%{$query}%");
                        })
                        ->orWhereHas('category', function ($q) use ($query) {
                            $q->where('name', 'like', "%{$query}%");
                        });
                })
                ->limit(5)
                ->get()
                ->map(function ($product) {
                    return [
                        'type' => 'product',
                        'label' => $product->name,
                        'sub' => $product->price . ' MAD',
                        'image' => $product->getFeaturedImageUrl('preview'),
                        'url' => route('products.show', $product)
                    ];
                });

            $results = $categories->concat($products);
        } else {
            // Suggest Users
            $results = User::where('username', 'like', "%{$query}%")
                ->orWhere('first_name', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'type' => 'user',
                        'label' => $user->full_name,
                        'sub' => '@' . $user->username,
                        'image' => $user->profile_photo_url, // or helper method
                        'url' => route('vendor.show', $user)
                    ];
                });
        }

        return response()->json($results);
    }
}
