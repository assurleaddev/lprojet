<div>
    @auth
        @if(auth()->id() !== $product->user_id)
            <div class="space-y-2">
                {{-- Make Offer Button --}}
                <button type="button" wire:click="$dispatch('open-make-offer-modal', { productId: {{ $product->id }} })"
                    class="w-full block text-center border font-bold py-2 rounded-md transition-colors text-sm"
                    style="border-color: var(--brand); color: var(--brand)">
                    Make an offer
                </button>

                {{-- Message Seller Button --}}
                <button type="button" wire:click="startConversation"
                    class="w-full block text-center border font-bold py-2 rounded-md transition-colors text-sm"
                    style="border-color: var(--brand); color: var(--brand)">
                    Ask seller
                </button>
            </div>
        @endif
    @else
        {{-- Buttons requiring login --}}
        <div class="space-y-2">
            <button type="button" x-data @click="$dispatch('open-auth-modal')"
                class="w-full border font-bold py-2 rounded-md transition-colors text-sm"
                style="border-color: var(--brand); color: var(--brand)">
                Make an offer
            </button>
            <button type="button" x-data @click="$dispatch('open-auth-modal')"
                class="w-full border font-bold py-2 rounded-md transition-colors text-sm"
                style="border-color: var(--brand); color: var(--brand)">
                Ask seller
            </button>
        </div>
    @endauth

    {{-- Include the MakeOfferModal component (it will be hidden initially) --}}
    @livewire('chat::make-offer-modal')
</div>