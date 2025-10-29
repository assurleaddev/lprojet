<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\Enums\OfferStatus;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete(); // User making the offer
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete(); // User receiving the offer (product owner)
            $table->decimal('offer_price', 10, 2);
            $table->string('status')->default(OfferStatus::Pending->value); // pending, accepted, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamp('responded_at')->nullable(); // When seller accepts/rejects
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_offers');
    }
};
