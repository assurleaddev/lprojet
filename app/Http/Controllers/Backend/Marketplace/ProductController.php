<?php

namespace App\Http\Controllers\Backend\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MLib;
use App\Services\MediaLibraryService;


class ProductController extends Controller
{
    protected $mediaService;
    public function __construct(MediaLibraryService $mediaService)
    {
        $this->mediaService = $mediaService;
        // Apply permissions using Laravel's policy and middleware features
        $this->authorizeResource(Product::class, 'product');
    }

    public function index()
    {
        $products = Product::with('category')->latest()->paginate(20);
        return view('backend.marketplace.products.index', compact('products'));
    }
    public function getAttributesByCategory(Category $category)
    {
        $attributes = $category->inherited_attributes;
        // Ensure options are loaded (they were loaded in the accessor but good to be sure or formatted)
        // The accessor already loaded 'options'.

        return response()->json($attributes->values()->all());
    }
    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get(); // Better to get root categories
        // $attributes = Attribute::with('options')->get(); // No longer needed as we fetch dynamically
        $brands = \App\Models\Brand::orderBy('name')->get();
        return view('backend.marketplace.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'condition' => 'nullable|string',
            'size' => 'nullable|string',
            'images' => 'required|array|min:3|max:7',
            'images.*' => 'exists:media,id',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::create(array_merge(
                $request->except('images', 'options'),
                ['vendor_id' => Auth::id()]
            ));

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

            if ($request->has('images')) {
                foreach ($request->input('images') as $mediaId) {
                    $this->mediaService->associateExistingMedia($product, $mediaId, 'products');
                }
            }

            if ($request->filled('featured_image')) {
                $this->mediaService->associateExistingMedia(
                    $product,
                    $request->input('featured_image'),
                    'featured'
                );
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    // ...

    public function edit(Product $product)
    {
        // Eager load relations
        $product->load(['options']);
        // dd($product->getMedia('products'));
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();

        // $attributes = Attribute::with('options')->get(); // Not needed

        // Transform media for the component
        // $existingMedia = $product->getMedia('products')
        $existingMedia = $product->getMedia('products')->map(function ($media) use ($product) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(), // or getUrl('thumb') if you want thumbnails
                'alt' => $media->getCustomProperty('alt') ?? $product->name,
            ];
        })->toArray();

        // Retrieve featured image
        $featuredMedia = [];
        if ($media = $product->getFirstMedia('featured')) {
            $featuredMedia[] = [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'alt' => $media->getCustomProperty('alt') ?? $product->name,
            ];
        }

        // dd($existingMedia);
        return view('backend.marketplace.products.edit', compact(
            'product',
            'categories',
            'existingMedia',
            'featuredMedia',
            'brands'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'condition' => 'nullable|string',
            'size' => 'nullable|string',
            'images' => 'required|array|min:3|max:7',
            'images.*' => 'exists:media,id',
        ]);


        DB::transaction(function () use ($request, $product) {
            $product->fill($request->except('images', 'options'));
            // Explicitly set these if not in fillable or just to be safe, but they are fillable now.
            // Actually $request->except('images', 'options') covers name, description, price, category_id, brand_id, condition, size...
            $product->save();

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
            // Delete all old image records for this product


            // Handle Featured Image
            if ($request->boolean('remove_featured_image')) {
                $product->clearMediaCollection('featured');
            } elseif ($request->hasFile('featured_image')) {
                // New file upload
                $product->clearMediaCollection('featured');
                $product->addMediaFromRequest('featured_image')->toMediaCollection('featured');
            } elseif ($request->filled('featured_image')) {
                // Existing media ID provided
                $newFeaturedId = $request->input('featured_image');
                $currentFeatured = $product->getFirstMedia('featured');

                // Only update if it's different
                if (!$currentFeatured || $currentFeatured->id != $newFeaturedId) {
                    $product->clearMediaCollection('featured');
                    $this->mediaService->associateExistingMedia(
                        $product,
                        $newFeaturedId,
                        'featured'
                    );
                }
            }

            // Handle Product Gallery Images
            if ($request->has('images')) {
                $newImageIds = $request->input('images');
                $currentMedia = $product->getMedia('products');
                $currentMediaIds = $currentMedia->pluck('id')->toArray();

                // 1. Identify images to remove (present in current, missing in new)
                $toRemove = array_diff($currentMediaIds, $newImageIds);
                if (!empty($toRemove)) {
                    $product->media()->whereIn('id', $toRemove)->delete();
                }

                // 2. Identify images to add (present in new, missing in current)
                // Note: We don't filter by 'missing in current' solely because we might want to re-order, 
                // but Spatie Media Library doesn't support explicit ordering via simple sync easily without custom 'order_column'.
                // For now, let's just ensure we associate any that aren't currently associated.
                // However, since associateExistingMedia typically moves/copies, we must be careful.
                // If they are already associated, we don't need to re-associate TO THE SAME model.

                $toAdd = array_diff($newImageIds, $currentMediaIds);
                foreach ($toAdd as $mediaId) {
                    $this->mediaService->associateExistingMedia($product, $mediaId, 'products');
                }

                // Optional: Update order (if your MediaLibraryService or Model supports it)
                // ProductImage::setNewOrder($newImageIds); // If using a specific model with sorting trait

                // Manual ordering if needed by updating 'order_column'
                if (count($newImageIds) > 0) {
                    MLib::setNewOrder($newImageIds);
                }
            }
        });

        // Add a check after update to ensure the image count is still valid
        // $product->refresh();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }


    /**
     * Delete a single product image.
     */
    public function destroyImage(Request $request, ProductImage $image) // Add Request
    {
        $this->authorize('update', $image->product);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        // If the request is AJAX, return a JSON response
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        }

        return back()->with('success', 'Image deleted successfully.');
    }

    public function approve(Product $product)
    {
        $this->authorize('approve', $product);

        $product->update(['status' => 'approved']);

        return back()->with('success', 'Product approved successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}