{{-- resources/views/livewire/product-messaging-button.blade.php --}}
<div>
    @auth
        @if(auth()->id() !== $product->vendor_id)
            {{-- The actual button that triggers the Livewire action --}}
            <button type="button" 
               wire:click="startConversation" 
               class="inline-flex items-center justify-center rounded-md bg-blue-600 px-6 py-3 text-base font-medium text-white hover:bg-blue-700"
            >
                <i class="fas fa-comment-alt mr-2"></i> {{ __('Message Seller') }}
            </button>
        @endif
    @else
        {{-- Button to trigger your login modal --}}
        <button type="button" x-data @click="$dispatch('open-auth-modal')" class="text-blue-500 hover:underline">
            {{ __('Log in to message the seller') }}
        </button>
    @endauth
</div>