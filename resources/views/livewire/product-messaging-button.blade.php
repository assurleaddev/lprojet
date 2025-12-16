<div>
    @auth
        @if(auth()->id() !== $product->user_id)
            <div class="space-y-2">
                {{-- Make Offer Button --}}
                <button type="button" wire:click="$dispatch('open-make-offer-modal', { productId: {{ $product->id }} })"
                    class="w-full block text-center border border-[#007782] text-[#007782] font-bold py-2 rounded-md hover:bg-[#007782]/10 transition-colors text-sm">
                    Make an offer
                </button>

                {{-- Message Seller Button --}}
                <button type="button" wire:click="startConversation"
                    class="w-full block text-center border border-[#007782] text-[#007782] font-bold py-2 rounded-md hover:bg-[#007782]/10 transition-colors text-sm">
                    Ask seller
                </button>
            </div>
        @endif
    @else
        {{-- Buttons requiring login --}}
        <div class="space-y-2">
            <button type="button" x-data @click="$dispatch('open-auth-modal')"
                class="w-full border border-[#007782] text-[#007782] font-bold py-2 rounded-md hover:bg-[#007782]/10 transition-colors text-sm">
                Make an offer
            </button>
            <button type="button" x-data @click="$dispatch('open-auth-modal')"
                class="w-full border border-[#007782] text-[#007782] font-bold py-2 rounded-md hover:bg-[#007782]/10 transition-colors text-sm">
                Ask seller
            </button>
        </div>
    @endauth

    {{-- Include the MakeOfferModal component (it will be hidden initially) --}}
    @livewire('chat::make-offer-modal')
</div>