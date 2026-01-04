<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('received_at')->nullable()->after('status');
        });

        // Note: Changing ENUMs in migration often requires raw SQL or specific logic depending on DB driver.
        // For simplicity and compatibility, we assume status is string or we might just use raw statement if strictly enum.
        // If it's a string column (common in Laravel default unless explicitly enum), no change needed for 'delivered'.
        // Checking Order model, 'status' is likely string.
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('received_at');
        });
    }
};
