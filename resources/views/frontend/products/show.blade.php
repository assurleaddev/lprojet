@extends('layouts.app')

{{-- @section('title', $product->name) --}}
@section('before_head')
    <style>
        /* Custom scrollbar for webkit browsers */
        .custom-scrollbar::-webkit-scrollbar {
            height: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .like-btn {
            position: absolute;
            right: 10px;
            bottom: 10px;
            height: 40px;
            border-radius: 999px;
            place-items: center;
            background: #fff;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .15);
        }

        .like-btn:hover {
            transform: translateY(-1px)
        }
    </style>
@endsection
@section('content')
    {{-- {{ dd($product->media[0]) }} --}}
    <main class="bg-vinted-gray-100 pb-20 md:pb-0">
        <div class="container mx-auto px-0 md:px-6 py-0 md:py-6">
            <nav class="px-4 md:px-0 text-xs text-vinted-gray-500 mb-4 mt-4 md:mt-0 space-x-1.5 flex flex-wrap items-center">
                @foreach($breadcrumbs as $category)
                    <a href="{{ route('search', ['categories' => [$category->id]]) }}"
                        class="hover:underline">{{ $category->translated_name }}</a>
                    <span>›</span>
                @endforeach
                <a href="#" class="font-semibold hover:underline text-gray-900">
                    {{ $product->brand ? $product->brand->name . ', ' : '' }}{{ $product->name }}
                </a>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-8 relative self-start">
                    @php
                        $mediaItems = $product->getMedia('products');
                        $mediaCount = $mediaItems->count();
                        // Use the dedicated featured image if set, otherwise fall back to first product image
                        $featuredUrl = $product->hasFeaturedImage()
                            ? $product->getFeaturedImageUrl()
                            : ($mediaCount > 0 ? $mediaItems[0]->getUrl() : null);
                    @endphp

                        @if($mediaCount <= 1)
                            <div class="w-full">
                                <img src="{{ $featuredUrl }}" alt="{{ $product->name }}"
                                    class="w-full h-[400px] md:h-[600px] lg:h-[800px] object-cover cursor-pointer product-gallery-image rounded">
                            </div>
                        @elseif($mediaCount == 2)
                            <div class="grid grid-cols-2 gap-1.5">
                                <img src="{{ $featuredUrl }}" alt="{{ $product->name }}"
                                    class="w-full h-[400px] md:h-[600px] lg:h-[800px] object-cover cursor-pointer product-gallery-image rounded-l">
                                <img src="{{ $mediaItems[1]->getUrl() }}" alt="{{ $product->name }}"
                                    class="w-full h-[400px] md:h-[600px] lg:h-[800px] object-cover cursor-pointer product-gallery-image rounded-r">
                            </div>
                        @elseif($mediaCount == 3)
                            <div class="grid grid-cols-2 gap-1.5">
                                <img src="{{ $featuredUrl }}" alt="{{ $product->name }}"
                                    class="w-full h-[400px] md:h-[600px] lg:h-[800px] object-cover cursor-pointer product-gallery-image rounded-l">
                                <div class="grid grid-rows-2 gap-1.5 h-[400px] md:h-[600px] lg:h-[800px]">
                                    <img src="{{ $mediaItems[1]->getUrl() }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover cursor-pointer product-gallery-image rounded-tr">
                                    <img src="{{ $mediaItems[2]->getUrl() }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover cursor-pointer product-gallery-image rounded-br">
                                </div>
                            </div>
                        @elseif($mediaCount == 4)
                            <div class="grid grid-cols-2 gap-1.5">
                                <img src="{{ $featuredUrl }}" alt="{{ $product->name }}"
                                    class="w-full h-[400px] md:h-[600px] lg:h-[800px] object-cover cursor-pointer product-gallery-image rounded-l">
                                <div class="grid grid-rows-2 gap-1.5 h-[400px] md:h-[600px] lg:h-[800px]">
                                    <img src="{{ $mediaItems[1]->getUrl() }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover cursor-pointer product-gallery-image rounded-tr">
                                    <div class="grid grid-cols-2 gap-1.5 h-full">
                                        <img src="{{ $mediaItems[2]->getUrl() }}" alt="{{ $product->name }}"
                                            class="w-full h-full object-cover cursor-pointer product-gallery-image">
                                        <img src="{{ $mediaItems[3]->getUrl() }}" alt="{{ $product->name }}"
                                            class="w-full h-full object-cover cursor-pointer product-gallery-image rounded-br">
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-1.5">
                                <img src="{{ $featuredUrl }}" alt="{{ $product->name }}"
                                    class="w-full h-[400px] md:h-[600px] lg:h-[800px] object-cover cursor-pointer product-gallery-image rounded-l">
                                <div class="grid grid-rows-2 grid-cols-2 gap-1.5 h-[400px] md:h-[600px] lg:h-[800px]">
                                    <img src="{{ $mediaItems[1]->getUrl() }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover cursor-pointer product-gallery-image">
                                    <img src="{{ $mediaItems[2]->getUrl() }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover cursor-pointer product-gallery-image rounded-tr">                                    <img src="{{ $mediaItems[3]->getUrl() }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover cursor-pointer product-gallery-image">
                                    <div class="relative w-full h-full cursor-pointer group overflow-hidden rounded-br" onclick="this.querySelector('img').click()">
                                        <img src="{{ $mediaItems[4]->getUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover product-gallery-image group-hover:scale-105 transition-transform duration-500">
                                        @if($mediaCount > 5)
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center text-white text-3xl font-bold group-hover:bg-black/50 transition duration-300">
                                                +{{ $mediaCount - 5 }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @php
                        $liked = auth()->check() && $product->isFavorited();
                        $count = $product->favoritedBy()->count();
                    @endphp

                    @if(auth()->id() !== $product->vendor_id)
                        <button type="button"
                            class="like-btn absolute bottom-0 right-0 px-4 py-4 flex items-center justify-center text-xl {{ $liked ? 'text-red-500' : 'text-gray-400' }}"
                            data-product-id="{{ $product->id }}" aria-label="Add to favourites"
                            aria-pressed="{{ $liked ? 'true' : 'false' }}">
                            <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
                                <path
                                    d="M12 21s-7.5-4.46-9.5-8.32C1 8.86 3.42 6 6.5 6c1.74 0 3.41.81 4.5 2.09C12.09 6.81 13.76 6 15.5 6 18.58 6 21 8.86 21.5 12.68 19.5 16.54 12 21 12 21z"
                                    fill="currentColor" />
                            </svg>
                            <span
                                class="like-count ml-2 text-base {{ $count > 0 ? '' : 'hidden' }}">{{ $count > 0 ? $count : '' }}</span>
                        </button>
                    @endif
                </div>



                <aside class="lg:col-span-4 space-y-4">
                    <!-- Product Info Card -->
                    <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-100">

                        <!-- Title -->
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h1 class="text-xl font-medium text-gray-900 leading-snug">{{ $product->name }}</h1>
                            @if($product->created_at->diffInDays() <= 7)
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Nouveau</span>
                            @endif
                        </div>

                        <!-- Subtitle (Size - Condition - Brand) -->
                        <div class="text-sm text-gray-500 mb-4 flex items-center gap-1">
                            @if($product->size) <span>{{ $product->size }}</span> <span class="text-gray-300">•</span>
                            @endif
                            @if($product->condition) <span>{{ ucwords(str_replace('_', ' ', $product->condition)) }}</span>
                            <span class="text-gray-300">•</span> @endif
                            @if($product->brand) <a href="#" class="hover:underline"
                            class="text-gray-900">{{ $product->brand->name }}</a> @endif
                        </div>

                        <!-- Price Block -->
                        <div class="mb-4">
                            <!-- Item Price (Gray) -->
                            <div class="text-gray-500 text-lg font-bold">{{ number_format($product->price, 2) }} MAD</div>
                            <!-- Total Price (Teal, Bold) - Approximated Total -->
                            <div class="text-xl font-bold text-gray-900">{{ number_format($product->price + $protectionFee, 2) }} MAD</div>

                            <!-- Buyer Protection Label -->
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="text-sm text-gray-900">{{ __('Includes Buyer Protection') }}</span>
                                <svg class="w-4 h-4 text-gray-900" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            @php
                                $verificationThreshold = (float) config('settings.product_verification_threshold', 500);
                                $verificationFee       = (float) config('settings.product_verification_fee', 50);
                            @endphp
                            @if($product->price >= $verificationThreshold)
                                <div class="mt-2 flex items-start gap-2 bg-green-50 border border-green-200 rounded-md px-3 py-2">
                                    <svg class="w-4 h-4 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <p class="text-xs text-green-700 leading-relaxed">
                                        {{ __('Verification available') }} — {{ __('Have this item physically inspected before shipping for') }} <span class="font-semibold">{{ number_format($verificationFee, 2) }} MAD</span>. {{ __('Select it at checkout.') }}
                                    </p>
                                </div>
                            @endif

                            @if(auth()->check() && auth()->user()->wallet)
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ number_format(auth()->user()->wallet->balance, 2) }} MAD dans votre solde pour
                                    vos achats
                                </div>
                            @endif

                            @if($product->status === 'reserved' && $product->buyer)
                                <div
                                    class="mt-3 p-2 bg-gray-100 border border-gray-300 rounded text-sm text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span>Réservé pour <strong>{{ $product->buyer->username }}</strong></span>
                                </div>
                            @endif
                        </div>

                        <hr class="border-gray-100 mb-5">

                        <!-- Attributes Table -->
                        <div class="space-y-3 text-sm mb-6">
                            @if($product->brand)
                                <div class="grid grid-cols-2">
                                    <span class="text-gray-500 font-bold">{{ __('Brand') }}</span>
                                    <a href="#" class="hover:underline"
                                        class="text-gray-900">{{ $product->brand->name }}</a>
                                </div>
                            @endif
                            @if($product->size)
                                <div class="grid grid-cols-2">
                                    <span class="text-gray-500 font-bold">{{ __('Size') }}</span>
                                    <span class="text-gray-700 uppercase">{{ $product->size }}</span>
                                </div>
                            @endif
                            @if($product->condition)
                                <div class="grid grid-cols-2">
                                    <span class="text-gray-500 font-bold">{{ __('Condition') }}</span>
                                    <span class="text-gray-700">{{ ucwords(str_replace('_', ' ', $product->condition)) }}</span>
                                </div>
                            @endif
                            @if(!empty($product->fabric))
                                <div class="grid grid-cols-2">
                                    <span class="text-gray-500 font-bold">{{ __('Fabric') }}</span>
                                    <span class="text-gray-700">{{ implode(', ', $product->fabric) }}</span>
                                </div>
                            @endif
                            @if($product->color)
                                <div class="grid grid-cols-2">
                                    <span class="text-gray-500 font-bold">{{ __('Colour') }}</span>
                                    <span class="text-gray-700">{{ $product->color }}</span>
                                </div>
                            @endif
                            <div class="grid grid-cols-2">
                                <span class="text-gray-500 font-bold">{{ __('Uploaded') }}</span>
                                <span class="text-gray-700">{{ $product->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <hr class="border-gray-100 mb-5">

                        <!-- Description -->
                        <div class="mb-5">
                            <div class="text-sm text-gray-700 leading-relaxed mb-2 prose prose-sm max-w-none">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                            <button class="text-sm hover:underline flex items-center gap-1 text-gray-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                                {{ __('Translate this') }}
                            </button>
                        </div>

                        <hr class="border-gray-100 mb-5">

                        <!-- Shipping Estimates -->
                        <div class="flex justify-between items-center text-sm mb-6">
                            <span class="text-gray-500 font-bold">{{ __('Shipping') }}</span>
                            <span class="text-gray-700">à partir de {{ number_format($deliveryFeeFixed, 2) }} MAD</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            @auth
                                @if(auth()->id() === $product->vendor_id)
                                    {{-- Owner Actions --}}
                                    <div class="p-4 bg-gray-50 border border-gray-100 rounded-md text-center">
                                        <p class="text-sm text-gray-600 mb-3">{{ __('This is your item') }}</p>
                                        <div class="space-y-2">
                                            @if($product->status === 'pending')
                                                <div class="w-full py-2 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded text-sm font-medium text-center">
                                                    {{ __('Pending approval') }}
                                                </div>
                                                <a href="{{ route('items.edit', $product) }}"
                                                    class="block w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50 text-center">
                                                    {{ __('Edit listing') }}
                                                </a>
                                            @else
                                                <!-- Bump Button -->
                                                <button type="button" @click="$dispatch('open-bump-modal')"
                                                    class="w-full py-2 text-white rounded text-sm font-bold border border-transparent bg-gray-900 hover:bg-gray-800">
                                                    Bump
                                                </button>

                                                <!-- Mark as Sold -->
                                                @if($product->status !== 'sold')
                                                    <form action="{{ route('items.markAsSold', $product) }}" method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                            class="w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">
                                                            {{ __('Mark as sold') }}
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Mark as Reserved / Unreserve -->
                                                @if($product->status === 'reserved')
                                                    <form action="{{ route('items.unreserve', $product) }}" method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                            class="w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">
                                                            {{ __('Mark as unreserved') }}
                                                        </button>
                                                    </form>
                                                @elseif($product->status !== 'sold')
                                                    <button type="button" @click="$dispatch('open-reserve-modal')"
                                                        class="w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">
                                                        {{ __('Reserve') }}
                                                    </button>
                                                @endif

                                                <!-- Hide / Unhide -->
                                                @if($product->status === 'hidden')
                                                    <button type="button" @click="$dispatch('open-unhide-modal')"
                                                        class="w-full py-2 bg-white text-gray-700 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">
                                                        Unhide
                                                    </button>
                                                @else
                                                    <button type="button" @click="$dispatch('open-hide-modal')"
                                                        class="w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">
                                                        Hide
                                                    </button>
                                                @endif

                                                <!-- Edit Button -->
                                                <a href="{{ route('items.edit', $product) }}"
                                                    class="block w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50 text-center">
                                                    {{ __('Edit listing') }}
                                                </a>
                                            @endif

                                            <!-- Delete Button (always visible to owner) -->
                                            <button type="button" @click="$dispatch('open-delete-modal')"
                                                class="w-full py-2 bg-white text-red-600 border border-red-200 rounded text-sm font-bold hover:bg-red-50">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    {{-- Buyer Actions --}}
                                    <div class="hidden md:block md:space-y-3">
                                        @if($product->status === 'reserved' && $product->buyer_id !== auth()->id())
                                            <button disabled
                                                class="w-full py-2.5 bg-gray-200 text-gray-400 font-medium rounded text-sm cursor-not-allowed">{{ __('Reserved') }}</button>
                                        @else
                                            <a href="{{ route('product.checkout', $product) }}"
                                                class="block w-full py-2.5 text-center text-white font-medium rounded transition-colors text-sm bg-gray-900 hover:bg-gray-800">
                                                {{ __('Buy now') }}
                                            </a>
                                            @livewire('product-messaging-button', ['product' => $product])
                                        @endif
                                    </div>
                                @endif
                            @else
                                {{-- Guest Actions --}}
                                <div class="hidden md:block md:space-y-3">
                                    @if($product->status === 'reserved')
                                        <button disabled
                                            class="w-full py-2.5 bg-gray-200 text-gray-400 font-medium rounded text-sm cursor-not-allowed">{{ __('Reserved') }}</button>
                                    @else
                                        <a href="{{ route('product.checkout', $product) }}"
                                            class="block w-full py-2.5 text-center text-white font-medium rounded transition-colors text-sm bg-gray-900 hover:bg-gray-800">
                                            {{ __('Buy now') }}
                                        </a>
                                        <button type="button" @click="$dispatch('open-auth-modal')"
                                            class="w-full py-2.5 bg-white border border-gray-900 text-gray-900 font-medium rounded transition-colors text-sm">
                                            {{ __('Ask seller') }}
                                        </button>
                                    @endif
                                </div>
                            @endauth
                        </div>

                        <!-- Buyer Protection Fee Box -->
                        <div class="mt-6 flex gap-3 p-4 border border-gray-200 rounded">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">{{ __('Buyer Protection fee') }}</h4>
                                <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                    Our <a href="#" class="hover:underline text-gray-900">Buyer Protection</a>
                                    est incluse dans chaque achat effectué via le bouton "Acheter maintenant".
                                    <a href="#" class="hover:underline text-gray-900">{{ __('Refund Policy') }}</a>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Seller Profile Card -->
                    <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <a href="{{ route('vendor.show', $product->vendor) }}" class="flex items-center gap-3 group">
                                <img src="{{ $product->vendor->avatar_url }}" alt="{{ $product->vendor->username }}"
                                    class="w-12 h-12 rounded-full border border-gray-100">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 group-hover:underline">
                                        {{ $product->vendor->username }}
                                    </h3>
                                    @php
                                        $vendorReviews = $product->vendor->receivedReviews()->whereNull('parent_id')->get();
                                        $vendorAvg = $vendorReviews->avg('rating') ?? 0;
                                        $vendorTotal = $vendorReviews->count();
                                        $vendorSales = \App\Models\Order::where('vendor_id', $product->vendor->id)->where('status', 'completed')->count();
                                    @endphp
                                    <div class="flex items-center gap-1 text-sm">
                                        @if($vendorTotal > 0)
                                            <span class="font-semibold text-gray-900 text-xs">{{ number_format($vendorAvg, 1) }}</span>
                                        @endif
                                        @for ($i = 0; $i < 5; $i++)
                                            <svg class="h-3.5 w-3.5 {{ $i < round($vendorAvg) ? 'text-amber-400' : 'text-gray-300' }}"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path d="m9.05 2.927 1.3 2.638c.18.365.527.619.93.678l2.91.423c1.014.147 1.419 1.394.685 2.11l-2.104 2.051c-.292.285-.425.695-.356 1.096l.497 2.897c.173 1.006-.883 1.772-1.787 1.298l-2.6-1.366a1.25 1.25 0 0 0-1.164 0l-2.6 1.366c-.904.474-1.96-.292-1.788-1.298l.498-2.897a1.25 1.25 0 0 0-.357-1.096L1.17 8.776c-.733-.716-.327-1.963.686-2.11l2.91-.423a1.25 1.25 0 0 0 .93-.678l1.3-2.638a1.25 1.25 0 0 1 2.254 0Z"/>
                                            </svg>
                                        @endfor
                                        <span class="text-gray-400 text-xs">({{ $vendorTotal }})</span>
                                    </div>
                                </div>
                            </a>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                        @php
                            $vendorCity    = $product->vendor->getMeta('city');
                            $vendorCountry = $product->vendor->getMeta('country');
                            $vendorLocation = collect([$vendorCity, $vendorCountry])->filter()->implode(', ');
                        @endphp

                        <div class="border-t border-gray-100 pt-4 mb-4 space-y-2 text-sm">
                            @if($vendorLocation)
                            <div class="flex items-center gap-2 text-gray-600">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ $vendorLocation }}</span>
                            </div>
                            @endif
                            <div class="flex items-center gap-2 text-gray-600">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Vu {{ $product->vendor->last_seen_at ? $product->vendor->last_seen_at->diffForHumans() : 'il y a longtemps' }}</span>
                            </div>
                            @if($vendorSales > 0)
                                <div class="flex items-center gap-2 text-gray-600">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <span>{{ $vendorSales }} vente{{ $vendorSales > 1 ? 's' : '' }}</span>
                                </div>
                            @endif
                            @php
                                $lastSeen = $product->vendor->last_seen_at;
                                $responseLabel = null;
                                if ($lastSeen) {
                                    $minutesAgo = $lastSeen->diffInMinutes();
                                    if ($minutesAgo <= 60) $responseLabel = 'Répond rapidement';
                                    elseif ($minutesAgo <= 1440) $responseLabel = 'Répond généralement le jour même';
                                }
                            @endphp
                            @if($responseLabel)
                                <div class="flex items-center gap-2 text-green-600">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>{{ $responseLabel }}</span>
                                </div>
                            @endif
                        </div>

                        @php
                            $isFollowing = auth()->check() ? auth()->user()->isFollowing($product->vendor) : false;
                        @endphp
                        @if(auth()->check() && auth()->id() !== $product->vendor->id)
                            <button type="button"
                                class="follow-btn w-full py-2 border font-medium rounded hover:opacity-80 transition-colors text-sm"
                                style="{{ $isFollowing ? 'border-color: #d1d5db; color: #374151; background-color: #f9fafb;' : 'border-color: #111827; color: #111827;' }}"
                                data-user-id="{{ $product->vendor->id }}">
                                {{ $isFollowing ? 'Abonné(e)' : 'Suivre' }}
                            </button>
                        @elseif(!auth()->check())
                            <button type="button" @click="$dispatch('open-auth-modal')"
                                class="w-full py-2 border font-medium rounded hover:opacity-80 transition-colors text-sm"
                                style="border-color: #111827; color: #111827">
                                {{ __('Follow') }}
                            </button>
                        @endif
                    </div>
                </aside>
            </div>


            @php
                $bpPercent = (float) config('settings.buyer_protection_fee_percentage', 5);
                $bpFixed   = (float) config('settings.buyer_protection_fee_fixed', 0.70);
            @endphp

            <section class="mt-4 md:mt-8 px-4 md:px-0 pb-6">
                <div class="flex justify-between items-center mb-4 pt-4 md:pt-0">
                    <h2 class="text-lg font-bold text-vinted-gray-900">{{ __("Member's items") }}</h2>
                    <a href="{{ route('search', ['vendor_id' => $product->vendor->id]) }}"
                        class="text-sm font-semibold hover:underline text-gray-900">{{ __('See all') }}</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @forelse ($product->vendor->products->take(6) as $item)
                        @php
                            $itemBpFee = ($item->price * $bpPercent / 100) + $bpFixed;
                            $itemInclTotal = $item->price + $itemBpFee;
                        @endphp
                        <div class="grid-item relative">
                            <div class="used-image-wrapper">
                                <a href="{{ route('products.show', $item) }}" class="absolute inset-0 z-10 cursor-pointer block"></a>
                                <img src="{{ $item->getFeaturedImageUrl('preview') }}" class="used-image-content" alt="{{ $item->name }}">
                                @if($item->status === 'sold')
                                    <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20" style="background-color:#4fb286">{{ __('Sold') }}</div>
                                @elseif($item->status === 'reserved')
                                    <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20" style="background-color:#f59e0b">{{ __('Reserved') }}</div>
                                @endif
                                @if(auth()->id() !== $item->vendor_id)
                                    <button class="fav-badge z-30" aria-label="Favourite"
                                        data-id="{{ $item->id }}"
                                        data-url="{{ route('products.favorite', $item) }}">
                                        <svg viewBox="0 0 24 24" class="{{ $item->isFavorited() ? '!text-red-500 !fill-current !stroke-current' : '' }}">
                                            <path d="M12 21s-7.2-4.2-9.3-8.4C1.3 10.1 2.1 6.9 4.8 5.7c1.8-.8 3.9-.3 5.2 1.1L12 8.8l2-2c1.3-1.4 3.4-1.9 5.2-1.1 2.7 1.2 3.5 4.4 2.1 6.9C19.2 16.8 12 21 12 21z"/>
                                        </svg>
                                        <span>{{ $item->favoritedBy()->count() }}</span>
                                    </button>
                                @endif
                            </div>
                            <a href="{{ route('products.show', $item) }}" class="block cursor-pointer">
                                <div class="pt-1.5">
                                    <p class="brand-line">{{ $item->name }}</p>
                                    <p class="meta-line">{{ $item->getOptionsSummaryAttribute() }}</p>
                                    <p class="price-line">{{ $item->price }} MAD</p>
                                    <div class="incl-line">
                                        <span>{{ number_format($itemInclTotal, 2) }} MAD incl.</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                            <svg class="w-80 h-80 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-400">{{ __('No other items from this seller yet') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="mt-4 md:mt-8 px-4 md:px-0 pb-6">
                <div class="flex justify-between items-center mb-4 pt-4 md:pt-0">
                    <h2 class="text-lg font-bold text-vinted-gray-900">{{ __('Similar products') }}</h2>
                    <a href="{{ route('search', ['categories' => [$product->category_id]]) }}"
                        class="text-sm font-semibold hover:underline text-gray-900">{{ __('See all') }}</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @forelse ($similarProducts as $item)
                        @php
                            $itemBpFee = ($item->price * $bpPercent / 100) + $bpFixed;
                            $itemInclTotal = $item->price + $itemBpFee;
                        @endphp
                        <div class="grid-item relative">
                            <div class="used-image-wrapper">
                                <a href="{{ route('products.show', $item) }}" class="absolute inset-0 z-10 cursor-pointer block"></a>
                                <img src="{{ $item->getFeaturedImageUrl('preview') }}" class="used-image-content" alt="{{ $item->name }}">
                                @if(in_array($item->id, $trendingProductIds ?? []))
                                    <div class="absolute top-2 right-2 z-20 flex items-center gap-1 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg pointer-events-none"
                                         style="background-color:#111827;" title="{{ __('Trending') }}">
                                        <svg class="w-3 h-3 text-orange-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C9.5 6 10 9 8 11c-.8-2-2-3.5-2-3.5C4.5 10 3 13 3 15.5 3 19.6 7 23 12 23s9-3.4 9-7.5C21 10 15.5 5.5 12 2zm0 19c-3.3 0-6-2.2-6-5 0-1.5.8-3 2-4 .3 1 .9 2 1.8 2.5C10 12 11 9.5 11 7c2 2.5 2.5 5 1.5 7.5 1-.5 1.8-1.5 2-2.5 1.2 1 2 2.5 2 4C16.5 19.8 15.3 21 12 21z"/>
                                        </svg>
                                        <span class="tracking-wide uppercase">{{ __('Trending') }}</span>
                                    </div>
                                @endif
                                @if($item->status === 'sold')
                                    <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20" style="background-color:#4fb286">{{ __('Sold') }}</div>
                                @elseif($item->status === 'reserved')
                                    <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20" style="background-color:#f59e0b">{{ __('Reserved') }}</div>
                                @endif
                                @if(auth()->id() !== $item->vendor_id)
                                    <button class="fav-badge z-30" aria-label="Favourite"
                                        data-id="{{ $item->id }}"
                                        data-url="{{ route('products.favorite', $item) }}">
                                        <svg viewBox="0 0 24 24" class="{{ $item->isFavorited() ? '!text-red-500 !fill-current !stroke-current' : '' }}">
                                            <path d="M12 21s-7.2-4.2-9.3-8.4C1.3 10.1 2.1 6.9 4.8 5.7c1.8-.8 3.9-.3 5.2 1.1L12 8.8l2-2c1.3-1.4 3.4-1.9 5.2-1.1 2.7 1.2 3.5 4.4 2.1 6.9C19.2 16.8 12 21 12 21z"/>
                                        </svg>
                                        <span>{{ $item->favoritedBy()->count() }}</span>
                                    </button>
                                @endif
                            </div>
                            <a href="{{ route('products.show', $item) }}" class="block cursor-pointer">
                                <div class="pt-1.5">
                                    <p class="brand-line">{{ $item->name }}</p>
                                    <p class="meta-line">{{ $item->getOptionsSummaryAttribute() }}</p>
                                    <p class="price-line">{{ $item->price }} MAD</p>
                                    <div class="incl-line">
                                        <span>{{ number_format($itemInclTotal, 2) }} MAD incl.</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                            <svg class="w-80 h-80 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-400">{{ __('No similar products found') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- You Might Like --}}
            @if($youMightLike->isNotEmpty())
            <section class="mt-4 md:mt-8 px-4 md:px-0 pb-6">
                <div class="flex justify-between items-center mb-4 pt-4 md:pt-0">
                    <h2 class="text-lg font-bold text-vinted-gray-900">{{ __('You Might Like') }}</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($youMightLike as $item)
                        @php
                            $ymlBpFee     = ($item->price * $bpPercent / 100) + $bpFixed;
                            $ymlInclTotal = $item->price + $ymlBpFee;
                        @endphp
                        <div class="grid-item relative">
                            <div class="used-image-wrapper">
                                <a href="{{ route('products.show', $item) }}" class="absolute inset-0 z-10 cursor-pointer block"></a>
                                <img src="{{ $item->getFeaturedImageUrl('preview') }}" class="used-image-content" alt="{{ $item->name }}">
                                @if(in_array($item->id, $trendingProductIds ?? []))
                                    <div class="absolute top-2 right-2 z-20 flex items-center gap-1 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg pointer-events-none"
                                         style="background-color:#111827;" title="{{ __('Trending') }}">
                                        <svg class="w-3 h-3 text-orange-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C9.5 6 10 9 8 11c-.8-2-2-3.5-2-3.5C4.5 10 3 13 3 15.5 3 19.6 7 23 12 23s9-3.4 9-7.5C21 10 15.5 5.5 12 2zm0 19c-3.3 0-6-2.2-6-5 0-1.5.8-3 2-4 .3 1 .9 2 1.8 2.5C10 12 11 9.5 11 7c2 2.5 2.5 5 1.5 7.5 1-.5 1.8-1.5 2-2.5 1.2 1 2 2.5 2 4C16.5 19.8 15.3 21 12 21z"/>
                                        </svg>
                                        <span class="tracking-wide uppercase">{{ __('Trending') }}</span>
                                    </div>
                                @endif
                                @if($item->status === 'sold')
                                    <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20" style="background-color:#4fb286">{{ __('Sold') }}</div>
                                @elseif($item->status === 'reserved')
                                    <div class="absolute bottom-0 left-0 right-0 text-white text-[11px] font-bold px-3 py-1.5 z-20" style="background-color:#f59e0b">{{ __('Reserved') }}</div>
                                @endif
                                @if(auth()->id() !== $item->vendor_id)
                                    <button class="fav-badge z-30" aria-label="Favourite" data-id="{{ $item->id }}"
                                        data-url="{{ route('products.favorite', $item) }}">
                                        <svg viewBox="0 0 24 24" class="{{ $item->isFavorited() ? '!text-red-500 !fill-current !stroke-current' : '' }}">
                                            <path d="M12 21s-7.2-4.2-9.3-8.4C1.3 10.1 2.1 6.9 4.8 5.7c1.8-.8 3.9-.3 5.2 1.1L12 8.8l2-2c1.3-1.4 3.4-1.9 5.2-1.1 2.7 1.2 3.5 4.4 2.1 6.9C19.2 16.8 12 21 12 21z"/>
                                        </svg>
                                        <span>{{ $item->favoritedBy()->count() }}</span>
                                    </button>
                                @endif
                            </div>
                            <a href="{{ route('products.show', $item) }}" class="block cursor-pointer">
                                <div class="pt-1.5">
                                    <p class="brand-line">{{ $item->name }}</p>
                                    <p class="meta-line">{{ $item->getOptionsSummaryAttribute() }}</p>
                                    <p class="price-line">{{ $item->price }} MAD</p>
                                    <div class="incl-line">
                                        <span>{{ number_format($ymlInclTotal, 2) }} MAD incl.</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif
        </div>
        @include('frontend.products._image_modal')

        {{-- Reserve Confirmation Modal --}}
        <div x-data="{ showReserveModal: false }" @open-reserve-modal.window="showReserveModal = true"
            x-show="showReserveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black opacity-50" @click="showReserveModal = false"></div>

            <div class="relative z-10 bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Reserve this item') }}</h3>
                <p class="text-sm text-gray-600 mb-4">
                    {{ __("You can reserve this item for a specific buyer. They will still be able to buy it, but others won't.") }}
                </p>

                <form action="{{ route('items.reserve', $product) }}" method="POST" x-data="{
                                        search: '',
                                        results: [],
                                        selectedUser: null,
                                        isOpen: false,
                                        isSearching: false,

                                        fetchUsers() {
                                            if (this.search.length < 2) {
                                                this.results = [];
                                                this.isOpen = false;
                                                return;
                                            }
                                            this.isSearching = true;
                                            fetch(`/search/suggestions?type=user&query=${this.search}`)
                                                .then(res => res.json())
                                                .then(data => {
                                                    this.results = data;
                                                    this.isOpen = true;
                                                })
                                                .finally(() => {
                                                    this.isSearching = false;
                                                });
                                        },

                                        selectUser(user) {
                                            this.selectedUser = user;
                                            this.search = user.label; // Display name
                                            this.isOpen = false;
                                        },

                                        clearSelection() {
                                            this.selectedUser = null;
                                            this.search = '';
                                        }
                                    }">
                    @csrf
                    <div class="mb-4 relative">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Réserver pour (nom d'utilisateur ou
                            e-mail)</label>

                        <!-- Hidden Input for Form Submission -->
                        <input type="hidden" name="username" :value="search">

                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" x-model="search" @input.debounce.300ms="fetchUsers()" @click="fetchUsers()"
                                @click.outside="isOpen = false" placeholder="{{ __('Type to search users...') }}"
                                class="w-full border border-gray-300 rounded-md p-2 focus:ring-gray-500 focus:border-gray-500 text-sm"
                                autocomplete="off">

                            <!-- Spinner -->
                            <div x-show="isSearching" class="absolute right-3 top-2.5 text-gray-400">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <!-- Dropdown Results -->
                        <div x-show="isOpen && results.length > 0"
                            class="absolute z-50 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                            <template x-for="user in results" :key="user.sub">
                                <div @click="selectUser(user)"
                                    class="px-4 py-2 hover:bg-gray-50 cursor-pointer flex items-center gap-3 border-b border-gray-50 last:border-0">
                                    <img :src="user.image" class="w-8 h-8 rounded-full bg-gray-200 object-cover">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="user.label"></p>
                                        <p class="text-xs text-gray-500" x-text="user.sub"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <p class="text-xs text-gray-500 mt-1">{{ __('Leave empty to reserve generally.') }}</p>
                    </div>

                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="showReserveModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-white rounded-md transition-colors bg-gray-900 hover:bg-gray-800">
                            Reserve
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bump Feature Modal --}}
        <div x-data="{ showBumpModal: false }" @open-bump-modal.window="showBumpModal = true" x-show="showBumpModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black opacity-50" @click="showBumpModal = false"></div>

            <div class="relative z-10 bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 text-center">
                <div class="mb-4">
                    <span class="inline-block p-3 rounded-full bg-teal-100 text-gray-700">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('Boost Feature Coming Soon!') }}</h3>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    Nous travaillons sur une nouvelle façon de vous aider à vendre plus vite. La fonctionnalité <strong>Bump</strong> vous permettra
                    d'augmenter la visibilité de votre article pour toucher plus d'acheteurs. Restez connecté(e) !
                </p>
                <button @click="showBumpModal = false"
                    class="w-full px-4 py-2 text-white font-bold rounded-md transition-colors bg-gray-900 hover:bg-gray-800">
                    {{ __('Got it!') }}
                </button>
            </div>
        </div>

        {{-- Delete Confirmation Modal --}}
        <div x-data="{ showDeleteModal: false }" @open-delete-modal.window="showDeleteModal = true" x-show="showDeleteModal"
            x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black opacity-50" @click="showDeleteModal = false"></div>

            <div class="relative z-10 bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Delete Product?') }}</h3>
                <p class="text-sm text-gray-600 mb-6">
                    {{ __('Are you sure you want to delete this product? This action cannot be undone.') }}
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <form action="{{ route('items.destroy', $product) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Hide Confirmation Modal --}}
        <div x-data="{ showHideModal: false }" @open-hide-modal.window="showHideModal = true" x-show="showHideModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black opacity-50" @click="showHideModal = false"></div>

            <div class="relative z-10 bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Hide Product?') }}</h3>
                <p class="text-sm text-gray-600 mb-6">
                    {{ __('Are you sure you want to hide this product? It will no longer be visible to other users.') }}
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showHideModal = false"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <form action="{{ route('items.hide', $product) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                            Hide
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Unhide Confirmation Modal --}}
        <div x-data="{ showUnhideModal: false }" @open-unhide-modal.window="showUnhideModal = true" x-show="showUnhideModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black opacity-50" @click="showUnhideModal = false"></div>
            <div class="relative z-10 bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Unhide Product?') }}</h3>
                <p class="text-sm text-gray-600 mb-6">
                    {{ __('Your listing will be made visible to buyers again.') }}
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showUnhideModal = false"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <form action="{{ route('items.unhide', $product) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 text-white rounded-md transition-colors bg-gray-900 hover:bg-gray-800">
                            Unhide
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if(!auth()->check() || auth()->id() !== $product->vendor_id)
            {{-- Floating Bottom Action Bar for Mobile --}}
            <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] flex gap-3 md:hidden z-50">
                @auth
                    @if($product->status === 'reserved' && $product->buyer_id !== auth()->id())
                        <button disabled class="flex-1 py-2.5 bg-gray-200 text-gray-400 font-bold rounded text-sm cursor-not-allowed">{{ __('Reserved') }}</button>
                    @else
                        <button type="button" @click="$dispatch('open-make-offer-modal', { productId: {{ $product->id }} })" class="flex-1 py-2.5 bg-white border border-gray-900 text-gray-900 font-bold rounded transition-colors text-sm text-center">{{ __('Make an offer') }}</button>
                        <a href="{{ route('product.checkout', $product) }}" class="flex-1 py-2.5 flex items-center justify-center text-white font-bold rounded transition-colors text-base bg-gray-900 hover:bg-gray-800">{{ __('Buy now') }}</a>
                    @endif
                @else
                    @if($product->status === 'reserved')
                        <button disabled class="flex-1 py-2.5 bg-gray-200 text-gray-400 font-bold rounded text-sm cursor-not-allowed">{{ __('Reserved') }}</button>
                    @else
                        <button type="button" @click="$dispatch('open-auth-modal')" class="flex-1 py-2.5 bg-white border border-gray-900 text-gray-900 font-bold rounded transition-colors text-sm text-center">{{ __('Make an offer') }}</button>
                        <a href="{{ route('product.checkout', $product) }}" class="flex-1 py-2.5 flex items-center justify-center text-white font-bold rounded transition-colors text-base bg-gray-900 hover:bg-gray-800">{{ __('Buy now') }}</a>
                    @endif
                @endauth
            </div>
        @endif

    {{-- Make-offer modal must live outside the md:hidden wrapper so it is present on all screen sizes --}}
    @if(!auth()->check() || auth()->id() !== $product->vendor_id)
        @livewire('chat::make-offer-modal')
    @endif
    </main>
@endsection

@section('after_body')
    <script>
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.like-btn');
            const followBtn = e.target.closest('.follow-btn');

            if (followBtn) {
                e.preventDefault();
                e.stopPropagation();

                const userId = followBtn.dataset.userId;
                const token = document.querySelector('meta[name="csrf-token"]').content;

                followBtn.disabled = true;
                followBtn.classList.add('opacity-70');

                try {
                    const res = await fetch(`/users/${userId}/follow`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        }
                    });

                    if (res.status === 401) {
                        Livewire.dispatch('open-login-popup');
                        return;
                    }

                    const data = await res.json(); // { following: bool, followers_count: int }

                    if (data.following) {
                        followBtn.textContent = 'Abonné(e)';
                        followBtn.classList.remove('border-vinted-teal', 'text-vinted-teal');
                        followBtn.classList.add('border-gray-300', 'text-gray-700', 'bg-gray-50');
                    } else {
                        followBtn.textContent = 'Suivre';
                        followBtn.classList.remove('border-gray-300', 'text-gray-700', 'bg-gray-50');
                        followBtn.classList.add('border-vinted-teal', 'text-vinted-teal');
                    }

                } catch (err) {
                    console.error(err);
                } finally {
                    followBtn.disabled = false;
                    followBtn.classList.remove('opacity-70');
                }
                return;
            }

            if (!btn) return;

            // Prevent default button action and stop propagation
            e.preventDefault();
            e.stopPropagation();

            const productId = btn.dataset.productId;
            const countEl = btn.querySelector('.like-count');
            const token = document.querySelector('meta[name="csrf-token"]').content;

            btn.disabled = true;
            btn.classList.add('opacity-70');

            try {
                const res = await fetch(`/products/${productId}/favorite`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                });

                if (res.status === 401) {
                    Livewire.dispatch('open-login-popup');
                    return;
                }

                const data = await res.json(); // { liked: bool, count: int }

                // heart color + aria
                btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
                btn.classList.toggle('text-red-500', data.liked);
                btn.classList.toggle('text-gray-400', !data.liked);

                // count show/hide
                if (countEl) {
                    if (data.count > 0) {
                        countEl.textContent = data.count;
                        countEl.classList.remove('hidden');
                    } else {
                        countEl.textContent = '';
                        countEl.classList.add('hidden');
                    }
                }
            } catch (err) {
                console.error(err);
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-70');
            }
        });
    </script>
@endsection