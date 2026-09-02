<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite indexes for the hot chat / notification queries:
 * - per-conversation unread counts (inbox list, header badge)
 * - per-user unread message count
 * - conversation lists ordered by last_message_at
 * - the offer-expiry sweep and product-offer lookups
 * - unread-notification counts per notifiable
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'read_at'], 'messages_conversation_read_idx');
            $table->index(['user_id', 'read_at'], 'messages_user_read_idx');
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->index(['user_one_id', 'last_message_at'], 'conversations_user_one_last_idx');
            $table->index(['user_two_id', 'last_message_at'], 'conversations_user_two_last_idx');
        });

        Schema::table('chat_offers', function (Blueprint $table) {
            $table->index(['product_id', 'status'], 'offers_product_status_idx');
            $table->index(['status', 'expires_at'], 'offers_status_expires_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_notifiable_read_idx');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conversation_read_idx');
            $table->dropIndex('messages_user_read_idx');
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_user_one_last_idx');
            $table->dropIndex('conversations_user_two_last_idx');
        });

        Schema::table('chat_offers', function (Blueprint $table) {
            $table->dropIndex('offers_product_status_idx');
            $table->dropIndex('offers_status_expires_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_notifiable_read_idx');
        });
    }
};
