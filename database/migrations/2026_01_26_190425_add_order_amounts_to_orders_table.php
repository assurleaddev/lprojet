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
        // Columns already exist, skipping
        // Schema::table('orders', function (Blueprint $table) {
        //     $table->decimal('shipping_cost', 10, 2)->nullable();
        //     $table->decimal('buyer_protection_fee', 10, 2)->nullable();
        //     $table->decimal('total_amount', 10, 2)->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'buyer_protection_fee', 'total_amount']);
        });
    }
};
