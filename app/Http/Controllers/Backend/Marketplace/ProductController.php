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
        // Eager load the attributes and their options
        $category->load('attributes.options');

        return response()->json($category->attributes);
    }
    public function create()
    {
        $categories = Category::all();
        $attributes = Attribute::with('options')->get(); // We'll handle this dynamically later
        return view('backend.marketplace.products.create', compact('categories', 'attributes'));
    }


    public function approve(Product $product)
    {
        // Check if the user has permission
        $this->authorize('approve', $product);

        // Update the product status
        $product->update(['status' => 'approved']);

        // Redirect back with a success message
        return back()->with('success', 'Product has been approved.');
    }
    public function store(Request $request)
    {
        // dd( MLib::whereIn('id', $request->input('images'))->get());
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'options' => 'nullable|array',
            'images' => 'required|array|min:3|max:7',
            'images.*' => 'exists:media,id',
        ]);
        DB::transaction(function () use ($request) {

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'vendor_id' => Auth::id(),
                'status' => Auth::user()->can('products.approve') ? 'approved' : 'pending',
            ]);

            if ($request->has('options')) {
                $product->options()->sync($request->options);
            }


            if ($request->hasFile('featured_image')) {
                $product->addMediaFromRequest('featured_image')->toMediaCollection('featured');
            } elseif (!empty($request->input('featured_image'))) {
                $this->mediaService->associateExistingMedia($product, $request->input('featured_image'), 'featured');
            }

            foreach ($request->input('images') as $mediaId) {
                $this->mediaService->associateExistingMedia($product, $mediaId, 'products');
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        // Eager load relations
        $product->load(['options']);
        // dd($product->getMedia('products'));
        $categories = Category::whereNull('parent_id')->get();
        $attributes = Attribute::with('options')->get();

        // Transform media for the component
        // $existingMedia = $product->getMedia('products')
        $existingMedia = $product->getMedia('products')->map(function ($media) use ($product) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(), // or getUrl('thumb') if you want thumbnails
                'alt' => $media->getCustomProperty('alt') ?? $product->name,
            ];
        })->toArray();
        // dd($existingMedia);
        return view('backend.marketplace.products.edit', compact(
            'product',
            'categories',
            'attributes',
            'existingMedia'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'images' => 'required|array|min:3|max:7',
            'images.*' => 'exists:media,id',
        ]);


        DB::transaction(function () use ($request, $product) {
            $product->update($request->except('images', 'options'));

            $product->options()->sync($request->options ?? []);
            // Delete all old image records for this product


            if ($request->boolean('remove_featured_image')) {
                $product->clearMediaCollection('featured');
            }
            // New upload provided
            elseif ($request->hasFile('featured_image')) {
                $product->clearMediaCollection('featured');
                $product->addMediaFromRequest('featured_image')->toMediaCollection('featured');
            }
            // Existing media id provided (same behavior as in store())
            elseif ($request->filled('featured_image')) {
                $product->clearMediaCollection('featured');
                $this->mediaService->associateExistingMedia(
                    $product,
                    $request->input('featured_image'),
                    'featured'
                );
            }

            if ($request->has('images')) {
                $product->clearMediaCollection('products');
                foreach ($request->input('images') as $mediaId) {
                    $this->mediaService->associateExistingMedia($product, $mediaId, 'products');
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
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}