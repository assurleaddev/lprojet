<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RecalculateSellerScores implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // completed_orders × 5 + follower_count × 0.5 − cancelled_orders × 3, floored at 0
        DB::statement("
            UPDATE users u
            SET seller_score = GREATEST(
                (
                    SELECT COUNT(*) FROM orders o
                    WHERE o.vendor_id = u.id
                      AND o.status IN ('delivered', 'completed')
                ) * 5
                +
                (
                    SELECT COUNT(*) FROM followables f
                    WHERE f.followable_id = u.id
                      AND f.followable_type = 'App\\\\Models\\\\User'
                ) * 0.5
                -
                (
                    SELECT COUNT(*) FROM orders o
                    WHERE o.vendor_id = u.id
                      AND o.status = 'cancelled'
                ) * 3,
                0
            )
        ");
    }
}
