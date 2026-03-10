<?php

namespace Modules\Chat\Livewire;

use App\Models\Product;
use App\Models\User;
use Livewire\Component;
use Modules\Chat\Services\ChatService;
use Modules\Chat\Models\Conversation;
use Illuminate\Support\Facades\Log;

class BundleBuilder extends Component
{
    public User $vendor;
    public array $selectedProducts = [];
    public bool $isOpen = false;

    protected $listeners = ['open-bundle-builder' => 'open'];

    public function mount(User $vendor)
    {
        $this->vendor = $vendor;
    }

    public function open()
    {
        $this->isOpen = true;
    }

    public function toggleProduct(int $productId)
    {
        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_diff($this->selectedProducts, [$productId]);
        } else {
            $this->selectedProducts[] = $productId;
        }
    }

    public function sendRequest(ChatService $chatService)
    {
        if (empty($this->selectedProducts)) {
            $this->dispatch('toast', message: 'Please select at least one product.', type: 'error');
            return;
        }

        $buyer = auth()->user();
        $products = Product::whereIn('id', $this->selectedProducts)->get();

        // Calculate price using ChatService
        $priceData = $chatService->calculateBundlePrice($this->vendor, $products->all());

        // Create conversation
        $conversation = $chatService->getOrCreateConversation($buyer, $this->vendor);

        // Create offer
        $offer = $chatService->createOffer($conversation, $buyer, $priceData['final_total'], $products);

        // Send message
        $chatService->sendOfferMadeMessage($conversation, $buyer, $offer);

        $this->isOpen = false;
        $this->selectedProducts = [];

        $this->dispatch('toast', message: 'Bundle request sent!', type: 'success');

        return redirect()->route('chat.show', ['chat' => $conversation->id]);
    }

    public function render()
    {
        $products = $this->vendor->products()->whereIn('status', ['approved', 'active'])->get();

        return view('chat::livewire.bundle-builder', [
            'vendorProducts' => $products
        ]);
    }
}
