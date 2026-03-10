<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->string('parcel_size')->nullable()->after('status'); // small, medium, large
        });

        Schema::table('chat_offers', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->string('parcel_size')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->dropColumn('parcel_size');
        });

        Schema::table('chat_offers', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->dropColumn('parcel_size');
        });
    }
};
