@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-[1200px] px-6 md:px-10">
        <div class="h-6"></div>
        <h1 class="text-2xl font-semibold mb-6">Favourited items</h1>

        @if($products->count() > 0)
            <div class="grid-container">
                @foreach ($products as $product)
                    <div class="grid-item">
                        <a href="{{ route('products.show', $product) }}" class="block cursor-pointer">
                            <div class="relative">
                                {{-- For better performance, we'll lazy-load images --}}
                                <img src="{{ $product->getFeaturedImageUrl('preview') }}" class="product-image"
                                    alt="{{ $product->name }}">

                                @if($product->status === 'sold')
                                    <div
                                        class="absolute bottom-0 left-0 right-0 bg-[#4fb286] text-white text-[11px] font-bold px-3 py-1.5">
                                        Vendus
                                    </div>
                                @elseif($product->status === 'reserved')
                                    <div
                                        class="absolute bottom-0 left-0 right-0 bg-amber-500 text-white text-[11px] font-bold px-3 py-1.5">
                                        Réservé
                                    </div>
                                @endif

                                <button class="fav-badge" aria-label="Favourite">
                                    <svg viewBox="0 0 24 24" class="text-red-500 fill-current">
                                        <path
                                            d="M12 21s-7.2-4.2-9.3-8.4C1.3 10.1 2.1 6.9 4.8 5.7c1.8-.8 3.9-.3 5.2 1.1L12 8.8l2-2c1.3-1.4 3.4-1.9 5.2-1.1 2.7 1.2 3.5 4.4 2.1 6.9C19.2 16.8 12 21 12 21z" />
                                    </svg>
                                    <span>{{ $product->favoritedBy()->count() }}</span>
                                </button>
                            </div>
                            <div class="pt-1.5">
                                <p class="brand-line">{{ $product->name }}</p>
                                <p class="meta-line">{{ $product->getOptionsSummaryAttribute() }}</p>
                                <p class="price-line">{{ $product->price }} MAD</p>
                                <div class="incl-line">
                                    <span>{{ number_format($product->price + 5, 2) }} MAD incl.</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links('pagination::tailwind') }}
            </div>
        @else
            <div class="text-center py-20">
                <h2 class="text-xl text-gray-600">No favourited items yet.</h2>
                <a href="{{ route('home') }}"
                    class="mt-4 inline-block px-6 py-2 bg-teal-600 text-white rounded-md hover:bg-teal-700">Start exploring</a>
            </div>
        @endif
    </div>
@endsection