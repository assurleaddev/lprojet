<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('tracking_events')->nullable()->after('tracking_code');
            $table->json('tracking_info')->nullable()->after('tracking_events');
            $table->timestamp('tracking_checked_at')->nullable()->after('tracking_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_events', 'tracking_info', 'tracking_checked_at']);
        });
    }
};
