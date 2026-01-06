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
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('type')->default('select')->after('name'); // select, color, radio, checkbox, text
            $table->string('code')->nullable()->after('name'); // e.g., size_eu, main_color
            $table->string('icon')->nullable()->after('type'); // FontAwesome class or similar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn(['type', 'code', 'icon']);
        });
    }
};
