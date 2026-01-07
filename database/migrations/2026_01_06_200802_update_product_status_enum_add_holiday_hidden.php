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
        // Modify status enum to include 'hidden' and 'holiday'
        // We include all previous statuses: pending, approved, sold, reserved
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('pending', 'approved', 'sold', 'reserved', 'hidden', 'holiday') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum to original
        // We should handle potential data loss for 'hidden'/'holiday' rows, but for rollback we'll just set them to pending usually.
        \Illuminate\Support\Facades\DB::statement("UPDATE products SET status = 'pending' WHERE status IN ('hidden', 'holiday')");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('pending', 'approved', 'sold', 'reserved') DEFAULT 'pending'");
    }
};
