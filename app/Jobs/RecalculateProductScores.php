<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RecalculateProductScores implements ShouldQueue
{
    use Queueable;

    /**
     * Recalculate the ranking score for every approved product.
     *
     * Formula (time-decayed):
     *   raw = (favorites_count × 3) + (orders_count × 10) + (clicks_count × 1) + (views_count × 0.1)
     *   score = raw / POW(hours_since_listed + 2, 1.5)
     *
     * The +2 prevents division by zero for brand-new listings and gives them a
     * small initial boost before any engagement data exists.
     */
    public function handle(): void
    {
        DB::statement("
            UPDATE products
            SET
                score = (
                    (favorites_count * 3)
                    + (orders_count * 10)
                    + (clicks_count * 1)
                    + (views_count * 0.1)
                ) / POW(
                    GREATEST(TIMESTAMPDIFF(HOUR, created_at, NOW()), 0) + 2,
                    1.5
                ),
                score_updated_at = NOW()
            WHERE status = 'approved'
        ");
    }
}
