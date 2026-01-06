<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete()->after('vendor_id');
        });

        // Modify status enum to include 'reserved'
        // Using raw statement to be database agnostic if possible, but for MySQL/MariaDB modifying ENUM requires ALTER TABLE
        DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('pending', 'approved', 'sold', 'reserved') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['buyer_id']);
            $table->dropColumn('buyer_id');
        });

        // Revert status enum to original
        DB::statement("UPDATE products SET status = 'pending' WHERE status = 'reserved'");
        DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('pending', 'approved', 'sold') DEFAULT 'pending'");
    }
};
