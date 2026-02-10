<?php

namespace Modules\Chat\Livewire;

use Livewire\Component;
use App\Models\Product;
use Modules\Chat\Models\Offer;
use Modules\Chat\Services\ChatService;
use Livewire\Attributes\On; // Import On attribute
use Illuminate\Support\Facades\Auth;
use Modules\Chat\Models\Message;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Enums\OfferStatus;
use App\Models\User;

class MakeOfferModal extends Component
{
    public bool $showModal = false;
    public ?Product $product = null;
    public $productPrice = 0; // Add this property
    public $offerPrice = '';
    public bool $isCounter = false; // Flag for counter offer
    public ?int $targetBuyerId = null; // Used when seller makes a counter offer

    #[On('open-make-offer-modal')] // Listen for the browser event
    public function openModal($productId, $isCounter = false, $targetBuyerId = null): void
    {
        if (!Auth::check()) {
            $this->dispatch('open-auth-modal'); // Redirect to login if not authenticated
            return;
        }

        $this->product = Product::find($productId);
        $this->isCounter = $isCounter;
        $this->targetBuyerId = $targetBuyerId;

        if (!$this->product) {
            $this->product = null;
            return;
        }

        $this->productPrice = $this->product->price; // Set the price

        // If it's a counter offer, ensure the user is the owner (Seller)
        if ($this->isCounter && $this->product->user_id !== Auth::id()) {
            return; // Unauthorized
        }

        // If it's a normal offer, ensure the user is NOT the owner (Buyer)
        if (!$this->isCounter && $this->product->user_id === Auth::id()) {
            return; // Owner cannot make offer on own product
        }

        $this->reset('offerPrice'); // Clear previous price
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->product = null;
        $this->isCounter = false;
        $this->targetBuyerId = null;
        $this->resetValidation();
    }

    public function submitOffer(ChatService $chatService): void
    {
        if (!$this->product)
            return; // Should not happen if modal opened correctly

        $user = Auth::user();
        if (!$user)
            return; // Should not happen

        $validated = $this->validate([
            // Ensure offer is numeric and less than or equal to original price
            'offerPrice' => ['required', 'numeric', 'min:0.01', 'max:' . $this->product->price],
        ]);

        // Determine buyer and seller for the conversation and offer
        $buyerUser = null;
        $sellerUser = null;

        if ($this->isCounter) {
            // If current user is the seller making a counter offer
            if (!$this->targetBuyerId) {
                // This should not happen if the modal is opened correctly for a counter offer
                // from a chat context where the other user (buyer) is known.
                return;
            }
            $buyerUser = User::find($this->targetBuyerId);
            $sellerUser = $user; // Current user is the seller
        } else {
            // If current user is the buyer making an initial offer
            $buyerUser = $user; // Current user is the buyer
            $sellerUser = $this->product->vendor;
        }

        if (!$buyerUser || !$sellerUser) {
            return; // Invalid buyer or seller
        }

        // Ensure conversation exists first
        $conversation = $chatService->getOrCreateConversation($buyerUser, $sellerUser, $this->product);

        // If this is a Counter Offer, we must first REJECT the original Pending offer from the Buyer
        if ($this->isCounter) {
            $originalOffer = Offer::where('product_id', $this->product->id)
                ->where('buyer_id', $buyerUser->id)
                ->where('seller_id', $sellerUser->id) // I am the seller
                ->where('status', OfferStatus::Pending)
                ->first();

            if ($originalOffer) {
                // Reject the original offer
                $originalOffer->update([
                    'status' => OfferStatus::Rejected,
                    'rejection_reason' => 'Counter offer made',
                    'responded_at' => now(),
                ]);

                // Send rejection message
                $chatService->sendOfferResponseMessage($conversation, $sellerUser, $originalOffer, false, 'Counter offer made');
            }
        }

        // Check for an existing pending offer from this user for this product
        // For counter offers, we check if there's an existing counter offer (AwaitingBuyer)
        $statusToCheck = $this->isCounter ? OfferStatus::AwaitingBuyer : OfferStatus::Pending;

        $existingOffer = Offer::where('product_id', $this->product->id)
            ->where(function ($query) use ($user, $buyerUser, $sellerUser) {
                if ($this->isCounter) {
                    // If current user is seller, they are countering an offer made by $buyerUser
                    // So we look for an offer where $buyerUser is the buyer and $user (seller) is the seller.
                    $query->where('buyer_id', $buyerUser->id)
                        ->where('seller_id', $user->id);
                } else {
                    // If current user is buyer, they are making an offer to $sellerUser
                    // So we look for an offer where $user (buyer) is the buyer and $sellerUser is the seller.
                    $query->where('buyer_id', $user->id)
                        ->where('seller_id', $sellerUser->id);
                }
            })
            ->where('status', $statusToCheck)
            ->first();

        if ($existingOffer) {
            // Update existing offer
            $existingOffer->update([
                'offer_price' => $validated['offerPrice'],
                'created_at' => now(), // Bump timestamp
                // If it was a counter offer update, status remains AwaitingBuyer.
                // If it was a normal offer update, status remains Pending.
            ]);
            $offer = $existingOffer;

            // Find and update the associated message
            $messageType = $this->isCounter ? 'offer_countered' : 'offer_made';
            $message = Message::where('offer_id', $offer->id)
                ->where('type', $messageType)
                ->first();

            if ($message) {
                $body = sprintf(
                    "%s made a %s of $%s for %s.",
                    $user->name,
                    $this->isCounter ? 'counter offer' : 'offer',
                    number_format($offer->offer_price, 2),
                    $offer->product->name
                );

                $message->update([
                    'body' => $body,
                    'created_at' => now(), // Bump message
                ]);

                // Bump conversation
                $conversation->update(['last_message_at' => now()]);

                // Dispatch event to refresh UI
                MessageSent::dispatch($message->load('user'));
            } else {
                // If message not found (maybe first time countering this specific offer instance?), send new one
                if ($this->isCounter) {
                    $chatService->sendOfferCounteredMessage($conversation, $user, $offer);
                } else {
                    $chatService->sendOfferMadeMessage($conversation, $user, $offer);
                }
            }
        } else {
            // Create NEW Offer
            $offer = Offer::create([
                'conversation_id' => $conversation->id,
                'product_id' => $this->product->id,
                'buyer_id' => $buyerUser->id,
                'seller_id' => $sellerUser->id,
                'offer_price' => $validated['offerPrice'],
                'status' => $this->isCounter ? OfferStatus::AwaitingBuyer : OfferStatus::Pending,
            ]);

            if ($this->isCounter) {
                $chatService->sendOfferCounteredMessage($conversation, $user, $offer);
            } else {
                $chatService->sendOfferMadeMessage($conversation, $user, $offer);
            }
        }

        // Optionally: Send notification (email/push) to seller
        // $this->product->user->notify(new NewOfferReceived($offer));

        $this->closeModal();

        // Dispatch notification for success
        $this->dispatch('notify', message: 'Offer sent successfully!', type: 'success');

        // Dispatch event locally for the sender if they stay on the same page
        $this->dispatch('refresh-chat')->to(ChatWindow::class);

        // Redirect user to the chat
        $this->redirect(route('chat.dashboard', ['id' => $conversation->id]), navigate: true); // Use Livewire navigate
    }

    public function render()
    {
        return view('chat::livewire.make-offer-modal');
    }
}