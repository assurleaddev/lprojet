<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // One row per search query submitted
        Schema::create('search_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('query', 255);
            $table->unsignedSmallInteger('result_count')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at', 'se_created_index');
        });

        // One row per result clicked after a search
        Schema::create('search_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('search_event_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedSmallInteger('position')->default(0); // rank position shown
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id', 'sc_product_index');
            $table->index('search_event_id', 'sc_search_event_index');

            $table->foreign('search_event_id')->references('id')->on('search_events')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_clicks');
        Schema::dropIfExists('search_events');
    }
};
