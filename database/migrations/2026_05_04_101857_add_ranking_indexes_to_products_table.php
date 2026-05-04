<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'products_status_created_at_index');
            $table->index(['status', 'price'], 'products_status_price_index');
            $table->index(['category_id', 'status'], 'products_category_status_index');
            $table->index(['brand_id', 'status'], 'products_brand_status_index');
        });

        Schema::table('product_option', function (Blueprint $table) {
            $table->index('option_id', 'product_option_option_id_index');
        });

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->index(['favoriteable_id', 'favoriteable_type'], 'favorites_favoriteable_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_created_at_index');
            $table->dropIndex('products_status_price_index');
            $table->dropIndex('products_category_status_index');
            $table->dropIndex('products_brand_status_index');
        });

        Schema::table('product_option', function (Blueprint $table) {
            $table->dropIndex('product_option_option_id_index');
        });

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropIndex('favorites_favoriteable_index');
            });
        }
    }
};
