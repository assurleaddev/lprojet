<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserInterest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use Modules\Chat\Models\Offer;
use Modules\Chat\Enums\OfferStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable|string
     */
    public function index(Request $request)
    {
        $initialLoadSize = 25;
        $ajaxLoadSize = 5;

        $query = $this->buildFeedQuery();

        if ($request->ajax()) {
            $currentPage = $request->input('page', 1);
            $offset = $initialLoadSize + (($currentPage - 2) * $ajaxLoadSize);

            $products = $query->skip($offset)->take($ajaxLoadSize)->get();

            return view('layouts.partials._product_grid_items', ['products' => $products])->render();
        }

        $products = $query->paginate($initialLoadSize);

        return view('home', [
            'products' => $products,
        ]);
    }

    private function buildFeedQuery()
    {
        $query = Product::with(['category', 'options'])
            ->where('status', 'approved');

        // Personalized feed for authenticated users, global score for guests
        if (Auth::check()) {
            $userId = Auth::id();

            $topCategories = UserInterest::where('user_id', $userId)
                ->whereNotNull('category_id')
                ->orderBy('interest_score', 'desc')
                ->take(5)
                ->pluck('interest_score', 'category_id');

            $topBrands = UserInterest::where('user_id', $userId)
                ->whereNotNull('brand_id')
                ->orderBy('interest_score', 'desc')
                ->take(5)
                ->pluck('interest_score', 'brand_id');

            $followedIds = Auth::user()->followings()->pluck('followable_id')->toArray();

            if ($topCategories->isNotEmpty() || $topBrands->isNotEmpty() || ! empty($followedIds)) {
                // Build CASE WHEN boosts inline — avoids a separate subquery
                $catIds = $topCategories->keys()->toArray();
                $brandIds = $topBrands->keys()->toArray();

                $catIds = array_map('intval', $catIds ?: [0]);
                $brandIds = array_map('intval', $brandIds ?: [0]);
                $sellerIds = array_map('intval', $followedIds ?: [0]);

                $catPlaceholders = implode(',', array_fill(0, count($catIds), '?'));
                $brandPlaceholders = implode(',', array_fill(0, count($brandIds), '?'));
                $sellerPlaceholders = implode(',', array_fill(0, count($sellerIds), '?'));

                $query->selectRaw("
                    products.*,
                    (
                        score
                        + CASE WHEN category_id IN ({$catPlaceholders}) THEN 2 ELSE 0 END
                        + CASE WHEN brand_id    IN ({$brandPlaceholders}) THEN 1.5 ELSE 0 END
                        + CASE WHEN vendor_id   IN ({$sellerPlaceholders}) THEN 3 ELSE 0 END
                    ) AS personalized_score
                ", array_merge($catIds, $brandIds, $sellerIds))
                ->orderByRaw('personalized_score DESC, created_at DESC');

                return $query;
            }
        }

        // Default: global score
        return $query->orderBy('score', 'desc')->orderBy('created_at', 'desc');
    }

    public function show(Product $product)
    {
        if (in_array($product->status, ['sold', 'pending']) && auth()->id() !== $product->vendor_id) {
            return response()->view('errors.product_unavailable', [], 404);
        }

        $product->load([
            'vendor' => function ($query) {
                $query->withCount('followers');
            },
            'vendor.products' => function ($query) use ($product) {
                // Filter member's other items
                $query->where('status', 'approved')
                    ->where('id', '!=', $product->id) // Optional: exclude current product from the "more from user" list
                    ->latest()
                    ->take(10); // Limit to reasonable amount
            },
            'vendor.products.options',
            'vendor.products.category',
            'vendor.products.images',
            'vendor.products.images',
            'category',
            'options',
            'options.attribute',
            'images',
        ]);

        $similarProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'approved')
            ->with(['vendor', 'category', 'options', 'images'])
            ->orderBy('score', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        // Collaborative filtering: products co-favorited by users who also liked this one
        $coFavCounts = DB::table('favorites as f2')
            ->select('f2.favoriteable_id', DB::raw('COUNT(*) as co_fav_count'))
            ->where('f2.favoriteable_type', 'App\\Models\\Product')
            ->where('f2.favoriteable_id', '!=', $product->id)
            ->whereIn('f2.user_id', function ($sub) use ($product) {
                $sub->select('user_id')
                    ->from('favorites')
                    ->where('favoriteable_id', $product->id)
                    ->where('favoriteable_type', 'App\\Models\\Product');
            })
            ->groupBy('f2.favoriteable_id')
            ->orderByDesc('co_fav_count')
            ->take(8)
            ->pluck('co_fav_count', 'favoriteable_id');

        $youMightLike = Product::whereIn('id', $coFavCounts->keys())
            ->where('status', 'approved')
            ->with(['category', 'options'])
            ->get()
            ->sortByDesc(fn ($p) => $coFavCounts[$p->id] ?? 0)
            ->values();

        // Build Breadcrumbs
        $breadcrumbs = [];
        $currentCategory = $product->category;

        while ($currentCategory) {
            $breadcrumbs[] = $currentCategory;
            $currentCategory = $currentCategory->parent; // Assuming 'parent' relationship works
        }
        $breadcrumbs = array_reverse($breadcrumbs);

        $deliveryFeeFixed = config('settings.delivery_fee_fixed', 25.00);
        $buyerProtectionPercentage = (float) config('settings.buyer_protection_fee_percentage', 5);
        $buyerProtectionFixed = (float) config('settings.buyer_protection_fee_fixed', 0.70);
        $protectionFee = ($product->price * $buyerProtectionPercentage / 100) + $buyerProtectionFixed;

        try {
            $members = \Illuminate\Support\Facades\Redis::smembers('ranking:trending_ids');
            $trendingProductIds = array_map('intval', $members ?: []);
        } catch (\Exception) {
            $trendingProductIds = [];
        }

        return view('frontend.products.show', [
            'product' => $product,
            'similarProducts' => $similarProducts,
            'youMightLike' => $youMightLike,
            'trendingProductIds' => $trendingProductIds,
            'breadcrumbs' => $breadcrumbs,
            'deliveryFeeFixed' => $deliveryFeeFixed,
            'protectionFee' => $protectionFee,
        ]);
    }

    public function member_profile(User $user)
    {
        $user->load(['products', 'products.options', 'products.category', 'products.options', 'receivedReviews.author']);
        // followers can be counted directly with withCount
        $user->loadCount('followers');

        // followings is polymorphic-by-type; count the model you care about
        $followingUsersCount = $user->followings()->count();

        $reviews = $user->receivedReviews()
            ->whereNull('parent_id')
            ->with(['author', 'reply.author'])
            ->latest()
            ->get();

        // Calculate Stats
        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0 ? $reviews->avg('rating') : 0;

        $autoReviews = $reviews->filter(function ($review) {
            return $review->is_auto || str_contains(strtolower($review->review), 'auto-feedback');
        });
        $memberReviews = $reviews->diff($autoReviews);

        $memberCount = $memberReviews->count();
        $memberAvg = $memberCount > 0 ? $memberReviews->avg('rating') : 0;

        $autoCount = $autoReviews->count();
        $autoAvg = $autoCount > 0 ? $autoReviews->avg('rating') : 0;

        // Check for pending review for the authenticated user visiting this profile
        $pendingReviewOrder = null;
        if (Auth::check() && Auth::id() !== $user->id) {
            $pendingReviewOrder = \App\Models\Order::where('vendor_id', $user->id)
                ->where('user_id', Auth::id())
                ->where('status', 'delivered')
                ->where('received_at', '>', now()->subHours(48)) // Within 48h window
                ->latest()
                ->first();

            // Manual check if relationship doesn't exist to be safe
            if ($pendingReviewOrder) {
                $alreadyReviewed = \App\Models\Review::where('author_id', Auth::id())
                    ->where('model_id', $user->id)
                    ->where('model_type', \App\Models\User::class)
                    ->where('created_at', '>', $pendingReviewOrder->created_at)
                    ->exists();

                if ($alreadyReviewed) {
                    $pendingReviewOrder = null;
                }
            }
        }

        return view('frontend.vendors.profile', [
            'user' => $user,
            'followingUsersCount' => $followingUsersCount,
            'reviews' => $reviews,
            'pendingReviewOrder' => $pendingReviewOrder,
            'stats' => [
                'total' => $totalReviews,
                'avg' => $averageRating,
                'member_count' => $memberCount,
                'member_avg' => $memberAvg,
                'auto_count' => $autoCount,
                'auto_avg' => $autoAvg,
            ],
        ]);
    }

    public function trackClick(Request $request, Product $product): \Illuminate\Http\JsonResponse
    {
        // Skip tracking for the product owner
        if ($request->user()?->id === $product->vendor_id) {
            return response()->json(['ok' => true]);
        }

        $source = $request->input('source', 'homepage');

        \Illuminate\Support\Facades\DB::table('product_clicks')->insert([
            'product_id' => $product->id,
            'user_id' => $request->user()?->id,
            'session_id' => substr(session()->getId(), 0, 64),
            'source' => $source,
            'created_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('products')
            ->where('id', $product->id)
            ->increment('clicks_count');

        return response()->json(['ok' => true]);
    }

    public function toggleFavorite(Request $request, Product $product)
    {
        // Prevent self-like
        if (Auth::id() === $product->vendor_id) {
            abort(422, 'You cannot like your own product.');
        }

        // Toggle favorite — catch duplicate key from rapid double-clicks
        try {
            $product->toggleFavorite();
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Concurrent request already inserted the row; treat as favorited
        }

        // New state & fresh total
        $liked = $product->isFavorited();                     // for current user
        $count = $product->favoritedBy()->count();            // total users who favorited

        // Keep denormalized counter in sync
        $product->timestamps = false;
        $product->favorites_count = $count;
        $product->save();
        $product->timestamps = true;

        if ($liked) {
            try {
                $product->vendor->notify(new \App\Notifications\ProductLikedNotification(Auth::user(), $product));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('ProductLikedNotification failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'liked' => $liked,
            'count' => $count,
        ]);
    }

    public function toggleFollow(Request $request, User $user)
    {
        $me = $request->user();

        abort_if($me->is($user), 422, 'You cannot follow yourself.');

        $me->isFollowing($user) ? $me->unfollow($user) : $me->follow($user);

        $isFollowing = $me->isFollowing($user);

        if ($isFollowing) {
            try {
                $user->notify(new \App\Notifications\NewFollowerNotification($me));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('NewFollowerNotification failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'following' => $isFollowing,
            'followers_count' => $user->followers()->count(),
        ]);
    }

    public function checkout(Product $product)
    {
        if (in_array($product->status, ['sold', 'pending'])) {
            return response()->view('errors.product_unavailable', [], 404);
        }

        $addresses = Auth::user()->addresses;

        if ($addresses->isEmpty()) {
            return view('frontend.products.no-address', [
                'checkoutUrl' => route('product.checkout', $product),
            ]);
        }
        $allShippingOptions = \App\Models\ShippingOption::where('is_active', true)->get();

        // Filter options based on Vendor preferences
        $shippingOptions = $allShippingOptions->filter(function ($option) use ($product) {
            // Default to enabled ('1') if not set
            return $product->vendor->getMeta($option->key, '1') !== '0';
        });

        // If seller has no shipping options, use admin's default shipping options
        if ($shippingOptions->isEmpty()) {
            $defaultShippingIds = json_decode(config('settings.default_shipping_options', '[]'), true) ?? [];
            if (! empty($defaultShippingIds)) {
                $shippingOptions = $allShippingOptions->whereIn('id', $defaultShippingIds)->values();
                \Log::info('Using default shipping options', [
                    'default_ids' => $defaultShippingIds,
                    'filtered_count' => $shippingOptions->count(),
                    'options' => $shippingOptions->pluck('id', 'label')->toArray(),
                ]);
            }
        }

        // Fee Settings
        $buyerProtectionPercentage = config('settings.buyer_protection_fee_percentage', 5);
        $buyerProtectionFixed = config('settings.buyer_protection_fee_fixed', 0.70);
        $deliveryFeeFixed = config('settings.delivery_fee_fixed', 25.00);

        // Calculate default protection fee
        $protectionFee = ((float) $product->price * ((float) $buyerProtectionPercentage / 100)) + (float) $buyerProtectionFixed;

        $verificationThreshold = (float) config('settings.product_verification_threshold', 500);
        $verificationFee = (float) config('settings.product_verification_fee', 50);

        return view('frontend.products.checkout', [
            'product' => $product,
            'isBundle' => false,
            'addresses' => $addresses,
            'shippingOptions' => $shippingOptions,
            'protectionFee' => $protectionFee,
            'deliveryFeeFixed' => $deliveryFeeFixed,
            'verificationThreshold' => $verificationThreshold,
            'verificationFee' => $verificationFee,
        ]);
    }

    public function offerCheckout(Request $request, Offer $offer)
    {
        // --- Authorization ---
        if ($offer->status !== OfferStatus::Accepted) {
            abort(404, 'Offer not accepted.');
        }
        if ($offer->buyer_id !== Auth::id()) {
            abort(403, 'You did not make this offer.');
        }

        // --- Load Data ---
        $product = $offer->product;
        $items = $offer->items;

        if (! $product && $items->isNotEmpty()) {
            $product = $items->first()->product;
            $isBundle = true;
        } else {
            $isBundle = false;
        }

        if (! $product || in_array($product->status, ['sold', 'pending'])) {
            return response()->view('errors.product_unavailable', [], 404);
        }

        $priceToPay = $offer->offer_price;
        $addresses = Auth::user()->addresses;
        $allShippingOptions = \App\Models\ShippingOption::where('is_active', true)->get();

        // Filter options based on Vendor preferences
        $shippingOptions = $allShippingOptions->filter(function ($option) use ($product) {
            return $product->vendor->getMeta($option->key, '1') !== '0';
        });

        // If vendor has no configured options, fall back to admin defaults
        if ($shippingOptions->isEmpty()) {
            $defaultShippingIds = json_decode(config('settings.default_shipping_options', '[]'), true) ?? [];
            if (! empty($defaultShippingIds)) {
                $shippingOptions = $allShippingOptions->whereIn('id', $defaultShippingIds)->values();
            }
        }

        // Fee Settings
        $buyerProtectionPercentage = config('settings.buyer_protection_fee_percentage', 5);
        $buyerProtectionFixed = config('settings.buyer_protection_fee_fixed', 0.70);
        $deliveryFeeFixed = config('settings.delivery_fee_fixed', 25.00);

        // Calculate default protection fee
        $protectionFee = ((float) $priceToPay * ((float) $buyerProtectionPercentage / 100)) + (float) $buyerProtectionFixed;

        $verificationThreshold = (float) config('settings.product_verification_threshold', 500);
        $verificationFee = (float) config('settings.product_verification_fee', 50);

        return view('frontend.products.checkout', [
            'product' => $product,
            'isBundle' => $isBundle,
            'offer' => $offer,
            'checkoutPrice' => $priceToPay,
            'addresses' => $addresses,
            'shippingOptions' => $shippingOptions,
            'protectionFee' => $protectionFee,
            'deliveryFeeFixed' => $deliveryFeeFixed,
            'verificationThreshold' => $verificationThreshold,
            'verificationFee' => $verificationFee,
        ]);
    }

    public function followers(User $user)
    {
        $followers = $user->followers()->paginate(20);
        return view('frontend.vendors.followers', compact('user', 'followers'));
    }

    public function following(User $user)
    {
        $following = $user->followings()->with('followable')->paginate(20);
        return view('frontend.vendors.following', compact('user', 'following'));
    }

    public function favorites()
    {
        $favorites = Auth::user()->favorite(Product::class); // Returns a Collection

        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;

        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $favorites->forPage($page, $perPage),
            $favorites->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('frontend.products.favorites', compact('products'));
    }
}
