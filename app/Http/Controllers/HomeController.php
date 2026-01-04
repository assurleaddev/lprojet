<?php

declare(strict_types=1);

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use Modules\Chat\Models\Offer;
use Modules\Chat\Enums\OfferStatus;
use Illuminate\Support\Facades\Auth;
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
        // Define your page sizes
        $initialLoadSize = 25;
        $ajaxLoadSize = 5;

        if ($request->ajax()) {
            // --- AJAX Request Logic ---

            // Get the page number requested by the JavaScript
            $currentPage = $request->input('page', 1);

            // Manually calculate how many products to skip.
            // This is the key to preventing overlap.
            // Formula: initial size + (number of AJAX loads * ajax size)
            $offset = $initialLoadSize + (($currentPage - 2) * $ajaxLoadSize);

            $products = Product::with(['category', 'options'])
                ->where('status', 'approved')
                ->latest()
                ->skip($offset)
                ->take($ajaxLoadSize)
                ->get();

            return view('layouts.partials._product_grid_items', ['products' => $products])->render();

        } else {
            // --- Initial Page Load Logic ---

            // Laravel's paginator is fine here for the first load.
            $products = Product::with(['category', 'options'])
                ->where('status', 'approved')
                ->latest()
                ->paginate($initialLoadSize);

            return view('home', [
                'products' => $products
            ]);
        }
    }

    public function show(Product $product)
    {
        $product->load([
            'vendor' => function ($query) {
                $query->withCount('followers');
            },
            'vendor.products',
            'vendor.products.options',
            'vendor.products.category',
            'vendor.products.images',
            'category',
            'options',
            'images'
        ]);

        $similarProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with([
                'vendor',
                'vendor.products',
                'vendor.products.options',
                'vendor.products.category',
                'vendor.products.images',
                'category',
                'options',
                'images'
            ])
            ->take(20)
            ->get();
        return view('frontend.products.show', [
            'product' => $product,
            'similarProducts' => $similarProducts,
        ]);
    }

    public function member_profile(User $user)
    {
        $user->load(['products', 'products.options', 'products.category', 'products.options', 'receivedReviews.author']);
        // followers can be counted directly with withCount
        $user->loadCount('followers');

        // followings is polymorphic-by-type; count the model you care about
        $followingUsersCount = $user->followings()->count();

        $reviews = $user->receivedReviews()->with('author')->latest()->get();
        \Log::info('Reviews for user ' . $user->id . ': ' . $reviews->count());

        // Calculate Stats
        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0 ? $reviews->avg('rating') : 0;

        $autoReviews = $reviews->filter(function ($review) {
            return str_contains($review->review, 'Auto-feedback');
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
            ]
        ]);
    }

    public function toggleFavorite(Request $request, Product $product)
    {
        // Toggle favorite for the authenticated user
        $product->toggleFavorite();

        // New state & fresh total
        $liked = $product->isFavorited();                     // for current user
        $count = $product->favoritedBy()->count();            // total users who favorited

        if ($liked) {
            $product->vendor->notify(new \App\Notifications\ProductLikedNotification(Auth::user(), $product));
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
            $user->notify(new \App\Notifications\NewFollowerNotification($me));
        }

        return response()->json([
            'following' => $isFollowing,
            'followers_count' => $user->followers()->count(),
        ]);
    }

    public function checkout(Product $product)
    {
        return view('frontend.products.checkout', [
            'product' => $product,
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
        $priceToPay = $offer->offer_price;

        // --- Display View ---
        // Ensure 'frontend.products.checkout' view exists
        return view('frontend.products.checkout', [
            'product' => $product,
            'checkoutPrice' => $priceToPay, // Use this variable in the view
            'offer' => $offer
        ]);
    }

    public function followers(User $user)
    {
        $followers = $user->followers()->paginate(20);
        return view('frontend.vendors.followers', compact('user', 'followers'));
    }

    public function following(User $user)
    {
        $following = $user->followings()->paginate(20);
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
