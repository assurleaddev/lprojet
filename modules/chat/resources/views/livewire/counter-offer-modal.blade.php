<div x-data="{ show: @entangle('showModal') }" x-show="show" x-on:keydown.escape.window="show = false"
    style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">

    <div class="flex items-end sm:items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-gray-500 opacity-75 dark:bg-gray-900 dark:bg-opacity-75 z-[60]"
            @click.self="show = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" @click.stop x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg sm:align-middle z-[70]">

            @if($this->product)
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">
                    Make a Counter Offer for {{ $this->product->name }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Original Price:
                    ${{ number_format($this->product->price, 2) }}
                </p>

                <form wire:submit.prevent="submitCounterOffer" class="mt-4 space-y-4">
                    <div>
                        <label for="counterOfferPriceInput"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Your Counter Offer
                            ($)</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $this->product->price }}"
                            wire:model="offerPrice" id="counterOfferPriceInput"
                            class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200"
                            required>
                        @error('offerPrice') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Must be less than or equal to original
                            price.</p>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4 border-t dark:border-gray-600">
                        <button type="button" @click="show = false" wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="submitCounterOffer"
                            class="px-4 py-2 text-sm font-medium text-white bg-teal-600 border border-transparent rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50">
                            <span wire:loading wire:target="submitCounterOffer" class="animate-pulse">Sending...</span>
                            <span wire:loading.remove wire:target="submitCounterOffer">Send Counter Offer</span>
                        </button>
                    </div>
                </form>
            @else
                <p class="text-center text-red-500">Could not load product information.</p>
                <div class="flex justify-end mt-4">
                    <button type="button" @click="show = false" wire:click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                        Close
                    </button>
                </div>
            @endif

        </div>
    </div>
</div>