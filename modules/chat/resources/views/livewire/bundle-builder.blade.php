<div x-data="{ open: @entangle('isOpen') }" x-show="open" @open-bundle-builder.window="open = true"
    style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop --}}
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-10 transition-opacity bg-black/50" @click="open = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal Panel --}}
        <div x-show="open" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative z-20 inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:align-middle">

            {{-- Header --}}
            <div class="flex justify-between items-center mb-1">
                <h2 class="text-xl font-bold text-gray-900">Shop Bundle</h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="text-sm text-gray-500 mb-4">Select the items you want to buy from
                {{ $vendor->username }}.
            </p>

            {{-- Discount Tiers Preview --}}
            @php
                $bundleTiers = $vendor->bundleDiscounts()->orderBy('min_items')->get();
            @endphp
            @if($bundleTiers->count() > 0)
                <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl mb-4"
                    style="background: #fff5f5; border: 1px solid #ffe0e0;">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        style="color: var(--brand)">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($bundleTiers as $tier)
                            <span
                                class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full {{ in_array(count($selectedProducts), range($tier->min_items, 100)) ? 'text-white' : 'text-gray-700 bg-gray-100' }}"
                                @if(count($selectedProducts) >= $tier->min_items) style="background-color: var(--brand)" @endif>
                                {{ $tier->min_items }}+ items:
                                <span class="font-bold">{{ $tier->discount_percentage }}% off</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Product Grid --}}
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 max-h-[50vh] overflow-y-auto pr-1">
                @foreach($vendorProducts as $product)
                            <div wire:key="prod-{{ $product->id }}" wire:click="toggleProduct({{ $product->id }})" class="relative rounded-xl overflow-hidden cursor-pointer transition-all border-2
                                        {{ in_array($product->id, $selectedProducts)
                    ? 'border-red-500 ring-1 ring-red-500 bg-red-50'
                    : 'border-transparent hover:border-gray-300' }}">

                                {{-- Image --}}
                                <div class="relative w-full" style="aspect-ratio: 3/4;">
                                    <img src="{{ $product->getFeaturedImageUrl('preview') }}" alt="{{ $product->name }}"
                                        class="absolute inset-0 w-full h-full object-cover rounded-t-lg">

                                    {{-- Checkmark --}}
                                    @if(in_array($product->id, $selectedProducts))
                                        <div class="absolute top-1.5 right-1.5 text-white rounded-full p-0.5 shadow"
                                            style="background-color: var(--brand)">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="p-1.5">
                                    <p class="text-[11px] font-medium text-gray-800 truncate leading-tight">{{ $product->name }}</p>
                                    <p class="text-xs font-bold mt-0.5" style="color: var(--brand)">
                                        {{ number_format($product->price, 2) }} MAD
                                    </p>
                                </div>
                            </div>
                @endforeach
            </div>

            {{-- Footer --}}
            @php
                $selectedProds = $vendorProducts->whereIn('id', $selectedProducts);
                $total = $selectedProds->sum('price');
                $discount = $vendor->bundleDiscounts()
                    ->where('min_items', '<=', count($selectedProducts))
                    ->orderByDesc('min_items')
                    ->first();
                $finalTotal = $total;
                $savings = 0;
                if ($discount) {
                    $finalTotal = $total * (1 - ($discount->discount_percentage / 100));
                    $savings = $total - $finalTotal;
                }
            @endphp

            <div class="mt-5 pt-4 border-t flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Selected: <span
                            class="font-bold text-gray-900">{{ count($selectedProducts) }} items</span>
                    </p>
                    <div class="mt-0.5">
                        @if($discount)
                            <p class="text-xs text-gray-400 line-through">{{ number_format($total, 2) }} MAD</p>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($finalTotal, 2) }} MAD</p>
                            <p class="text-xs font-semibold" style="color: var(--brand)">
                                You save {{ number_format($savings, 2) }} MAD ({{ $discount->discount_percentage }}% off)
                            </p>
                        @else
                            <p class="text-lg font-bold text-gray-900">{{ number_format($total, 2) }} MAD</p>
                            @if($bundleTiers->count() > 0 && count($selectedProducts) > 0)
                                @php $nextTier = $bundleTiers->where('min_items', '>', count($selectedProducts))->first() ?? $bundleTiers->first(); @endphp
                                @if($nextTier && count($selectedProducts) < $nextTier->min_items)
                                    <p class="text-xs text-gray-500">Add {{ $nextTier->min_items - count($selectedProducts) }} more
                                        for {{ $nextTier->discount_percentage }}% off!</p>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>

                <button wire:click="sendRequest" wire:loading.attr="disabled"
                    class="px-6 py-2.5 text-white text-sm font-bold rounded-full shadow-md hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                    style="background-color: var(--brand)">
                    <span wire:loading wire:target="sendRequest" class="animate-pulse">Sending...</span>
                    <span wire:loading.remove wire:target="sendRequest">Request Bundle</span>
                </button>
            </div>
        </div>
    </div>
</div>