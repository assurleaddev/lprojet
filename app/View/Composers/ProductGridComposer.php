<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;

class ProductGridComposer
{
    public function compose(View $view): void
    {
        try {
            $members = Redis::smembers('ranking:trending_ids');
            // smembers returns string IDs; cast to int for in_array strict checks
            $trendingProductIds = array_map('intval', $members ?: []);
        } catch (\Exception $e) {
            // Redis unavailable — degrade gracefully, no badges shown
            Log::warning('Unable to read trending products from Redis: ' . $e->getMessage());

            $trendingProductIds = [];
        }

        $view->with('trendingProductIds', $trendingProductIds);
    }
}
