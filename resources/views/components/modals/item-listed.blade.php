@props(['product' => null])

@php
    $initialProduct = null;
    if ($product) {
        $initialProduct = [
            'image_url' => $product->getFirstMediaUrl('featured') ?: $product->getFirstMediaUrl('products'),
            'name' => $product->name,
            'brand_name' => $product->brand ? $product->brand->name : 'No Brand',
            'condition_label' => $product->condition ? ucwords(str_replace('_', ' ', $product->condition)) : 'Pre-owned',
            'price_formatted' => number_format($product->price, 2)
        ];
    }
@endphp

<div x-data="{ 
        show: {{ $initialProduct ? 'true' : 'false' }}, 
        product: @json($initialProduct) 
    }" 
    @item-listed.window="show = true; product = $event.detail.product"
    x-show="show" 
    class="relative z-50"
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true"
    x-cloak>
    
    <div x-show="show" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500 opacity-75 transition-opacity backdrop-blur-sm"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="show" 
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.away="window.location.reload()"
                class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                
                <!-- Decorative Elements -->
                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                    <!-- Top Left Confetti -->
                    <svg class="absolute top-10 left-10 w-12 h-12 text-blue-200 transform -rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <!-- Bottom Right Swirl -->
                    <svg class="absolute bottom-10 right-10 w-16 h-16 text-teal-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>

                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 relative z-10">
                    <div class="text-center">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 mb-6" id="modal-title">Item listed</h3>
                        
                        <!-- Product Card -->
                        <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4 mb-6 max-w-xs mx-auto transform rotate-1" x-show="product">
                            <div class="aspect-square w-full bg-gray-100 rounded-md overflow-hidden mb-3">
                                <img :src="product?.image_url" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="text-left">
                                <p class="text-sm text-gray-500 mb-1" x-text="product?.brand_name"></p>
                                <p class="text-sm font-medium text-gray-900 mb-1" x-text="product?.name.substring(0, 30) + (product?.name.length > 30 ? '...' : '')"></p>
                                <p class="text-xs text-gray-500 mb-2" x-text="product?.condition_label"></p>
                                <p class="text-base font-bold text-gray-900">€<span x-text="product?.price_formatted"></span></p>
                            </div>
                        </div>

                        <!-- Info Text -->
                        <p class="text-sm text-gray-600 mb-6">
                            Did you know that you appear in search results more often when you have more listings?
                        </p>

                        <!-- Actions -->
                        <div class="space-y-3">
                            <button type="button" 
                                @click="window.location.reload()"
                                class="w-full inline-flex justify-center rounded-md bg-teal-600 px-3 py-3 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-600">
                                List another
                            </button>
                            <a href="{{ route('home') }}" 
                               class="block w-full text-sm font-medium text-teal-600 hover:text-teal-500 cursor-pointer">
                                Later
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>