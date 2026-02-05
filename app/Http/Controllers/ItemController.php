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
    public function create(Request $request)
    {
        $categories = Category::whereNull('parent_id')->with('children.children')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $conditions = ['new_with_tags', 'new_without_tags', 'very_good', 'good', 'satisfactory', 'heavily_worn'];

        // Check if duplicating an existing product (Repost feature)
        $duplicateProduct = null;
        if ($request->has('duplicate')) {
            $duplicateProduct = Product::with(['options', 'media', 'brand'])
                ->where('id', $request->duplicate)
                ->where('vendor_id', auth()->id()) // Only allow duplicating own products
                ->first();
        }

        // Check for listed product in session (success modal)
        $listedProduct = null;
        if (session('listed_product_id')) {
            $listedProduct = Product::with(['brand', 'media'])->find(session('listed_product_id'));
        }

        return view('frontend.items.create', compact('categories', 'brands', 'conditions', 'duplicateProduct', 'listedProduct'));
    }

    public function getAttributes(Category $category)
    {
        $category->load('assignedAttributes.options');
        return response()->json($category->assignedAttributes->values()->all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'condition' => 'nullable|string',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::create([
                'name' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'condition' => $request->condition,
                'vendor_id' => Auth::id(),
                'status' => 'pending',
            ]);

            // Flatten nested options array structure from frontend
            $optionIds = [];
            if ($request->has('options')) {
                foreach ($request->options as $value) {
                    if (is_array($value)) {
                        $optionIds = array_merge($optionIds, $value);
                    } else {
                        $optionIds[] = $value;
                    }
                }
            }
            $product->options()->sync($optionIds);

            // Handle Duplicate Images (for Repost feature)
            if ($request->has('duplicate_images') && is_array($request->duplicate_images)) {
                foreach ($request->duplicate_images as $index => $imageUrl) {
                    try {
                        if ($index === 0) {
                            $product->addMediaFromUrl($imageUrl)->toMediaCollection('featured');
                        } else {
                            $product->addMediaFromUrl($imageUrl)->toMediaCollection('products');
                        }
                    } catch (\Exception $e) {
                        // Silently skip if image copy fails
                        \Log::warning("Failed to copy duplicate image: " . $e->getMessage());
                    }
                }
            }

            // Handle New Images Upload
            if ($request->hasFile('images')) {
                $images = $request->file('images');

                foreach ($images as $index => $image) {
                    // If duplicate images exist, offset the index
                    $actualIndex = $request->has('duplicate_images') ?
                        $index + count($request->duplicate_images) : $index;

                    if ($actualIndex === 0) {
                        $product->addMedia($image)->toMediaCollection('featured');
                    } else {
                        $product->addMedia($image)->toMediaCollection('products');
                    }
                }
            }

            DB::commit();

            session()->flash('success', 'Item listed successfully!');
            session()->flash('listed_product_id', $product->id);

            if ($request->wantsJson()) {
                // Prepare data for client-side modal
                return response()->json([
                    'message' => 'Item listed successfully!',
                    'product' => [
                        'image_url' => $product->getFirstMediaUrl('featured') ?: $product->getFirstMediaUrl('products'),
                        'name' => $product->name,
                        'brand_name' => $product->brand ? $product->brand->name : 'No Brand',
                        'condition_label' => $product->condition ? ucwords(str_replace('_', ' ', $product->condition)) : 'Pre-owned',
                        'price_formatted' => number_format($product->price, 2),
                    ],
                    'redirect_url' => route('items.create') // Used for "List another" reload
                ]);
            }

            return redirect()->route('items.create');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error listing item: ' . $e->getMessage());
        }
    }

    public function edit(Product $product)
    {
        // Ensure the user owns this product
        if (auth()->id() !== $product->vendor_id) {
            abort(403, 'Unauthorized action.');
        }

        // Load necessary relationships
        $product->load(['options', 'media']);

        $categories = Category::whereNull('parent_id')->with('children.children')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $conditions = ['new_with_tags', 'new_without_tags', 'very_good', 'good', 'satisfactory', 'heavily_worn'];

        // Get selected option IDs grouped by attribute
        $selectedOptions = $product->options->pluck('id')->toArray();

        return view('frontend.items.edit', compact('product', 'categories', 'brands', 'conditions', 'selectedOptions'));
    }

    public function update(Request $request, Product $product)
    {
        // Ensure the user owns this product
        if (auth()->id() !== $product->vendor_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'condition' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $product->update([
                'name' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'condition' => $request->condition,
                'status' => 'pending',
            ]);

            // Flatten nested options array structure from frontend
            $optionIds = [];
            if ($request->has('options')) {
                foreach ($request->options as $value) {
                    if (is_array($value)) {
                        $optionIds = array_merge($optionIds, $value);
                    } else {
                        $optionIds[] = $value;
                    }
                }
            }
            $product->options()->sync($optionIds);

            // Handle new images if uploaded
            if ($request->hasFile('images')) {
                $images = $request->file('images');

                foreach ($images as $index => $image) {
                    if ($index === 0 && $product->getMedia('featured')->isEmpty()) {
                        $product->addMedia($image)->toMediaCollection('featured');
                    } else {
                        $product->addMedia($image)->toMediaCollection('products');
                    }
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Item updated successfully!',
                    'redirect_url' => route('products.show', $product)
                ]);
            }

            return redirect()->route('home')->with('success', 'Item updated successfully! It is now pending review.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating item: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        // Ensure the user owns this product
        if (auth()->id() !== $product->vendor_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Delete product media
            $product->clearMediaCollection('featured');
            $product->clearMediaCollection('products');

            // Delete product
            $product->delete();

            return redirect()->route('home')->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    public function markAsSold(Product $product)
    {
        // Ensure the user owns this product
        if (auth()->id() !== $product->vendor_id) {
            abort(403, 'Unauthorized action.');
        }

        // Update product status to sold
        $product->update(['status' => 'sold']);

        return redirect()->route('products.show', $product)->with('success', 'Product marked as sold!');
    }

    public function reserve(Request $request, Product $product)
    {
        // Ensure the user owns this product
        if (auth()->id() !== $product->vendor_id) {
            abort(403, 'Unauthorized action.');
        }

        $buyerId = null;
        if ($request->filled('username')) {
            $user = \App\Models\User::where('username', $request->username)
                ->orWhere('first_name', $request->username) // Optional: also search first_name
                ->orWhere('email', $request->username)
                ->first();

            if (!$user) {
                return back()->with('error', 'User not found with that name or email.');
            }
            $buyerId = $user->id;
        }

        // Update product status to reserved
        $product->update([
            'status' => 'reserved',
            'buyer_id' => $buyerId
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Product marked as reserved!');
    }

    public function unreserve(Product $product)
    {
        // Ensure the user owns this product
        if (auth()->id() !== $product->vendor_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only unreserve if currently reserved
        if ($product->status !== 'reserved') {
            return back()->with('error', 'Product is not reserved.');
        }

        // Update product status back to approved and clear buyer_id
        $product->update([
            'status' => 'approved',
            'buyer_id' => null
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Reservation removed!');
    }
    public function hide(Product $product)
    {
        if (auth()->id() !== $product->vendor_id) {
            abort(403);
        }

        $product->update(['status' => 'hidden']);

        return back()->with('success', 'Product hidden successfully.');
    }
}
