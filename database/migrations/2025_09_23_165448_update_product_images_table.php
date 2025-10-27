<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temporarily disable foreign key checks to prevent errors
        Schema::disableForeignKeyConstraints();

        // Clear out the old data from the product_images table
        DB::table('product_images')->truncate();

        Schema::table('product_images', function (Blueprint $table) {
            // 1. Add the new media_id column, making it nullable first
            $table->unsignedBigInteger('media_id')->nullable()->after('product_id');

            // 2. Add the foreign key constraint
            $table->foreign('media_id')
                  ->references('id')
                  ->on('media')
                  ->onDelete('cascade');

            // 3. Remove the old path column
            $table->dropColumn('path');
        });

        // Now, make the media_id column non-nullable
        Schema::table('product_images', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id')->nullable(false)->change();
        });

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('path')->after('product_id');
            $table->dropForeign(['media_id']);
            $table->dropColumn('media_id');
        });
    }
};
