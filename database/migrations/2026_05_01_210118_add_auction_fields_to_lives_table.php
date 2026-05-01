<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('lives', function (Blueprint $table) {
            $table->enum('auction_status', ['idle', 'active'])->default('idle')->after('status');
            $table->unsignedBigInteger('likes_count')->default(0)->after('auction_status');
        });
    }

    public function down(): void
    {
        Schema::table('lives', function (Blueprint $table) {
            $table->dropColumn(['auction_status', 'likes_count']);
        });
    }
};
