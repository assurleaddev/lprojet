{{-- resources/views/partials/_product_grid_items.blade.php --}}

@forelse ($products as $product)
    <div class="grid-item relative">
        <div class="used-image-wrapper">
            <a href="{{ route('products.show', $product) }}"
               class="absolute inset-0 z-10 cursor-pointer block js-product-click"
               data-product-id="{{ $product->id }}"
               data-source="{{ request()->routeIs('home') ? 'homepage' : (request()->routeIs('search') ? 'search' : 'browse') }}"></a>
            {{-- For better performance, we'll lazy-load images --}}
            <img data-src="{{ $product->getFeaturedImageUrl('preview') }}"
                src="{{ $product->getFeaturedImageUrl('preview') }}" class="lazy used-image-content"
                alt="{{ $product->name }}">

            {{-- Bundle badge --}}
            @if($product->vendor && $product->vendor->bundleDiscounts()->exists())
                <div class="absolute top-1.5 left-1.5 z-20 bg-white/90 backdrop-blur-sm text-[10px] font-bold px-1.5 py-0.5 rounded-md flex items-center gap-1 shadow-sm"
                    style="color: var(--brand)">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Bundle
                </div>
            @endif

            {{-- Trending fire badge --}}
            @if(in_array($product->id, $trendingProductIds ?? []))
                <div class="absolute top-2 right-2 z-20 flex items-center gap-1 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg pointer-events-none"
                     style="background-color: #111827;"
                     title="{{ __('Trending') }}">
                    <svg class="w-3 h-3 text-orange-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C9.5 6 10 9 8 11c-.8-2-2-3.5-2-3.5C4.5 10 3 13 3 15.5 3 19.6 7 23 12 23s9-3.4 9-7.5C21 10 15.5 5.5 12 2zm0 19c-3.3 0-6-2.2-6-5 0-1.5.8-3 2-4 .3 1 .9 2 1.8 2.5C10 12 11 9.5 11 7c2 2.5 2.5 5 1.5 7.5 1-.5 1.8-1.5 2-2.5 1.2 1 2 2.5 2 4C16.5 19.8 15.3 21 12 21z"/>
                    </svg>
                    <span class="tracking-wide uppercase">{{ __('Trending') }}</span>
                </div>
            @endif

            @if($product->status === 'sold')
                <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20"
                    style="background-color: #4fb286 !important;">
                    {{ __('Sold') }}
                </div>
            @elseif($product->status === 'reserved')
                <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20"
                    style="background-color: #f59e0b !important;">
                    {{ __('Reserved') }}
                </div>
            @endif

            @if(auth()->id() !== $product->vendor_id)
                <button class="fav-badge z-30" aria-label="Favourite" data-id="{{ $product->id }}"
                    data-url="{{ route('products.favorite', $product) }}">
                    <svg viewBox="0 0 24 24"
                        class="{{ $product->isFavorited() ? '!text-red-500 !fill-current !stroke-current' : '' }}">
                        <path
                            d="M12 21s-7.2-4.2-9.3-8.4C1.3 10.1 2.1 6.9 4.8 5.7c1.8-.8 3.9-.3 5.2 1.1L12 8.8l2-2c1.3-1.4 3.4-1.9 5.2-1.1 2.7 1.2 3.5 4.4 2.1 6.9C19.2 16.8 12 21 12 21z" />
                    </svg>
                    <span>{{ $product->favoritedBy()->count() }}</span>
                </button>
            @endif
        </div>
        
    <a href="{{ route('products.show', $product) }}" class="block cursor-pointer">
        <div class="pt-1.5">
            <p class="brand-line">{{ $product->name }}</p>
            <p class="meta-line">{{ $product->getOptionsSummaryAttribute() }}</p>
            <p class="price-line">{{ $product->price }} MAD</p>
            @php
                $bpPercent = (float) config('settings.buyer_protection_fee_percentage', 5);
                $bpFixed   = (float) config('settings.buyer_protection_fee_fixed', 0.70);
                $bpFee     = ($product->price * $bpPercent / 100) + $bpFixed;
                $inclTotal = $product->price + $bpFee;
            @endphp
            <div class="incl-line">
                <span>{{ number_format($inclTotal, 2) }} MAD incl.</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </a>
    </div>

    {{-- ✨ BANNER LOGIC: Insert a sell banner after every 16th product ✨ --}}
    @if ($loop->iteration > 0 && $loop->iteration % 16 == 0)
        <div class="col-span-full relative rounded-lg overflow-hidden h-[240px] md:h-[300px]">
            <img src="{{ asset('images/home/banner.png') }}" alt=""
                class="absolute inset-0 w-full h-full object-cover object-[70%_30%]" loading="lazy">
            {{-- Left-to-right scrim keeps the text readable over any crop of the photo --}}
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/35 to-transparent"></div>
            <div class="relative h-full max-w-xl flex flex-col items-start justify-center gap-2 p-6 md:p-10">
                <h2 class="text-2xl md:text-3xl font-bold text-white">{{ __('Earn money from your wardrobe') }}</h2>
                <p class="text-sm md:text-base text-white/90">{{ __('List your items in minutes and reach thousands of buyers.') }}</p>
                <a href="{{ route('items.create') }}"
                    class="mt-3 px-5 py-2.5 rounded font-bold text-sm text-gray-900 bg-white hover:bg-gray-100">{{ __('Sell now') }}</a>
            </div>
        </div>
    @endif
@empty
    {{-- This prevents the "No products found" message from appearing on the final AJAX call --}}
@endforelse