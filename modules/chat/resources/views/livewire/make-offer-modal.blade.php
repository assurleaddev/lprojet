<div x-data="{ 
    show: @entangle('showModal'),
    offerType: 'custom', // 'custom', '10', '20'
    originalPrice: @entangle('productPrice'),
    offerPrice: @entangle('offerPrice'),
    
    init() {
        // No need to watch 'show' anymore as originalPrice is entangled
    },

    setDiscount(percent) {
        this.offerType = percent;
        let discount = this.originalPrice * (percent / 100);
        this.offerPrice = (this.originalPrice - discount).toFixed(2);
    },

    setCustom() {
        this.offerType = 'custom';
        this.offerPrice = '';
    }
}" x-show="show" x-on:keydown.escape.window="show = false" style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">

    <div class="flex items-end sm:items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-gray-500 opacity-75 z-[60]" @click="show = false"
            aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" @click.stop x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg sm:align-middle z-[70]">

            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button @click="show = false" type="button"
                    class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            @if($product)
                <div class="text-center mb-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                        Make an offer
                    </h3>
                </div>

                <div class="flex items-start gap-4 mb-6">
                    <img src="{{ $product->getFeaturedImageUrl('preview') }}" alt="{{ $product->name }}"
                        class="w-12 h-16 object-cover rounded-md bg-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                        <p class="text-sm text-gray-500">Item price: {{ $product->price }} MAD</p>
                    </div>
                </div>

                <form wire:submit.prevent="submitOffer">
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <button type="button" @click="setDiscount(10)"
                            :class="{'border-teal-600 text-teal-600 bg-teal-50': offerType == 10, 'border-gray-200 text-gray-700 hover:border-gray-300': offerType != 10}"
                            class="border rounded-md py-2 px-3 text-center transition-colors">
                            <div class="text-sm font-bold" x-text="(originalPrice * 0.9).toFixed(2)"></div>
                            <div class="text-xs">10% off</div>
                        </button>
                        <button type="button" @click="setDiscount(20)"
                            :class="{'border-teal-600 text-teal-600 bg-teal-50': offerType == 20, 'border-gray-200 text-gray-700 hover:border-gray-300': offerType != 20}"
                            class="border rounded-md py-2 px-3 text-center transition-colors">
                            <div class="text-sm font-bold" x-text="(originalPrice * 0.8).toFixed(2)"></div>
                            <div class="text-xs">20% off</div>
                        </button>
                        <button type="button" @click="setCustom()"
                            :class="{'border-teal-600 text-teal-600 bg-teal-50': offerType == 'custom', 'border-gray-200 text-gray-700 hover:border-gray-300': offerType != 'custom'}"
                            class="border rounded-md py-2 px-3 text-center transition-colors">
                            <div class="text-sm font-bold">Custom</div>
                            <div class="text-xs">Set a price</div>
                        </button>
                    </div>

                    <div class="mb-6">
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">MAD</span>
                            </div>
                            <input type="number" step="0.01" min="0.01" max="{{ $product->price }}" wire:model="offerPrice"
                                class="block w-full rounded-md border-0 py-1.5 pl-12 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-teal-600 sm:text-sm sm:leading-6"
                                placeholder="0.00">
                        </div>
                        @error('offerPrice') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                        <div class="mt-2 text-xs text-gray-500 flex justify-between">
                            <span>{{ number_format((floatval($offerPrice ?: 0) * 1.05) + 10, 2) }} MAD incl. Buyer
                                Protection fee</span>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mb-6">
                        You have 25 offer(s) left today. This limit makes it easier for members to manage and review them.
                    </p>

                    <button type="submit" wire:loading.attr="disabled" wire:target="submitOffer"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#007782] hover:bg-[#006670] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50">
                        <span wire:loading wire:target="submitOffer" class="animate-pulse">Sending...</span>
                        <span wire:loading.remove wire:target="submitOffer">Offer</span>
                    </button>
                </form>
            @else
                <p class="text-center text-red-500">Could not load product information.</p>
            @endif
        </div>
    </div>
</div>