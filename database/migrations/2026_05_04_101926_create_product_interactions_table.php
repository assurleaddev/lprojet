<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // One row per product page view (detail page)
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 64)->nullable();
            $table->string('source', 32)->nullable(); // homepage, search, category, similar, profile
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'created_at'], 'pv_product_created_index');
            $table->index('created_at', 'pv_created_index');

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        // One row per click from a listing grid to the detail page
        Schema::create('product_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 64)->nullable();
            $table->string('source', 32)->nullable(); // homepage, search, similar
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'created_at'], 'pc_product_created_index');
            $table->index('created_at', 'pc_created_index');

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_clicks');
        Schema::dropIfExists('product_views');
    }
};
