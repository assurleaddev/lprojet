{{-- resources/views/partials/_product_grid_items.blade.php --}}

@forelse ($products as $product)
    <div class="grid-item">
        <a href="{{ route('products.show', $product) }}" class="block cursor-pointer">
            <div class="relative">
            {{-- For better performance, we'll lazy-load images --}}
            <img data-src="{{ $product->getFeaturedImageUrl('preview') }}" src="{{ $product->getFeaturedImageUrl('preview') }}" class="lazy product-image" alt="{{ $product->name }}">
            <button class="fav-badge" aria-label="Favourite">
                <svg viewBox="0 0 24 24">
                <path d="M12 21s-7.2-4.2-9.3-8.4C1.3 10.1 2.1 6.9 4.8 5.7c1.8-.8 3.9-.3 5.2 1.1L12 8.8l2-2c1.3-1.4 3.4-1.9 5.2-1.1 2.7 1.2 3.5 4.4 2.1 6.9C19.2 16.8 12 21 12 21z"/>
                </svg>
                <span>{{-- Favorite count --}}</span>
            </button>
            </div>
            <div class="pt-1.5">
            <p class="brand-line">{{ $product->name }}</p>
            <p class="meta-line">{{ $product->getOptionsSummaryAttribute() }}</p>
            <p class="price-line">{{ $product->price }} MAD</p>
            <div class="incl-line">
                <span>30.70 MAD incl.</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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