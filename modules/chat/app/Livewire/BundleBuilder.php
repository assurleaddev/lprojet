<?php

namespace Modules\Chat\Livewire;

use App\Models\Product;
use App\Models\User;
use Livewire\Component;
use Modules\Chat\Services\ChatService;
use Modules\Chat\Models\Conversation;

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

        // A vendor cannot bundle-buy from themselves.
        if ($buyer->id === $this->vendor->id) {
            $this->dispatch('toast', message: 'You cannot buy your own products.', type: 'error');
            return;
        }

        // Only the mounted vendor's own, currently sellable products — never trust
        // client-supplied IDs (they could reference other sellers' or sold items).
        $products = Product::whereIn('id', $this->selectedProducts)
            ->where('vendor_id', $this->vendor->id)
            ->whereIn('status', ['approved', 'active'])
            ->get();

        if ($products->count() !== count(array_unique($this->selectedProducts))) {
            $this->selectedProducts = $products->pluck('id')->all();
            $this->dispatch('toast', message: 'Some selected products are no longer available.', type: 'error');
            return;
        }

        // Calculate price using ChatService
        $priceData = $chatService->calculateBundlePrice($this->vendor, $products->all());

        // Create conversation
        $conversation = $chatService->getOrCreateConversation($buyer, $this->vendor);

        // Create offer with Accepted status
        $offer = $chatService->createOffer($conversation, $buyer, $priceData['final_total'], $products, \Modules\Chat\Enums\OfferStatus::Accepted);

        $this->isOpen = false;
        $this->selectedProducts = [];

        $this->dispatch('toast', message: 'Bundle request sent!', type: 'success');

        return redirect()->route('checkout.offer', ['offer' => $offer->id]);
    }

    public function render()
    {
        $products = $this->vendor->products()->whereIn('status', ['approved', 'active'])->get();

        return view('chat::livewire.bundle-builder', [
            'vendorProducts' => $products,
        ]);
    }
}
