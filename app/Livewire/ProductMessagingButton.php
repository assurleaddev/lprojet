<?php

namespace App\Livewire;
use Livewire\Component;
use App\Models\Product; // Your Product model
use Modules\Chat\Services\ChatService; // Your Chat service

class ProductMessagingButton extends Component
{
    public Product $product;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function startConversation(ChatService $chatService)
    {
        // Ensure the user is logged in
        if (!auth()->check()) {
            // Optionally dispatch an event to open your login modal
            $this->dispatch('open-auth-modal'); 
            return;
        }

        // Ensure the user isn't trying to message themselves
        if (auth()->id() === $this->product->user_id) {
            // Maybe flash a message?
            return; 
        }

        // Find or create the conversation using the ChatService
        $conversation = $chatService->getOrCreateConversation(
            auth()->user(), 
            $this->product->vendor, // Assuming product has a 'user' relationship
            $this->product
        );

        // Redirect the user to the chat page/dashboard, passing the conversation ID
        return redirect()->route('chat.show', ['queryConversationId' => $conversation->id]);
    }

    public function render()
    {
        // Render just the button itself
        return view('livewire.product-messaging-button'); 
    }
}
