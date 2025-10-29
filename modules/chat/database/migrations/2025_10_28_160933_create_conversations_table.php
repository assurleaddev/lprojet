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
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            // Assuming the Product model is central, link the chat to the item
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Link to the two participating users (seller and buyer)
            $table->foreignId('user_one_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_two_id')->constrained('users')->onDelete('cascade');
            
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            // Enforce only one conversation per pair of users per product
            $table->unique(['user_one_id', 'user_two_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
