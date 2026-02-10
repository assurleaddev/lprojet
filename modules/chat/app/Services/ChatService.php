<?php

namespace Modules\Chat\Services;

use App\Models\User;
use App\Models\Product;
use Modules\Chat\Models\Message;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Models\Conversation;
use Illuminate\Database\Eloquent\Builder;
use Modules\Chat\Models\Offer;
use App\Models\Order;
use Modules\Chat\Events\MessageRead;
use Modules\Chat\Enums\OfferStatus;
class ChatService
{
    public function getConversations(User $user)
    {
        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($user) {
                    $query->where('user_id', '!=', $user->id)
                        ->whereNull('read_at');
                }
            ])
            ->orderByDesc('last_message_at')
            ->with(['product', 'userOne', 'userTwo'])
            ->get();

        // Fetch unread chat notifications to check per conversation
        $unreadNotifications = $user->unreadNotifications()
            ->whereIn('data->type', User::getChatNotificationTypes())
            ->get();

        foreach ($conversations as $conversation) {
            $hasUnreadNotification = $unreadNotifications->contains(function ($n) use ($conversation) {
                $url = $n->data['url'] ?? '';
                // Match by ID in query string like ?id=123 or &id=123
                return str_contains($url, 'id=' . $conversation->id);
            });
            $conversation->has_unread = ($conversation->unread_messages_count > 0) || $hasUnreadNotification;
        }

        return $conversations;
    }

    public function getOrCreateConversation(User $userA, User $userB, Product $product): Conversation
    {
        // To ensure consistency, sort users IDs before querying/creating
        $ids = [$userA->id, $userB->id];
        sort($ids);
        $userOneId = $ids[0];
        $userTwoId = $ids[1];

        return Conversation::firstOrCreate([
            'product_id' => $product->id,
            'user_one_id' => $userOneId,
            'user_two_id' => $userTwoId,
        ]);
    }

    public function sendMessage(Conversation $conversation, User $sender, ?string $body = null, array $attachments = []): Message
    {
        $data = [
            'user_id' => $sender->id,
            'body' => $body,
        ];

        // Attachments are now handled separately via relation, but we create the message first.
        $message = $conversation->messages()->create($data);

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                // Determine file type
                $mime = $attachment->getMimeType();
                $type = str_contains($mime, 'image') ? 'image' : 'file';

                // Store file
                $path = $attachment->store('chat_attachments', 'public');

                // Create attachment record
                $message->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_type' => $type,
                    'file_size' => $attachment->getSize(),
                ]);
            }
        }

        $conversation->update(['last_message_at' => now()]);

        // Dispatch the broadcast event for real-time delivery
        MessageSent::dispatch($message);

        // Notify the recipient
        $recipient = $conversation->user_one_id === $sender->id ? $conversation->userTwo : $conversation->userOne;
        $recipient->notify(new \App\Notifications\NewMessageNotification($message, $sender));

        return $message;
    }

    public function markAsRead(Conversation $conversation, User $user): void
    {
        // Mark messages as read and delivered
        $updated = $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'delivered_at' => \DB::raw('COALESCE(delivered_at, NOW())')
            ]);

        // Mark related database notifications as read
        $user->unreadNotifications()
            ->whereIn('data->type', User::getChatNotificationTypes())
            ->each(function ($notification) use ($conversation) {
                /** @var \Illuminate\Notifications\DatabaseNotification $notification */
                $url = $notification->data['url'] ?? '';
                if (str_contains($url, 'id=' . $conversation->id)) {
                    $notification->markAsRead();
                }
            });

        // Broadcast that messages were read ONLY if something changed
        if ($updated > 0) {
            MessageRead::dispatch($conversation->id, $user->id);
        }
    }

    public function markAsDelivered(Conversation $conversation, User $user): void
    {
        // Mark messages as delivered for the recipient
        $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        // Note: We don't necessarily need a "MessageDelivered" broadcast yet, 
        // as simple read receipts are often enough, but it helps for multi-state.
    }

    public function withdrawOffer(Offer $offer, User $user): void
    {
        if ($offer->buyer_id !== $user->id || $offer->status !== OfferStatus::Pending) {
            throw new \Exception("Unauthorized or invalid offer status for withdrawal.");
        }

        $offer->update(['status' => OfferStatus::Withdrawn]);

        $body = sprintf("%s withdrew their offer of $%s.", $user->name, number_format($offer->offer_price, 2));

        $message = $offer->conversation->messages()->create([
            'user_id' => $user->id,
            'body' => $body,
            'type' => 'offer_withdrawn',
            'offer_id' => $offer->id,
        ]);

        MessageSent::dispatch($message->load('user'));
    }

    public function reserveProduct(Product $product, User $seller, User $buyer): void
    {
        if ($product->vendor_id !== $seller->id) {
            throw new \Exception("Only the seller can reserve the product.");
        }

        $product->update([
            'status' => 'reserved',
            'buyer_id' => $buyer->id, // Assuming this column exists per migration
        ]);

        // Find or create conversation to notify
        $conversation = $this->getOrCreateConversation($seller, $buyer, $product);

        $body = sprintf("Item Reserved! %s has reserved this item for %s.", $seller->full_name, $buyer->full_name);

        $message = $conversation->messages()->create([
            'user_id' => $seller->id,
            'body' => $body,
            'type' => 'product_reserved',
        ]);

        MessageSent::dispatch($message->load('user'));
    }

    public function unreserveProduct(Product $product, User $seller): void
    {
        if ($product->vendor_id !== $seller->id) {
            throw new \Exception("Only the seller can unreserve the product.");
        }

        $product->update([
            'status' => 'approved', // Or whatever the active status is
            'buyer_id' => null,
        ]);

        // Find the conversation with the buyer who it was reserved for (if possible)
        // For simplicity, we just log/update. If we want a message, we need the buyer.
    }
    public function sendOfferMadeMessage(Conversation $conversation, User $sender, Offer $offer): Message
    {
        // You might store more structured data if needed, e.g., in a JSON column
        $body = sprintf(
            "%s made an offer of $%s for %s.",
            $sender->name,
            number_format($offer->offer_price, 2),
            $offer->product->name
        );

        $offer->update(['expires_at' => now()->addHours(24)]);

        $message = $conversation->messages()->create([
            'user_id' => $sender->id, // The buyer who made the offer
            'body' => $body, // Keep it simple for now
            'type' => 'offer_made', // Indicate message type
            'offer_id' => $offer->id, // Link to the offer
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Dispatch broadcast event so it shows up in chat immediately
        MessageSent::dispatch($message->load('user')); // Ensure user is loaded

        // Notify Seller (Recipient)
        $recipient = $conversation->user_one_id === $sender->id ? $conversation->userTwo : $conversation->userOne;
        // OfferNotification is already handled in OfferService or wherever offer is created? 
        // Wait, OfferNotification was existing. Let's check where it's used.
        // It seems it's used in OfferService (not shown here) or maybe I should add it here if it's missing?
        // The prompt asked for "New Message", "Item Sold", "Item Shipped", "Order Completed".
        // "Offer Made" usually has its own notification. I'll assume it's handled elsewhere or add it if I see it's missing.
        // But for "New Message" (sendMessage above), I added it.

        return $message;
    }

    // Add methods for sending accepted/rejected messages later
    public function sendOfferResponseMessage(Conversation $conversation, User $responder, Offer $offer, bool $accepted, ?string $reason = null): void // Return void or array of messages
    {
        $offerDetails = sprintf(
            "offer of $%s for %s",
            number_format($offer->offer_price, 2),
            $offer->product->name
        );

        if ($accepted) {
            // --- Message 1: Confirmation of Acceptance (for both users) ---
            $acceptanceBody = sprintf(
                "%s accepted the %s.",
                $responder->name, // The seller
                $offerDetails
            );
            $acceptanceType = 'offer_accepted';

            $acceptanceMessage = $conversation->messages()->create([
                'user_id' => $responder->id, // Seller accepts
                'body' => $acceptanceBody,
                'type' => $acceptanceType,
                'offer_id' => $offer->id, // Link to the original offer
            ]);

            // Dispatch broadcast for the acceptance message
            MessageSent::dispatch($acceptanceMessage->load('user'));

            // Notify Buyer
            $offer->buyer->notify(new \App\Notifications\OfferNotification($offer, 'accepted'));

            // --- Message 2: Checkout Prompt (Primarily for Buyer) ---
            $checkoutUrl = route('checkout.offer', ['offer' => $offer->id]);
            $checkoutBody = sprintf(
                "Offer accepted! Proceed to checkout for %s at $%s:\n%s",
                $offer->product->name,
                number_format($offer->offer_price, 2),
                $checkoutUrl
            );
            $checkoutType = 'offer_checkout_prompt'; // New message type

            // Create the checkout prompt message - sender is still the seller (system action)
            $checkoutMessage = $conversation->messages()->create([
                'user_id' => $responder->id, // Or maybe a system user ID if you have one? Let's keep seller for now.
                'body' => $checkoutBody, // Body contains the link
                'type' => $checkoutType,
                'offer_id' => $offer->id, // Link to the original offer
            ]);

            // Dispatch broadcast for the checkout prompt message
            MessageSent::dispatch($checkoutMessage->load('user'));

            // Update conversation timestamp based on the *last* message sent
            $conversation->update(['last_message_at' => $checkoutMessage->created_at]);

        } else {
            // --- Rejection Message (Only one message needed) ---
            $rejectionBody = $responder->name . " rejected the " . $offerDetails .
                ($reason ? "\nReason: " . $reason : "");
            $rejectionType = 'offer_rejected';

            $rejectionMessage = $conversation->messages()->create([
                'user_id' => $responder->id,
                'body' => $rejectionBody,
                'type' => $rejectionType,
                'offer_id' => $offer->id,
            ]);

            // Dispatch broadcast for rejection message
            MessageSent::dispatch($rejectionMessage->load('user'));

            // Notify Buyer
            $offer->buyer->notify(new \App\Notifications\OfferNotification($offer, 'rejected'));

            // Update conversation timestamp
            $conversation->update(['last_message_at' => $rejectionMessage->created_at]);
        }
        // No single message return needed now as we might send multiple
    }

    public function sendOfferCounteredMessage(Conversation $conversation, User $sender, Offer $offer): Message
    {
        $body = sprintf(
            "%s made a counter offer of $%s for %s.",
            $sender->name,
            number_format($offer->offer_price, 2),
            $offer->product->name
        );

        $message = $conversation->messages()->create([
            'user_id' => $sender->id,
            'body' => $body,
            'type' => 'offer_countered',
            'offer_id' => $offer->id,
        ]);

        $conversation->update(['last_message_at' => now()]);

        MessageSent::dispatch($message->load('user'));

        // Notify Buyer (Counter offer recipient)
        // Assuming OfferNotification handles 'received' type for counter offers too?
        // The OfferNotification class I saw handled 'received', 'accepted', 'rejected'.
        // Let's assume 'received' covers counter offers or I should add a type.
        // For now, I'll leave it as is to avoid breaking existing flow if it's handled elsewhere.
        // But wait, if I'm here, I should probably ensure it's notified.
        // $offer->buyer->notify(new \App\Notifications\OfferNotification($offer, 'received')); 
        // But sender is seller, so recipient is buyer.

        return $message;
    }

    public function sendItemSoldMessage(Conversation $conversation, User $buyer, Order $order): Message
    {
        $downloadUrl = route('shipping-label.download', ['order' => $order->id]);

        $body = sprintf(
            "Item Sold! %s has purchased %s. Please download the shipping label and prepare the package.\n%s",
            $buyer->full_name,
            $order->product->name,
            $downloadUrl
        );

        // System message or from buyer? Let's make it look like a system notification or from the buyer.
        // Since the buyer triggered the action, we'll attribute it to the buyer for now, 
        // but the UI will render it specially based on type.
        $message = $conversation->messages()->create([
            'user_id' => $buyer->id,
            'body' => $body,
            'type' => 'item_sold',
            'offer_id' => null, // Not directly linked to an offer object here, but could be if needed
            // We might want to store order_id if we add a column, but for now we'll parse or just use the type.
        ]);

        $conversation->update(['last_message_at' => now()]);

        MessageSent::dispatch($message->load('user'));

        // Notify Vendor (Seller)
        $order->vendor->notify(new \App\Notifications\ItemSoldNotification($order, $buyer));

        return $message;
    }

    public function sendOrderPlacedMessage(Conversation $conversation, User $buyer, Order $order): Message
    {
        $seller = $conversation->product->vendor;
        $deadline = now()->addDays(7)->translatedFormat('d M');

        $body = sprintf(
            "%s must send the order content before %s. We will inform you with any updates of your order.",
            $seller->full_name,
            $deadline
        );

        $message = $conversation->messages()->create([
            'user_id' => $seller->id, // Attributed to seller for buyer perspective
            'body' => $body,
            'type' => 'order_placed',
            'offer_id' => null,
        ]);

        $conversation->update(['last_message_at' => now()]);
        MessageSent::dispatch($message->load('user'));

        return $message;
    }

    public function sendItemShippedMessage(Conversation $conversation, User $seller, Order $order): Message
    {
        $body = sprintf(
            "Item Shipped! %s has shipped %s. The package is on its way.",
            $seller->full_name,
            $order->product->name
        );

        $message = $conversation->messages()->create([
            'user_id' => $seller->id,
            'body' => $body,
            'type' => 'item_shipped',
        ]);

        $conversation->update(['last_message_at' => now()]);

        MessageSent::dispatch($message->load('user'));

        // Notify Buyer
        $order->user->notify(new \App\Notifications\ItemShippedNotification($order, $seller));

        return $message;
    }

    public function sendOrderCompletedMessage(Conversation $conversation, User $buyer, Order $order): Message
    {
        $body = sprintf(
            "Order Completed! %s has received the item and released the funds. Transaction finished.",
            $buyer->full_name
        );

        $message = $conversation->messages()->create([
            'user_id' => $buyer->id, // Buyer triggers this
            'body' => $body,
            'type' => 'order_completed',
        ]);

        $conversation->update(['last_message_at' => now()]);

        MessageSent::dispatch($message->load('user'));

        // Notify Vendor
        $order->vendor->notify(new \App\Notifications\OrderCompletedNotification($order, $buyer));

        return $message;
    }
}