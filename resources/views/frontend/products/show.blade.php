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
    <main class="bg-vinted-gray-100">
        <div class="container mx-auto px-6 py-6">
            <nav class="text-xs text-vinted-gray-500 mb-4 space-x-1.5 flex flex-wrap items-center">
                @foreach($breadcrumbs as $category)
                    <a href="{{ route('search', ['categories' => [$category->id]]) }}"
                        class="hover:underline">{{ $category->name }}</a>
                    <span>›</span>
                @endforeach
                <a href="#" class="font-semibold hover:underline" style="color: var(--brand)">
                    {{ $product->brand ? $product->brand->name . ', ' : '' }}{{ $product->name }}
                </a>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-8 relative ">
                    <div class="grid grid-cols-2 gap-1.5 ">
                        <img src="{{ $product->getFeaturedImageUrl() }}" alt="Main product image of brown flare jeans"
                            class="w-full h-[800px] object-cover col cursor-pointer product-gallery-image">
                        <div class="h-[800px] grid grid-rows-2 gap-1.5 grid-cols-2">
                            @forelse ($product->getMedia('products') as $media)
                                @if ($loop->index < 4)
                                    <img src="{{ $media->getUrl() }}" alt="Close up of jeans fabric"
                                        class="w-full h-[397px] object-cover cursor-pointer product-gallery-image">
                                @endif
                            @empty
                                no other images
                            @endforelse

                        </div>

                    </div>
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
                        <h1 class="text-xl font-medium text-gray-900 leading-snug mb-1">{{ $product->name }}</h1>

                        <!-- Subtitle (Size - Condition - Brand) -->
                        <div class="text-sm text-gray-500 mb-4 flex items-center gap-1">
                            @if($product->size) <span>{{ $product->size }}</span> <span class="text-gray-300">•</span>
                            @endif
                            @if($product->condition) <span>{{ ucwords(str_replace('_', ' ', $product->condition)) }}</span>
                            <span class="text-gray-300">•</span> @endif
                            @if($product->brand) <a href="#" class="hover:underline"
                            style="color: var(--brand)">{{ $product->brand->name }}</a> @endif
                        </div>

                        <!-- Price Block -->
                        <div class="mb-4">
                            <!-- Item Price (Gray) -->
                            <div class="text-gray-500 text-lg">{{ number_format($product->price, 2) }} MAD</div>
                            <!-- Total Price (Teal, Bold) - Approximated Total -->
                            @php
                                $buyerProtectionFee = 0.70 + ($product->price * 0.05);
                                $totalPrice = $product->price + $buyerProtectionFee;
                            @endphp
                            <div class="text-xl font-bold" style="color: var(--brand)">{{ number_format($totalPrice, 2) }}
                                MAD</div>

                            <!-- Buyer Protection Label -->
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="text-sm" style="color: var(--brand)">Includes Buyer Protection</span>
                                <svg class="w-4 h-4" style="color: var(--brand)" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            @if(auth()->check() && auth()->user()->wallet)
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ number_format(auth()->user()->wallet->balance, 2) }} MAD in your Balance to use for
                                    purchases
                                </div>
                            @endif

                            @if($product->status === 'reserved' && $product->buyer)
                                <div
                                    class="mt-3 p-2 bg-gray-100 border border-gray-300 rounded text-sm text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span>Reserved for <strong>{{ $product->buyer->username }}</strong></span>
                                </div>
                            @endif
                        </div>

                        <hr class="border-gray-100 mb-5">

                        <!-- Attributes Table -->
                        <div class="space-y-3 text-sm mb-6">
                            @if($product->brand)
                                <div class="grid grid-cols-2">
                                    <span class="text-gray-500">Brand</span>
                                    <a href="#" class="hover:underline"
                                        style="color: var(--brand)">{{ $product->brand->name }}</a>
                                </div>
                            @endif
                            @if($product->size)
                                <div class="grid grid-cols-2">
                                    <span class="text-gray-500">Size</span>
                                    <span class="text-gray-700 uppercase">{{ $product->size }}</span>
                                </div>
                            @endif
                            @if($product->condition)
                                <div class="grid grid-cols-2">
                                    <span class="text-gray-500">Condition</span>
                                    <span class="text-gray-700">{{ ucwords(str_replace('_', ' ', $product->condition)) }}</span>
                                </div>
                            @endif
                            @if($product->color)
                                <div class="grid grid-cols-2">
                                    <span class="text-gray-500">Colour</span>
                                    <span class="text-gray-700">{{ $product->color }}</span>
                                </div>
                            @endif
                            <div class="grid grid-cols-2">
                                <span class="text-gray-500">Uploaded</span>
                                <span class="text-gray-700">{{ $product->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <hr class="border-gray-100 mb-5">

                        <!-- Description -->
                        <div class="mb-5">
                            <div class="text-sm text-gray-700 leading-relaxed mb-2 prose prose-sm max-w-none">
                                {!! $product->description !!}
                            </div>
                            <button class="text-sm hover:underline flex items-center gap-1" style="color: var(--brand)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                                Translate this
                            </button>
                        </div>

                        <hr class="border-gray-100 mb-5">

                        <!-- Shipping Estimates -->
                        <div class="flex justify-between items-center text-sm mb-6">
                            <span class="text-gray-500">Shipping</span>
                            <span class="text-gray-700">from 25.00 MAD</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            @auth
                                @if(auth()->id() === $product->vendor_id)
                                    {{-- Owner Actions --}}
                                    <div class="p-4 bg-gray-50 border border-gray-100 rounded-md text-center">
                                        <p class="text-sm text-gray-600 mb-3">This is your item</p>
                                        <div class="space-y-2">
                                            <!-- Bump Button -->
                                            <button type="button" @click="$dispatch('open-bump-modal')"
                                                class="w-full py-2 text-white rounded text-sm font-bold border border-transparent"
                                                style="background-color: var(--brand)">
                                                Bump
                                            </button>

                                            <!-- Mark as Sold -->
                                            @if($product->status !== 'sold')
                                                <form action="{{ route('items.markAsSold', $product) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">
                                                        Mark as sold
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Mark as Reserved / Unreserve -->
                                            @if($product->status === 'reserved')
                                                <form action="{{ route('items.unreserve', $product) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">
                                                        Mark as unreserved
                                                    </button>
                                                </form>
                                            @elseif($product->status !== 'sold')
                                                <button type="button" @click="$dispatch('open-reserve-modal')"
                                                    class="w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">
                                                    Mark as reserved
                                                </button>
                                            @endif

                                            <!-- Hide / Unhide -->
                                            @if($product->status === 'hidden')
                                                {{-- Logic to unhide if needed, usually just reuse logic or separate route --}}
                                                {{-- Assuming 'hide' toggles or just hides. For now, strictly 'Hide' per request unless
                                                hidden --}}
                                                <div
                                                    class="w-full py-2 bg-gray-200 text-gray-500 border border-gray-300 rounded text-sm font-medium text-center">
                                                    Item is hidden
                                                </div>
                                            @else
                                                <button type="button" @click="$dispatch('open-hide-modal')"
                                                    class="w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">
                                                    Hide
                                                </button>
                                            @endif

                                            <!-- Edit Button -->
                                            <a href="{{ route('items.edit', $product) }}"
                                                class="block w-full py-2 bg-white text-vinted-gray-900 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50 text-center">
                                                Edit listing
                                            </a>

                                            <!-- Delete Button -->
                                            <button type="button" @click="$dispatch('open-delete-modal')"
                                                class="w-full py-2 bg-white text-red-600 border border-red-200 rounded text-sm font-bold hover:bg-red-50">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    {{-- Buyer Actions --}}
                                    @if($product->status === 'reserved' && $product->buyer_id !== auth()->id())
                                        <button disabled
                                            class="w-full py-2.5 bg-gray-200 text-gray-400 font-medium rounded text-sm cursor-not-allowed">Reserved</button>
                                    @else
                                        <a href="{{ route('product.checkout', $product) }}"
                                            class="block w-full py-2.5 text-center text-white font-medium rounded transition-colors text-sm"
                                            style="background-color: var(--brand)">
                                            Buy now
                                        </a>
                                        @livewire('product-messaging-button', ['product' => $product, 'class' => 'w-full py-2.5 bg-white border font-medium rounded transition-colors text-sm', 'style' => 'border-color: var(--brand); color: var(--brand)', 'text' => 'Ask seller'])
                                    @endif
                                @endif
                            @else
                                {{-- Guest Actions --}}
                                @if($product->status === 'reserved')
                                    <button disabled
                                        class="w-full py-2.5 bg-gray-200 text-gray-400 font-medium rounded text-sm cursor-not-allowed">Reserved</button>
                                @else
                                    <a href="{{ route('product.checkout', $product) }}"
                                        class="block w-full py-2.5 text-center text-white font-medium rounded transition-colors text-sm"
                                        style="background-color: var(--brand)">
                                        Buy now
                                    </a>
                                    <button type="button" @click="$dispatch('open-auth-modal')"
                                        class="w-full py-2.5 bg-white border font-medium rounded transition-colors text-sm"
                                        style="border-color: var(--brand); color: var(--brand)">
                                        Ask seller
                                    </button>
                                @endif
                            @endauth
                        </div>

                        <!-- Buyer Protection Fee Box -->
                        <div class="mt-6 flex gap-3 p-4 border border-gray-200 rounded">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6" style="color: var(--brand)" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Buyer Protection fee</h4>
                                <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                    Our <a href="#" class="hover:underline" style="color: var(--brand)">Buyer Protection</a>
                                    is added
                                    for a fee to every purchase made with the "Buy now" button.
                                    <a href="#" class="hover:underline" style="color: var(--brand)">Refund Policy</a>.
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
                                    <div class="flex items-center text-amber-400 text-sm">
                                        ★★★★★ <span
                                            class="text-gray-400 ml-1 text-xs">({{ $product->vendor->receivedReviews()->count() }})</span>
                                    </div>
                                </div>
                            </a>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                        <!-- Badges -->
                        <div class="mb-4 space-y-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-cyan-50 flex items-center justify-center text-vinted-teal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Frequent Uploads</p>
                                    <p class="text-xs text-gray-500">Regularly lists 5 or more items.</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 mb-4 space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ $product->vendor->city ?? 'Morocco' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Last seen {{ now()->subMinutes(rand(1, 59))->diffForHumans() }}</span>
                            </div>
                        </div>

                        @php
                            $isFollowing = auth()->check() ? auth()->user()->isFollowing($product->vendor) : false;
                        @endphp
                        @if(auth()->check() && auth()->id() !== $product->vendor->id)
                            <button type="button"
                                class="follow-btn w-full py-2 border font-medium rounded hover:opacity-80 transition-colors text-sm"
                                style="{{ $isFollowing ? 'border-color: #d1d5db; color: #374151; background-color: #f9fafb;' : 'border-color: var(--brand); color: var(--brand);' }}"
                                data-user-id="{{ $product->vendor->id }}">
                                {{ $isFollowing ? 'Following' : 'Follow' }}
                            </button>
                        @elseif(!auth()->check())
                            <button type="button" @click="$dispatch('open-auth-modal')"
                                class="w-full py-2 border font-medium rounded hover:opacity-80 transition-colors text-sm"
                                style="border-color: var(--brand); color: var(--brand)">
                                Follow
                            </button>
                        @endif
                    </div>
                </aside>
            </div>


            <section class="mt-8 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-vinted-gray-900">Member's items</h2>
                    <a href="{{ route('search', ['vendor_id' => $product->vendor->id]) }}"
                        class="text-sm font-semibold hover:underline" style="color: var(--brand)">Voir tout</a>
                </div>
                <div class="relative w-2/3">
                    <div class="flex space-x-4 flex-wrap overflow-x-auto pb-4 custom-scrollbar">
                        @forelse ($product->vendor->products as $item)
                            <div class="flex-shrink-0 w-40 block relative group">
                                <a href="{{ route('products.show', $item) }}"
                                    class="block hover:opacity-80 transition relative">
                                    <img src="{{ $item->getFeaturedImageUrl() }}" alt="Product"
                                        class="w-full h-56 object-cover mb-2 rounded-md">

                                    @php
                                        $isLiked = auth()->check() && $item->isFavorited();
                                        $count = $item->favoritedBy()->count();
                                    @endphp
                                    @if(auth()->id() !== $item->vendor_id)
                                        <button type="button"
                                            class="like-btn absolute bottom-2 right-2 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-sm transition-transform hover:scale-105 {{ $isLiked ? 'text-red-500' : 'text-gray-400' }}"
                                            data-product-id="{{ $item->id }}" aria-label="Add to favourites"
                                            style="height: 32px; width: 32px; bottom: 16px; right: 8px;"
                                            aria-pressed="{{ $isLiked ? 'true' : 'false' }}">
                                            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                                                <path
                                                    d="M12 21s-7.5-4.46-9.5-8.32C1 8.86 3.42 6 6.5 6c1.74 0 3.41.81 4.5 2.09C12.09 6.81 13.76 6 15.5 6 18.58 6 21 8.86 21.5 12.68 19.5 16.54 12 21 12 21z"
                                                    fill="currentColor" />
                                            </svg>
                                            {{-- Optional: hide count on small cards or show if needed --}}
                                        </button>
                                    @endif
                                </a>
                                <a href="{{ route('products.show', $item) }}" class="block">
                                    <p class="font-bold text-sm">{{ $item->price }} MAD</p>
                                    <p class="text-xs text-vinted-gray-500 truncate">
                                        {{ $item->options->groupBy('attribute_id')->map(fn($grp) => $grp->pluck('value')->implode(' / '))->implode(' · ') }}
                                    </p>
                                    <p class="text-xs text-vinted-gray-500 truncate">{{ $product->name }}</p>
                                </a>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No products yet</p>
                        @endforelse
                    </div>
                </div>
            </section>
            <section class="mt-8 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-vinted-gray-900">Similar products</h2>
                    <a href="{{ route('search', ['categories' => [$product->category_id]]) }}"
                        class="text-sm font-semibold hover:underline" style="color: var(--brand)">Voir tout</a>
                </div>
                <div class="relative w-2/3">
                    <div class="flex space-x-4 flex-wrap overflow-x-auto pb-4 custom-scrollbar">
                        @forelse ($similarProducts as $item)
                            <div class="flex-shrink-0 w-40 block relative group">
                                <a href="{{ route('products.show', $item) }}"
                                    class="block hover:opacity-80 transition relative">
                                    <img src="{{ $item->getFeaturedImageUrl() }}" alt="Product"
                                        class="w-full h-56 object-cover mb-2 rounded-md">

                                    @php
                                        $isLiked = auth()->check() && $item->isFavorited();
                                        $count = $item->favoritedBy()->count();
                                    @endphp
                                    @if(auth()->id() !== $item->vendor_id)
                                        <button type="button"
                                            class="like-btn absolute bottom-2 right-2 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-sm transition-transform hover:scale-105 {{ $isLiked ? 'text-red-500' : 'text-gray-400' }}"
                                            data-product-id="{{ $item->id }}" aria-label="Add to favourites"
                                            style="height: 32px; width: 32px; bottom: 16px; right: 8px;"
                                            aria-pressed="{{ $isLiked ? 'true' : 'false' }}">
                                            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                                                <path
                                                    d="M12 21s-7.5-4.46-9.5-8.32C1 8.86 3.42 6 6.5 6c1.74 0 3.41.81 4.5 2.09C12.09 6.81 13.76 6 15.5 6 18.58 6 21 8.86 21.5 12.68 19.5 16.54 12 21 12 21z"
                                                    fill="currentColor" />
                                            </svg>
                                            {{-- Optional: hide count on small cards or show if needed --}}
                                        </button>
                                    @endif
                                </a>
                                <a href="{{ route('products.show', $item) }}" class="block">
                                    <p class="font-bold text-sm">{{ $item->price }} MAD</p>
                                    <p class="text-xs text-vinted-gray-500 truncate">
                                        {{ $item->options->groupBy('attribute_id')->map(fn($grp) => $grp->pluck('value')->implode(' / '))->implode(' · ') }}
                                    </p>
                                    <p class="text-xs text-vinted-gray-500 truncate">{{ $item->name }}</p>
                                </a>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No similar products</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
        @include('frontend.products._image_modal')

        {{-- Reserve Confirmation Modal --}}
        <div x-data="{ showReserveModal: false }" @open-reserve-modal.window="showReserveModal = true"
            x-show="showReserveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black opacity-50" @click="showReserveModal = false"></div>

            <div class="relative z-10 bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Reserve this item</h3>
                <p class="text-sm text-gray-600 mb-4">
                    You can reserve this item for a specific buyer. They will still be able to buy it, but others won't.
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
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Reserve for (username or
                            email)</label>

                        <!-- Hidden Input for Form Submission -->
                        <input type="hidden" name="username" :value="search">

                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" x-model="search" @input.debounce.300ms="fetchUsers()" @click="fetchUsers()"
                                @click.outside="isOpen = false" placeholder="Type to search users..."
                                class="w-full border border-gray-300 rounded-md p-2 focus:ring-teal-500 focus:border-teal-500 text-sm"
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

                        <p class="text-xs text-gray-500 mt-1">Leave empty to reserve generally.</p>
                    </div>

                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="showReserveModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-white rounded-md transition-colors"
                            style="background-color: var(--brand)">
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
                    <span class="inline-block p-3 rounded-full bg-teal-100 text-teal-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Boost Feature Coming Soon!</h3>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    We're working on a new way to help you sell faster. The <strong>Bump</strong> feature will allow you to
                    boost your product's visibility to reach more buyers. Stay tuned!
                </p>
                <button @click="showBumpModal = false"
                    class="w-full px-4 py-2 text-white font-bold rounded-md transition-colors"
                    style="background-color: var(--brand)">
                    Got it!
                </button>
            </div>
        </div>

        {{-- Delete Confirmation Modal --}}
        <div x-data="{ showDeleteModal: false }" @open-delete-modal.window="showDeleteModal = true" x-show="showDeleteModal"
            x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black opacity-50" @click="showDeleteModal = false"></div>

            <div class="relative z-10 bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Product?</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to delete this product? This action cannot be undone.
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
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hide Product?</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to hide this product? It will no longer be visible to other users.
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
                        followBtn.textContent = 'Following';
                        followBtn.classList.remove('border-vinted-teal', 'text-vinted-teal');
                        followBtn.classList.add('border-gray-300', 'text-gray-700', 'bg-gray-50');
                    } else {
                        followBtn.textContent = 'Follow';
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