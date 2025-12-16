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

        $results = collect();
        $categories = [];
        $attributes = [];

        if ($query || !empty($categoryIds) || !empty($attributeFilters)) {
            if ($type === 'user') {
                $results = User::where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->paginate(20)
                    ->appends(['query' => $query, 'type' => $type]);
            } else {
                // Default to product search with filters
                $productsQuery = Product::query()->with(['category', 'options.attribute']);

                // Text search
                if ($query) {
                    $productsQuery->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
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

                $results = $productsQuery->paginate(20)
                    ->appends(['query' => $query, 'type' => $type, 'categories' => $categoryIds, 'attributes' => $attributeFilters]);

                // Get all categories for filter sidebar
                $categories = \App\Models\Category::with('children')->whereNull('parent_id')->get();

                // Get attributes for selected categories
                if (!empty($categoryIds)) {
                    $attributes = \App\Models\Attribute::whereHas('categories', function ($q) use ($categoryIds) {
                        $q->whereIn('categories.id', $categoryIds);
                    })->with('options')->get();
                } else {
                    // If no category selected, show all attributes
                    $attributes = \App\Models\Attribute::with('options')->get();
                }
            }
        }

        return view('search.results', compact('results', 'query', 'type', 'categories', 'attributes', 'categoryIds', 'attributeFilters'));
    }
}
