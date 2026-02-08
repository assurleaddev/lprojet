<?php

namespace Modules\Chat\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Modules\Chat\Services\ChatService;
use Modules\Chat\Models\Offer;
use Modules\Chat\Models\Conversation;
use App\Models\Product; // Correct import
use Illuminate\Support\Facades\Auth;
use Modules\Chat\Models\Message;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Enums\OfferStatus;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CounterOfferModal extends Component
{
    public bool $showModal = false;
    public $productId = null; // Store ID instead of Model
    public $offerPrice = '';
    public ?int $targetBuyerId = null;

    #[Computed]
    public function product()
    {
        return Product::find($this->productId);
    }

    #[On('open-counter-offer-modal')]
    public function openModal($productId, $targetBuyerId): void
    {
        Log::info("CounterOfferModal: openModal triggered for Product {$productId} and Buyer {$targetBuyerId}");

        if (!Auth::check()) {
            $this->dispatch('open-auth-modal');
            return;
        }

        $this->productId = $productId;
        $this->targetBuyerId = $targetBuyerId;

        if (!$this->product()) {
            Log::warning("CounterOfferModal: Product {$productId} not found.");
            $this->productId = null;
            return;
        }

        // Ensure the user is the owner (Seller)
        if ($this->product()->vendor_id !== Auth::id()) {
            Log::warning("CounterOfferModal: User " . Auth::id() . " is not the owner of Product {$productId}");
            return; // Unauthorized
        }

        $this->reset('offerPrice');
        $this->showModal = true;
        Log::info("CounterOfferModal: Modal opened successfully. showModal set to true.");
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->product = null;
        $this->targetBuyerId = null;
        $this->resetValidation();
    }

    public function submitCounterOffer(ChatService $chatService): void
    {
        if (!$this->product || !$this->targetBuyerId)
            return;

        $sellerUser = Auth::user();
        $buyerUser = User::find($this->targetBuyerId);

        if (!$sellerUser || !$buyerUser)
            return;

        $validated = $this->validate([
            'offerPrice' => ['required', 'numeric', 'min:0.01', 'max:' . $this->product->price],
        ]);

        // Ensure conversation exists
        $conversation = $chatService->getOrCreateConversation($buyerUser, $sellerUser, $this->product);

        // 1. Reject the original Pending offer from the Buyer
        $originalOffer = Offer::where('product_id', $this->product->id)
            ->where('buyer_id', $buyerUser->id)
            ->where('seller_id', $sellerUser->id)
            ->where('status', OfferStatus::Pending)
            ->first();

        if ($originalOffer) {
            $originalOffer->update([
                'status' => OfferStatus::Rejected,
                'rejection_reason' => 'Counter offer made',
                'responded_at' => now(),
            ]);

            // Send rejection message
            $chatService->sendOfferResponseMessage($conversation, $sellerUser, $originalOffer, false, 'Counter offer made');
        }

        // 2. Check for existing AwaitingBuyer counter offer to update, or create new
        $existingCounterOffer = Offer::where('product_id', $this->product->id)
            ->where('buyer_id', $buyerUser->id)
            ->where('seller_id', $sellerUser->id)
            ->where('status', OfferStatus::AwaitingBuyer)
            ->first();

        if ($existingCounterOffer) {
            // Update existing counter offer
            $existingCounterOffer->update([
                'offer_price' => $validated['offerPrice'],
                'created_at' => now(),
            ]);
            $offer = $existingCounterOffer;

            // Update message
            $message = Message::where('offer_id', $offer->id)
                ->where('type', 'offer_countered')
                ->first();

            if ($message) {
                $body = sprintf(
                    "%s made a counter offer of $%s for %s.",
                    $sellerUser->name,
                    number_format($offer->offer_price, 2),
                    $offer->product->name
                );
                $message->update([
                    'body' => $body,
                    'created_at' => now(),
                ]);
                $conversation->update(['last_message_at' => now()]);
                MessageSent::dispatch($message->load('user'));
            } else {
                $chatService->sendOfferCounteredMessage($conversation, $sellerUser, $offer);
            }

        } else {
            // Create NEW Counter Offer
            $offer = Offer::create([
                'conversation_id' => $conversation->id,
                'product_id' => $this->product->id,
                'buyer_id' => $buyerUser->id,
                'seller_id' => $sellerUser->id,
                'offer_price' => $validated['offerPrice'],
                'status' => OfferStatus::AwaitingBuyer,
            ]);

            $chatService->sendOfferCounteredMessage($conversation, $sellerUser, $offer);
        }

        $this->dispatch('refresh-chat')->to(ChatWindow::class);
        $this->closeModal();
        $this->dispatch('toast', ['message' => 'Counter offer sent successfully!', 'type' => 'success']);
    }

    public function render()
    {
        return view('chat::livewire.counter-offer-modal');
    }
}
