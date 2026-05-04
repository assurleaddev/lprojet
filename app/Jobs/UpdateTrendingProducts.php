<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UpdateTrendingProducts implements ShouldQueue
{
    use Queueable;

    private const TRENDING_LIMIT = 50;
    private const TTL_SECONDS = 7200; // 2 hours — job runs every 30 min
    private const W_VIEW = 1;
    private const W_FAV = 5;
    private const W_ORDER = 15;

    public function handle(): void
    {
        $window = now()->subHours(24)->toDateTimeString();

        $views = DB::table('product_views')
            ->select('product_id', DB::raw('COUNT(*) as cnt'))
            ->where('created_at', '>=', $window)
            ->groupBy('product_id')
            ->pluck('cnt', 'product_id');

        $favs = DB::table('favorites')
            ->select('favoriteable_id', DB::raw('COUNT(*) as cnt'))
            ->where('favoriteable_type', 'App\\Models\\Product')
            ->where('created_at', '>=', $window)
            ->groupBy('favoriteable_id')
            ->pluck('cnt', 'favoriteable_id');

        $orders = DB::table('orders')
            ->select('product_id', DB::raw('COUNT(*) as cnt'))
            ->where('created_at', '>=', $window)
            ->where('status', '!=', 'cancelled')
            ->groupBy('product_id')
            ->pluck('cnt', 'product_id');

        $productIds = $views->keys()
            ->merge($favs->keys())
            ->merge($orders->keys())
            ->unique();

        if ($productIds->isEmpty()) {
            Redis::del('ranking:trending', 'ranking:trending_ids');

            return;
        }

        $scores = $productIds->mapWithKeys(function ($id) use ($views, $favs, $orders) {
            return [$id => ($views->get($id, 0) * self::W_VIEW)
                         + ($favs->get($id, 0) * self::W_FAV)
                         + ($orders->get($id, 0) * self::W_ORDER)];
        });

        // Only keep currently approved products
        $approvedIds = DB::table('products')
            ->whereIn('id', $scores->keys()->toArray())
            ->where('status', 'approved')
            ->pluck('id')
            ->flip();

        $top = $scores
            ->filter(fn ($s, $id) => isset($approvedIds[$id]))
            ->sortDesc()
            ->take(self::TRENDING_LIMIT);

        $pipeline = Redis::pipeline();
        $pipeline->del('ranking:trending', 'ranking:trending_ids');

        $zArgs = [];
        foreach ($top as $id => $score) {
            $zArgs[] = $score;
            $zArgs[] = (string) $id;
        }
        if (! empty($zArgs)) {
            $pipeline->zadd('ranking:trending', ...$zArgs);
        }

        $ids = $top->keys()->map(fn ($id) => (string) $id)->toArray();
        if (! empty($ids)) {
            $pipeline->sadd('ranking:trending_ids', ...$ids);
        }

        $pipeline->expire('ranking:trending', self::TTL_SECONDS);
        $pipeline->expire('ranking:trending_ids', self::TTL_SECONDS);

        $pipeline->execute();
    }
}
