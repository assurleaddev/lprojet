<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $products = Product::where('vendor_id', $userId)
            ->select('id', 'name', 'status', 'price', 'views_count', 'clicks_count', 'favorites_count', 'orders_count', 'score', 'created_at')
            ->orderByDesc('views_count')
            ->get();

        $totals = [
            'views'  => $products->sum('views_count'),
            'clicks' => $products->sum('clicks_count'),
            'likes'  => $products->sum('favorites_count'),
            'orders' => $products->sum('orders_count'),
        ];

        $selectedProductId = $request->input('product_id', $products->first()?->id);
        $selectedProduct   = $products->firstWhere('id', $selectedProductId);
        $chartData         = $selectedProductId ? $this->getChartData((int) $selectedProductId) : null;

        return view('frontend.seller.analytics', compact('products', 'totals', 'selectedProduct', 'chartData'));
    }

    private function getChartData(int $productId): array
    {
        $days = collect(range(29, 0))->map(fn ($d) => now()->subDays($d)->format('Y-m-d'));

        $views = DB::table('product_views')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('product_id', $productId)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupByRaw('DATE(created_at)')
            ->pluck('count', 'date');

        $clicks = DB::table('product_clicks')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('product_id', $productId)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupByRaw('DATE(created_at)')
            ->pluck('count', 'date');

        $favs = DB::table('favorites')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('favoriteable_id', $productId)
            ->where('favoriteable_type', 'App\\Models\\Product')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupByRaw('DATE(created_at)')
            ->pluck('count', 'date');

        return [
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('M d'))->values()->toArray(),
            'views'  => $days->map(fn ($d) => (int) $views->get($d, 0))->values()->toArray(),
            'clicks' => $days->map(fn ($d) => (int) $clicks->get($d, 0))->values()->toArray(),
            'likes'  => $days->map(fn ($d) => (int) $favs->get($d, 0))->values()->toArray(),
        ];
    }
}
