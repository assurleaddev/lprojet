<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('bank_name');
            $table->string('account_holder');
            $table->string('rib');
            $table->timestamps();
        });

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->foreignId('payout_account_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['payout_account_id']);
            $table->dropColumn('payout_account_id');
        });
        Schema::dropIfExists('payout_accounts');
    }
};
