<div>
    @auth
        @if(auth()->id() !== $product->user_id)
            <div class="flex space-x-2 mt-4">
                {{-- Message Seller Button --}}
                <button type="button" 
                   wire:click="startConversation" 
                   class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-comment-alt mr-2"></i> Message Seller
                </button>

                {{-- Make Offer Button --}}
                <button type="button" 
                    wire:click="$dispatch('open-make-offer-modal', { productId: {{ $product->id }} })" 
                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-tag mr-2"></i> Make Offer
                </button>
            </div>
        @endif
    @else
        {{-- Buttons requiring login --}}
        <div class="flex space-x-2 mt-4">
             <button type="button" x-data @click="$dispatch('open-auth-modal')" class="text-blue-500 hover:underline text-sm">
                Log in to message seller
            </button>
             <button type="button" x-data @click="$dispatch('open-auth-modal')" class="text-green-500 hover:underline text-sm">
                Log in to make offer
            </button>
        </div>
    @endauth

    {{-- Include the MakeOfferModal component (it will be hidden initially) --}}
    @livewire('chat::make-offer-modal') 
</div>