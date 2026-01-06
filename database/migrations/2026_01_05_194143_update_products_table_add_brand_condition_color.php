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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('condition')->default('good')->nullable(); // new_with_tags, very_good, good...
            $table->string('size')->nullable(); // Denormalized size string for quick filter? Or rely on attributes? 
            // Decision: Let's keep size as an attribute for now, but brand/condition are global.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['brand_id', 'condition', 'size']);
        });
    }
};
