<?php

namespace Modules\Chat\Livewire;

use Livewire\Component;
use App\Models\Product;
use Modules\Chat\Models\Offer;
use Modules\Chat\Services\ChatService;
use Livewire\Attributes\On; // Import On attribute
use Illuminate\Support\Facades\Auth;

class MakeOfferModal extends Component
{
    public bool $showModal = false;
    public ?Product $product = null;
    public $offerPrice = '';

    #[On('open-make-offer-modal')] // Listen for the browser event
    public function openModal($productId): void
    {
        if (!Auth::check()) {
             $this->dispatch('open-auth-modal'); // Redirect to login if not authenticated
             return;
        }

        $this->product = Product::find($productId);
        if (!$this->product || $this->product->user_id === Auth::id()) {
            // Handle product not found or user trying to offer on own product
            // You might want to dispatch a toast notification here
            $this->product = null; 
            return;
        }
        $this->reset('offerPrice'); // Clear previous price
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->product = null;
        $this->resetValidation();
    }

    public function submitOffer(ChatService $chatService): void
    {
         if (!$this->product) return; // Should not happen if modal opened correctly

         $user = Auth::user();
         if (!$user) return; // Should not happen

         $validated = $this->validate([
             // Ensure offer is numeric and less than or equal to original price
             'offerPrice' => ['required', 'numeric', 'min:0.01', 'max:' . $this->product->price], 
         ]);

         // Ensure conversation exists first
         $conversation = $chatService->getOrCreateConversation($user, $this->product->vendor, $this->product);

         // Create the Offer record
         $offer = Offer::create([
             'conversation_id' => $conversation->id,
             'product_id' => $this->product->id,
             'buyer_id' => $user->id,
             'seller_id' => $this->product->vendor_id,
             'offer_price' => $validated['offerPrice'],
             'status' => \Modules\Chat\Enums\OfferStatus::Pending,
         ]);

         // Send a special message in the chat referencing the offer
         $chatService->sendOfferMadeMessage($conversation, $user, $offer); 

         // Optionally: Send notification (email/push) to seller
         // $this->product->user->notify(new NewOfferReceived($offer));

         $this->closeModal();

         // Dispatch toast notification for success
         $this->dispatch('toast', ['message' => 'Offer sent successfully!', 'type' => 'success']);

         // Redirect user to the chat
         $this->redirect(route('chat.show', ['queryConversationId' => $conversation->id]), navigate: true); // Use Livewire navigate
    }

    public function render()
    {
        return view('chat::livewire.make-offer-modal');
    }
}