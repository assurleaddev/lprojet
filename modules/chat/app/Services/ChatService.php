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
    public function sendOfferResponseMessage(Conversation $conversation, User $responder, Offer $offer, bool $accepted, ?string $reason = null): Message
    {
        $offerDetails = sprintf(
            "Offer of $%s for %s",
            number_format($offer->offer_price, 2),
            $offer->product->name
        );

        if ($accepted) {
            $body = $responder->name . " accepted the " . $offerDetails;
            $type = 'offer_accepted';
        } else {
            $body = $responder->name . " rejected the " . $offerDetails . ($reason ? "\nReason: " . $reason : "");
            $type = 'offer_rejected';
        }


        $message = $conversation->messages()->create([
            'user_id' => $responder->id, // The seller who responded
            'body' => $body,
            'type' => $type,
            'offer_id' => $offer->id,
        ]);

        $conversation->update(['last_message_at' => now()]);
        MessageSent::dispatch($message->load('user'));
        return $message;
    }
}