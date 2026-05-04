<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('views_count')->default(0)->after('fabric');
            $table->unsignedInteger('clicks_count')->default(0)->after('views_count');
            $table->unsignedInteger('favorites_count')->default(0)->after('clicks_count');
            $table->unsignedInteger('orders_count')->default(0)->after('favorites_count');
            $table->decimal('score', 10, 4)->default(0)->after('orders_count');
            $table->timestamp('score_updated_at')->nullable()->after('score');

            $table->index(['status', 'score'], 'products_status_score_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_score_index');
            $table->dropColumn(['views_count', 'clicks_count', 'favorites_count', 'orders_count', 'score', 'score_updated_at']);
        });
    }
};
