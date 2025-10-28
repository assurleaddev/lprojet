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
            <nav class="text-xs text-vinted-gray-500 mb-4 space-x-1.5">
                <a href="#" class="hover:underline">Femmes</a><span>›</span>
                <a href="#" class="hover:underline">Vêtements</a><span>›</span>
                <a href="#" class="hover:underline">Jeans</a><span>›</span>
                <a href="#" class="hover:underline">Jeans évasés</a><span>›</span>
                <a href="#" class="text-vinted-blue-link font-semibold hover:underline">Stradivarius, Jeans évasés</a>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-8 relative ">
                    <div class="grid grid-cols-2 gap-1.5 ">
                        <img src="{{ $product->getFeaturedImageUrl('preview') }}"
                            alt="Main product image of brown flare jeans" class="w-full h-[800px] object-cover col">
                        <div class="h-[800px] grid grid-rows-2 gap-1.5 grid-cols-2">
                            @forelse ($product->media as $media)
                                @if ($loop->index < 4)
                                    <img src="{{ $media->getUrl() }}" alt="Close up of jeans fabric"
                                        class="w-full h-full  object-cover grid-span-1">
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
                </div>



                <aside class="lg:col-span-4">
                    <div class="p-6 bg-white">
                        <p class="text-xl font-bold mb-4 text-vinted-gray-900">{{ $product->name }} MAD</p>

                        <div class="space-y-3 text-sm border-b border-vinted-gray-200 pb-4 mb-4">
                            <div class="flex"><span class="w-1/3 text-vinted-gray-500">TAILLE</span> <span
                                    class="text-vinted-gray-700">{{ $product->size }}</span></div>
                            <div class="flex"><span class="w-1/3 text-vinted-gray-500">ÉTAT</span> <span
                                    class="text-vinted-gray-900 font-semibold">{{ $product->condition }}</span></div>
                            <div class="flex"><span class="w-1/3 text-vinted-gray-500">COULEUR</span> <span
                                    class="text-vinted-gray-700">{{ $product->color }}</span></div>
                            <div class="flex"><span class="w-1/3 text-vinted-gray-500">Brand</span> <span
                                    class="text-vinted-gray-700">{{ $product->brand }}</span></div>
                        </div>

                        <div class="mb-5">
                            <h3 class="font-bold text-vinted-gray-900 mb-1.5">{{ $product->name }}</h3>
                            <p class="text-sm text-vinted-gray-700 mb-3">{!! $product->description !!}</p>
                            <div class="flex flex-wrap gap-x-3 gap-y-1">
                                <a href="#" class="text-sm text-vinted-blue-link hover:underline">#jeans</a>
                                <a href="#" class="text-sm text-vinted-blue-link hover:underline">#stradivarius</a>
                                <a href="#" class="text-sm text-vinted-blue-link hover:underline">#pattedelephant</a>
                                <a href="#" class="text-sm text-vinted-blue-link hover:underline">#marron</a>
                            </div>
                        </div>


                        <div class="flex items-center justify-between border-t border-b border-vinted-gray-200 py-4 mb-5">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $product->vendor->avatar_url }}" alt="Seller avatar"
                                    class="w-12 h-12 rounded-full">
                                <a href="{{ route('vendor.show', $product->vendor) }}">
                                    <p class="font-semibold text-sm">{{ $product->vendor->username }}</p>
                                    <div class="flex items-center text-xs text-vinted-gray-400">
                                        <div class="flex items-center">
                                            <svg class="w-3.5 h-3.5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                <path
                                                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                            </svg>
                                            <svg class="w-3.5 h-3.5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                <path
                                                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                            </svg>
                                            <svg class="w-3.5 h-3.5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                <path
                                                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                            </svg>
                                            <svg class="w-3.5 h-3.5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                <path
                                                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                            </svg>
                                            <svg class="w-3.5 h-3.5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                <path
                                                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                            </svg>
                                            <span
                                                class="ml-1 font-bold text-vinted-blue-link">{{ $product->vendor->followers_count !== 0 && $product->vendor->followers_count !== '' ? $product->vendor->followers_count : '' }}</span>
                                        </div>
                                    </div>
                                    <p class="text-2xs text-vinted-gray-400 mt-1">Vu la dernière fois : il y a 7 heures</p>
                                </a>
                            </div>
                        </div>

                        <div class="space-y-2.5">
                            <a href="{{ route('product.checkout', $product) }}"
                                class="w-full block text-center bg-vinted-teal text-white font-bold py-2.5 rounded-md text-sm hover:bg-vinted-teal-dark transition-colors">
                                Buy Now
                            </a>
                            @auth
                                @if(auth()->id() !== $product->vendor_id)
                                    @livewire('product-messaging-button', ['product' => $product])
                                
                                @else
                                    <button type="button" x-data @click="$dispatch('open-auth-modal')" class="text-blue-500 hover:underline">
                                        {{ __('Log in to message the seller') }}
                                    </button>
                                @endif
                            @endauth
                            <button
                                class="w-full border border-vinted-teal text-vinted-teal font-bold py-2 rounded-md hover:bg-vinted-teal/10 transition-colors text-sm">Ask
                                Seller</button>

                        </div>
                    </div>
                    <div class="bg-white p-4 flex items-start space-x-4 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10 text-vinted-teal flex-shrink-0 mt-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286Zm0 13.036h.008v.008h-.008v-.008Z" />
                        </svg>
                        <div>
                            <h4 class="font-bold text-vinted-gray-900">Protection acheteurs</h4>
                            <p class="text-sm text-vinted-gray-500">Bénéficie de la Protection acheteurs Vinted en payant
                                par le biais de Vinted. Cela inclut une politique de remboursement. <a href="#"
                                    class="text-vinted-blue-link font-semibold hover:underline">En savoir plus</a></p>
                        </div>
                    </div>
                </aside>
            </div>


            <section class="mt-8 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-vinted-gray-900">Member's items</h2>
                    <a href="#" class="text-sm font-semibold text-vinted-blue-link hover:underline">Voir tout</a>
                </div>
                <div class="relative w-2/3">
                    <div class="flex space-x-4 flex-wrap overflow-x-auto pb-4 custom-scrollbar">
                        @forelse ($product->vendor->products as $item)
                            <div class="flex-shrink-0 w-40">
                                <img src="{{ $item->getFeaturedImageUrl() }}" alt="Product"
                                    class="w-full h-56 object-cover mb-2">
                                <p class="font-bold text-sm">{{ $item->price }} MAD</p>
                                <p class="text-xs text-vinted-gray-500">
                                    {{ $item->options->groupBy('attribute_id')->map(fn($grp) => $grp->pluck('value')->implode(' / '))->implode(' · ') }}
                                </p>
                                <p class="text-xs text-vinted-gray-500">{{ $product->name }}</p>
                            </div>
                        @empty
                            no products yet
                        @endforelse

                    </div>
                </div>
            </section>
            <section class="mt-8 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-vinted-gray-900">Similar products</h2>
                    <a href="#" class="text-sm font-semibold text-vinted-blue-link hover:underline">Voir tout</a>
                </div>
                <div class="relative w-2/3">
                    <div class="flex space-x-4 flex-wrap overflow-x-auto pb-4 custom-scrollbar">
                        @forelse ($similarProducts as $item)
                            <div class="flex-shrink-0 w-40">
                                <img src="{{ $item->getFeaturedImageUrl() }}" alt="Product"
                                    class="w-full h-56 object-cover mb-2">
                                <p class="font-bold text-sm">{{ $item->price }} MAD</p>
                                <p class="text-xs text-vinted-gray-500">
                                    {{ $item->options->groupBy('attribute_id')->map(fn($grp) => $grp->pluck('value')->implode(' / '))->implode(' · ') }}
                                </p>
                                <p class="text-xs text-vinted-gray-500">{{ $item->name }}</p>
                            </div>
                        @empty
                            no products yet
                        @endforelse

                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection

@section('after_body')
    <script>
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.like-btn');
            if (!btn) return;

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
                    window.location.href = '/login';
                    return;
                }

                const data = await res.json(); // { liked: bool, count: int }

                // heart color + aria
                btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
                btn.classList.toggle('text-red-500', data.liked);
                btn.classList.toggle('text-gray-400', !data.liked);

                // count show/hide
                if (data.count > 0) {
                    countEl.textContent = data.count;
                    countEl.classList.remove('hidden');
                } else {
                    countEl.textContent = '';
                    countEl.classList.add('hidden');
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
