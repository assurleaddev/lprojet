<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        // Backfill favorites_count from existing favorites rows
        DB::statement("
            UPDATE products p
            SET favorites_count = (
                SELECT COUNT(*)
                FROM favorites f
                WHERE f.favoriteable_id = p.id
                  AND f.favoriteable_type = 'App\\\\Models\\\\Product'
            )
        ");

        // Backfill orders_count from completed orders (exclude cancelled)
        DB::statement("
            UPDATE products p
            SET orders_count = (
                SELECT COUNT(*)
                FROM orders o
                WHERE o.product_id = p.id
                  AND o.status NOT IN ('cancelled')
            )
        ");
    }

    public function down(): void
    {
        DB::statement("UPDATE products SET favorites_count = 0, orders_count = 0");
    }
};
