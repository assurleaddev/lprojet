<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $conditions = ['New with tags', 'New without tags', 'Very good', 'Good', 'Satisfactory'];

        return view('frontend.items.create', compact('categories', 'conditions'));
    }

    public function getAttributes(Category $category)
    {
        $category->load('attributes.options');
        return response()->json($category->attributes);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Item Store Request', $request->all());
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                \Illuminate\Support\Facades\Log::info('File: ' . $file->getClientOriginalName() . ' Size: ' . $file->getSize() . ' Error: ' . $file->getError());
            }
        } else {
            \Illuminate\Support\Facades\Log::info('No images found in request.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
        ]);

        DB::beginTransaction();

        try {
            $product = Product::create([
                'name' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'vendor_id' => Auth::id(),
                'status' => 'pending',
            ]);

            // Handle Attributes (Brand, Size, Condition, etc.)
            // This part depends on how attributes are stored. 
            // I'll assume a simple attachment for now or just basic fields if they were on the product table.
            // Since Product model has `options` relationship, we might need to attach options.
            // For this MVP, I'll focus on the core product and images.

            // Handle Images
            if ($request->hasFile('images')) {
                $images = $request->file('images');

                // The images array should be in the order they were submitted (reordered by JS)
                foreach ($images as $index => $image) {
                    if ($index === 0) {
                        $product->addMedia($image)->toMediaCollection('featured');
                    } else {
                        $product->addMedia($image)->toMediaCollection('products');
                    }
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Item listed successfully!',
                    'redirect_url' => route('products.show', $product)
                ]);
            }

            return redirect()->route('products.show', $product)->with('success', 'Item listed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error listing item: ' . $e->getMessage());
        }
    }
}
