<?php

namespace Modules\Chat\Services;

use App\Models\User;
use App\Models\Product;
use Modules\Chat\Models\Message;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Models\Conversation;
use Illuminate\Database\Eloquent\Builder;
use Modules\Chat\Models\Offer;
class ChatService
{
    public function getConversations(User $user)
    {
        return Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->orderByDesc('last_message_at')
            ->with(['product', 'userOne', 'userTwo'])
            ->get();
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

    public function sendMessage(Conversation $conversation, User $sender, string $body): Message
    {
        $message = $conversation->messages()->create([
            'user_id' => $sender->id,
            'body' => $body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Dispatch the broadcast event for real-time delivery
        MessageSent::dispatch($message);

        return $message;
    }

    public function markAsRead(Conversation $conversation, User $user): void
    {
        $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
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

        $message = $conversation->messages()->create([
            'user_id' => $sender->id, // The buyer who made the offer
            'body' => $body, // Keep it simple for now
            'type' => 'offer_made', // Indicate message type
            'offer_id' => $offer->id, // Link to the offer
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Dispatch broadcast event so it shows up in chat immediately
        MessageSent::dispatch($message->load('user')); // Ensure user is loaded

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
            // Update conversation timestamp
            $conversation->update(['last_message_at' => $rejectionMessage->created_at]);
        }

        // No single message return needed now as we might send multiple
    }
}