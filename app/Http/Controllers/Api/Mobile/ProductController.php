<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Notifications\ProductLikedNotification;
use ChristianKuri\LaravelFavorite\Models\Favorite;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /** Condition values shared by store & update, matching the web form. */
    private const CONDITIONS = 'new_with_tags,new_without_tags,very_good,good,satisfactory,heavily_worn';
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
            if ($request->boolean('include_subcategories')) {
                $ids = $this->descendantIds((int) $categoryId);
                $query->whereIn('category_id', $ids);
            } else {
                $query->where('category_id', $categoryId);
            }
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

        if ($optionIdsStr = $request->query('option_ids')) {
            $optionIds = array_filter(array_map('intval', explode(',', $optionIdsStr)));
            if (! empty($optionIds)) {
                $grouped = Option::whereIn('id', $optionIds)->get()->groupBy('attribute_id');
                foreach ($grouped as $attrOptionIds) {
                    $ids = $attrOptionIds->pluck('id')->toArray();
                    $query->whereHas('options', fn ($q) => $q->whereIn('options.id', $ids));
                }
            }
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
            ->with(['vendor' => fn ($q) => $q->withRating(), 'category', 'brand', 'media', 'options'])
            ->withCount('favorites')
            ->findOrFail($id);

        return response()->json(new ProductResource($product));
    }

    /**
     * Like / unlike a product (mirrors the web HomeController::toggleFavorite).
     * Sellers cannot like their own items.
     */
    public function toggleFavorite(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $product = Product::findOrFail($id);

        abort_if((int) $product->vendor_id === (int) $user->id, 422, 'Vous ne pouvez pas aimer votre propre article.');

        try {
            $product->toggleFavorite($user->id);
        } catch (UniqueConstraintViolationException) {
            // A concurrent request already inserted the row; treat as favorited.
        }

        $liked = $product->isFavorited($user->id);
        $count = $product->favorites()->count();

        // Keep the denormalised counter in sync without touching updated_at or firing model events.
        Product::query()->whereKey($product->id)->toBase()->update(['favorites_count' => $count]);

        if ($liked) {
            try {
                $product->vendor?->notify(new ProductLikedNotification($user, $product));
            } catch (\Throwable $e) {
                Log::error('ProductLikedNotification failed: '.$e->getMessage());
            }
        }

        return response()->json([
            'favorited' => $liked,
            'favorites_count' => $count,
        ]);
    }

    /**
     * The viewer's liked products ("Mes favoris"), most recently liked first.
     * Hidden and pending items are left out.
     */
    public function favorites(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        $products = Product::query()
            ->whereIn('status', ['approved', 'reserved', 'sold'])
            ->whereHas('favorites', fn ($q) => $q->where('user_id', $userId))
            ->with(['vendor', 'category', 'brand', 'media'])
            ->withCount('favorites')
            ->orderByDesc(
                Favorite::query()->select('created_at')
                    ->whereColumn('favoriteable_id', 'products.id')
                    ->where('favoriteable_type', (new Product())->getMorphClass())
                    ->where('user_id', $userId)
                    ->limit(1)
            )
            ->paginate(max(1, min((int) $request->query('per_page', 20), 50)));

        return ProductResource::collection($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'condition' => ['required', 'string', 'in:'.self::CONDITIONS],
            'size' => ['nullable', 'string'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'fabric' => ['nullable', 'array', 'max:2'],
            'fabric.*' => ['string', 'max:50'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:5120'],
            'options' => ['nullable', 'array'],
            'options.*' => ['integer', 'exists:options,id'],
        ]);

        $product = Product::create([
            ...collect($validated)->except('options', 'images')->toArray(),
            'description' => $validated['description'] ?? '', // column is NOT NULL
            'vendor_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        if (! empty($validated['options'] ?? [])) {
            $product->options()->sync($validated['options']);
        }

        if ($request->hasFile('images')) {
            foreach (array_values($request->file('images')) as $index => $image) {
                try {
                    // First image = cover (featured), the rest = gallery. Mirrors
                    // the web ItemController so the client-chosen order sets the cover.
                    $product->addMedia($image)->toMediaCollection($index === 0 ? 'featured' : 'products');
                } catch (\Exception $e) {
                    \Log::warning('Media upload failed for product '.$product->id.': '.$e->getMessage());
                }
            }
        }

        $product->load(['vendor', 'category', 'brand']);

        return response()->json(new ProductResource($product), 201);
    }

    /**
     * The authenticated seller's own listings (any status) for the
     * "My listings" screen and as the entry point to edit.
     */
    public function mine(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->where('vendor_id', $request->user()->id)
            ->with(['vendor', 'category', 'brand', 'media', 'options'])
            ->orderByDesc('created_at')
            ->paginate($request->query('per_page', 20));

        return ProductResource::collection($products);
    }

    /**
     * Brand list for the searchable brand picker (optionally filtered by ?q=).
     */
    public function brands(Request $request): JsonResponse
    {
        $q = $request->query('q');

        $brands = Brand::query()
            ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name']);

        return response()->json(['data' => $brands]);
    }

    /**
     * Update one of the seller's own products (create-form parity).
     *
     * Sent as multipart POST (PHP does not parse multipart PUT bodies). Mirrors
     * the web ItemController::update: only newly added images bump an approved
     * listing back to 'pending'; options are re-synced from the full id list.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $product = Product::findOrFail($id);

        if ((int) $product->vendor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'condition' => ['nullable', 'string', 'in:'.self::CONDITIONS],
            'size' => ['nullable', 'string'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'fabric' => ['nullable', 'array', 'max:2'],
            'fabric.*' => ['string', 'max:50'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:5120'],
            'options' => ['nullable', 'array'],
            'options.*' => ['integer', 'exists:options,id'],
            // Final photo order: "m:<media id>" = a photo already on the product,
            // "n:<i>" = the i-th file in images[]. First = cover; existing photos
            // left out are deleted. Absent (older app builds) = append only.
            'photo_order' => ['nullable', 'array', 'min:1', 'max:5'],
            'photo_order.*' => ['string', 'regex:/^(m|n):\d+$/'],
        ], [
            'photo_order.min' => 'Ajoutez au moins une photo.',
            'photo_order.max' => '5 photos maximum.',
        ]);

        $product->update(collect($validated)->except('options', 'images', 'photo_order')->toArray());

        if (array_key_exists('options', $validated)) {
            $product->options()->sync($validated['options'] ?? []);
        }

        if (! empty($validated['photo_order'])) {
            $addedImages = $this->applyPhotoOrder($product, $validated['photo_order'], array_values($request->file('images') ?? []));
            if ($addedImages && $product->status === 'approved') {
                $product->update(['status' => 'pending']);
            }
            $product->load(['vendor', 'category', 'brand', 'media']);

            return response()->json(new ProductResource($product));
        }

        $addedImages = false;
        if ($request->hasFile('images')) {
            foreach (array_values($request->file('images')) as $index => $image) {
                try {
                    // First image becomes the cover if none exists yet (mirror web).
                    $collection = ($index === 0 && $product->getMedia('featured')->isEmpty())
                        ? 'featured'
                        : 'products';
                    $product->addMedia($image)->toMediaCollection($collection);
                    $addedImages = true;
                } catch (\Exception $e) {
                    \Log::warning('Media upload failed for product '.$product->id.': '.$e->getMessage());
                }
            }
        }

        // New images require re-review (mirror web behaviour).
        if ($addedImages && $product->status === 'approved') {
            $product->update(['status' => 'pending']);
        }

        $product->load(['vendor', 'category', 'brand', 'media']);

        return response()->json(new ProductResource($product));
    }

    /**
     * A seller's other approved listings — the "Dressing du membre" section.
     */
    public function vendorProducts(Request $request, int $vendorId): AnonymousResourceCollection
    {
        $products = Product::query()
            ->where('vendor_id', $vendorId)
            ->where('status', 'approved')
            ->when($request->query('exclude'), fn ($q, $exclude) => $q->where('id', '!=', $exclude))
            ->with(['vendor', 'category', 'brand', 'media'])
            ->withCount('favorites')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * Similar approved products (same category) — the "Articles similaires" tab.
     */
    public function similarProducts(int $id): AnonymousResourceCollection
    {
        $product = Product::findOrFail($id);

        $base = fn () => Product::query()
            ->where('status', 'approved')
            ->where('id', '!=', $product->id)
            ->with(['vendor', 'category', 'brand', 'media'])
            ->withCount('favorites');

        // Same category first, then broaden to recent items so the tab is never empty.
        $products = $base()
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        if ($products->count() < 8) {
            $exclude = $products->pluck('id')->push($product->id)->all();
            $fill = $base()
                ->whereNotIn('id', $exclude)
                ->orderByDesc('created_at')
                ->limit(20 - $products->count())
                ->get();
            $products = $products->concat($fill);
        }

        return ProductResource::collection($products);
    }

    /**
     * Owner changes their listing's status (sold / reserved / hidden / approved).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $product = Product::findOrFail($id);

        if ((int) $product->vendor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,sold,reserved,hidden'],
        ]);

        $product->update(['status' => $validated['status']]);
        $product->load(['vendor', 'category', 'brand', 'media', 'options'])->loadCount('favorites');

        return response()->json(new ProductResource($product));
    }

    /**
     * Owner deletes their listing.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $product = Product::findOrFail($id);

        if ((int) $product->vendor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Article supprimé']);
    }

    /**
     * Report a product (logged for moderation).
     */
    public function report(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:100'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        \Log::info('Product reported', [
            'product_id' => $product->id,
            'reporter_id' => $request->user()->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => 'Merci, votre signalement a été envoyé.']);
    }

    /**
     * Apply the edit screen's photo order: upload the new files, delete the
     * existing photos that were removed, then make the first photo the cover
     * (`featured`) and the rest the gallery, in order. Returns whether new
     * photos were added (they send the listing back to review).
     *
     * @param  list<string>  $order  "m:<media id>" or "n:<index in $files>"
     * @param  list<UploadedFile>  $files
     */
    private function applyPhotoOrder(Product $product, array $order, array $files): bool
    {
        $existing = $product->media()->whereIn('collection_name', ['featured', 'products'])->get()->keyBy('id');

        $ordered = [];
        $added = false;
        foreach ($order as $token) {
            [$kind, $ref] = explode(':', $token);
            if ($kind === 'm') {
                if ($media = $existing->get((int) $ref)) {
                    $ordered[$media->id] = $media;
                }
            } elseif (isset($files[(int) $ref])) {
                try {
                    $media = $product->addMedia($files[(int) $ref])->toMediaCollection('products');
                    $ordered[$media->id] = $media;
                    $added = true;
                } catch (\Exception $e) {
                    Log::warning('Media upload failed for product '.$product->id.': '.$e->getMessage());
                }
            }
        }

        // Nothing usable (bad ids / failed uploads): keep the photos as they were.
        if ($ordered === []) {
            return $added;
        }

        foreach ($existing as $id => $media) {
            if (! isset($ordered[$id])) {
                $media->delete();
            }
        }

        $position = 0;
        foreach ($ordered as $media) {
            $media->collection_name = $position === 0 ? 'featured' : 'products';
            $media->order_column = $position + 1;
            $media->save();
            $position++;
        }

        return $added;
    }

    private function descendantIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $children = Category::where('parent_id', $categoryId)->pluck('id');
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->descendantIds($childId));
        }

        return $ids;
    }
}
