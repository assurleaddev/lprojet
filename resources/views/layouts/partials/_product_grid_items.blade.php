{{-- resources/views/partials/_product_grid_items.blade.php --}}

@forelse ($products as $product)
    <div class="grid-item relative">
        <div class="used-image-wrapper">
            <a href="{{ route('products.show', $product) }}" class="absolute inset-0 z-10 cursor-pointer block"></a>
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

            @if($product->status === 'sold')
                <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20"
                    style="background-color: #4fb286 !important;">
                    Sold
                </div>
            @elseif($product->status === 'reserved')
                <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20"
                    style="background-color: #f59e0b !important;">
                    Reserved
                </div>
            @endif

            @if(auth()->id() !== $product->vendor_id)
                <button class="fav-badge z-30" aria-label="Favourite" data-id="{{ $product->id }}"
                    data-url="{{ route('products.favorite', $product) }}">
                    <svg viewBox="0 0 24 24"
                        class="{{ $product->isFavorited() ? '!text-red-500 !fill-current !stroke-current' : '' }} transition-colors">
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
            <div class="incl-line">
                <span>30.70 MAD incl.</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </a>
    </div>

    {{-- ✨ BANNER LOGIC: Insert a banner after every 20th product ✨ --}}
    @if ($loop->iteration > 0 && $loop->iteration % 15 == 0)
        <div class="col-span-full bg-[#f6f2ff] rounded-lg p-8 min-h-[150px] h-[300px] flex flex-col items-start justify-between bannner"
            style="background-repeat:no-repeat;background-position:right center;background-size:cover;background-image: url('{{ asset('images/home/banner.png') }}')">
            <h2 class="text-xl md:text-2xl font-bold mb-4">Earn money from your homeware</h2>
            <a href="#" class="px-4 py-2 rounded text-white font-bold text-sm" style="background:var(--brand)">List now</a>
        </div>
    @endif
@empty
    {{-- This prevents the "No products found" message from appearing on the final AJAX call --}}
@endforelse