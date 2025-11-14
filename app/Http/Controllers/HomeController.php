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
     * @return \Illuminate\Contracts\Support\Renderable
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
                ->latest()
                ->skip($offset)
                ->take($ajaxLoadSize)
                ->get();

            return view('layouts.partials._product_grid_items', ['products' => $products])->render();

        } else {
            // --- Initial Page Load Logic ---

            // Laravel's paginator is fine here for the first load.
            $products = Product::with(['category', 'options'])
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
        $user->load(['products', 'products.options', 'products.category', 'products.options']);
        // followers can be counted directly with withCount
        $user->loadCount('followers');

        // followings is polymorphic-by-type; count the model you care about
        $followingUsersCount = $user->followings()->count();

        return view('frontend.vendors.profile', [
            'user' => $user,
            'followingUsersCount' => $followingUsersCount,
        ]);
    }

    public function toggleFavorite(Request $request, Product $product)
    {
        // Toggle favorite for the authenticated user
        $product->toggleFavorite();

        // New state & fresh total
        $liked = $product->isFavorited();                     // for current user
        $count = $product->favoritedBy()->count();            // total users who favorited

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

        return response()->json([
            'following' => $me->isFollowing($user),
            'followers_count' => $user->followers()->count(),
        ]);
    }

    public function checkout(Product $product){
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
}
