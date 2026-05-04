<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RebuildUserInterests implements ShouldQueue
{
    use Queueable;

    // Weights per signal type
    private const W_VIEW = 0.5;
    private const W_CLICK = 1.0;
    private const W_FAV = 3.0;
    private const W_ORDER = 10.0;

    // Decay factor applied to existing score each run (20% decay per day)
    private const DECAY = 0.80;

    // Look-back window in days
    private const WINDOW_DAYS = 30;

    public function handle(): void
    {
        $window = now()->subDays(self::WINDOW_DAYS)->toDateTimeString();

        // Apply decay to all existing interest scores before adding new signals
        DB::table('user_interests')->update([
            'interest_score' => DB::raw('interest_score * ' . self::DECAY),
        ]);

        // Remove entries that have decayed to essentially zero
        DB::table('user_interests')->where('interest_score', '<', 0.01)->delete();

        $this->aggregateViews($window);
        $this->aggregateClicks($window);
        $this->aggregateFavorites($window);
        $this->aggregateOrders($window);
    }

    private function upsertInterest(int $userId, ?int $categoryId, ?int $brandId, float $delta): void
    {
        DB::table('user_interests')
            ->upsert(
                [['user_id' => $userId, 'category_id' => $categoryId, 'brand_id' => $brandId, 'interest_score' => $delta, 'updated_at' => now()]],
                ['user_id', 'category_id', 'brand_id'],
                ['interest_score' => DB::raw('user_interests.interest_score + ' . $delta), 'updated_at' => now()]
            );
    }

    private function aggregateViews(string $window): void
    {
        $rows = DB::table('product_views as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->select('pv.user_id', 'p.category_id', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('pv.user_id')
            ->where('pv.created_at', '>=', $window)
            ->groupBy('pv.user_id', 'p.category_id')
            ->get();

        foreach ($rows as $row) {
            $this->upsertInterest($row->user_id, $row->category_id, null, $row->cnt * self::W_VIEW);
        }
    }

    private function aggregateClicks(string $window): void
    {
        $rows = DB::table('product_clicks as pc')
            ->join('products as p', 'p.id', '=', 'pc.product_id')
            ->select('pc.user_id', 'p.category_id', 'p.brand_id', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('pc.user_id')
            ->where('pc.created_at', '>=', $window)
            ->groupBy('pc.user_id', 'p.category_id', 'p.brand_id')
            ->get();

        foreach ($rows as $row) {
            $this->upsertInterest($row->user_id, $row->category_id, null, $row->cnt * self::W_CLICK);
            if ($row->brand_id) {
                $this->upsertInterest($row->user_id, null, $row->brand_id, $row->cnt * self::W_CLICK);
            }
        }
    }

    private function aggregateFavorites(string $window): void
    {
        $rows = DB::table('favorites as f')
            ->join('products as p', 'p.id', '=', 'f.favoriteable_id')
            ->select('f.user_id', 'p.category_id', 'p.brand_id', DB::raw('COUNT(*) as cnt'))
            ->where('f.favoriteable_type', 'App\\Models\\Product')
            ->where('f.created_at', '>=', $window)
            ->groupBy('f.user_id', 'p.category_id', 'p.brand_id')
            ->get();

        foreach ($rows as $row) {
            $this->upsertInterest($row->user_id, $row->category_id, null, $row->cnt * self::W_FAV);
            if ($row->brand_id) {
                $this->upsertInterest($row->user_id, null, $row->brand_id, $row->cnt * self::W_FAV);
            }
        }
    }

    private function aggregateOrders(string $window): void
    {
        $rows = DB::table('orders as o')
            ->join('products as p', 'p.id', '=', 'o.product_id')
            ->select('o.user_id', 'p.category_id', 'p.brand_id', DB::raw('COUNT(*) as cnt'))
            ->whereIn('o.status', ['delivered', 'completed'])
            ->where('o.created_at', '>=', $window)
            ->groupBy('o.user_id', 'p.category_id', 'p.brand_id')
            ->get();

        foreach ($rows as $row) {
            $this->upsertInterest($row->user_id, $row->category_id, null, $row->cnt * self::W_ORDER);
            if ($row->brand_id) {
                $this->upsertInterest($row->user_id, null, $row->brand_id, $row->cnt * self::W_ORDER);
            }
        }
    }
}
