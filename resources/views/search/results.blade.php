@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-6 md:px-12 py-6">
        @if($type === 'product')
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">
                    @if(!empty($categoryIds) && $firstCat = \App\Models\Category::find($categoryIds[0]))
                        {{ $firstCat->name }}
                    @else
                        Items
                    @endif
                </h1>
                @auth
                    <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        Save search
                    </button>
                @endauth
            </div>

            {{-- Filter Pills --}}
            <div class="flex flex-wrap items-center gap-3 mb-4">
                {{-- Category Filter --}}
                <livewire:search.category-filter :categoryIds="$categoryIds" />

                {{-- Attribute Filters --}}
                @foreach($attributes as $attribute)
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50">
                            <span>{{ $attribute->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false"
                            class="absolute z-10 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto">
                            <form method="GET" action="{{ route('search') }}">
                                <input type="hidden" name="query" value="{{ $query }}">
                                <input type="hidden" name="type" value="{{ $type }}">
                                @foreach($categoryIds as $catId)
                                    <input type="hidden" name="categories[]" value="{{ $catId }}">
                                @endforeach
                                @foreach($attributeFilters as $attrId => $options)
                                    @if($attrId != $attribute->id)
                                        @foreach($options as $optionId)
                                            <input type="hidden" name="attributes[{{ $attrId }}][]" value="{{ $optionId }}">
                                        @endforeach
                                    @endif
                                @endforeach
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-semibold">{{ $attribute->name }}</h3>
                                        <button type="button" @click="open = false"
                                            class="text-teal-600 hover:underline text-sm">Close</button>
                                    </div>
                                    @foreach($attribute->options as $option)
                                        <label class="flex items-center py-2 cursor-pointer hover:bg-gray-50 px-2 rounded">
                                            <input type="checkbox" name="attributes[{{ $attribute->id }}][]" value="{{ $option->id }}"
                                                {{ isset($attributeFilters[$attribute->id]) && in_array($option->id, $attributeFilters[$attribute->id]) ? 'checked' : '' }} onchange="this.form.submit()"
                                                class="rounded text-teal-600 mr-2">
                                            <span>{{ $option->value }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Sort By --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50">
                        <span>Sort by</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg">
                        <div class="p-2">
                            <a href="#" class="block px-4 py-2 hover:bg-gray-50 rounded">Relevance</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-50 rounded">Newest first</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-50 rounded">Price: Low to high</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-50 rounded">Price: High to low</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Filter Tags --}}
            @if($query || !empty($categoryIds) || !empty($attributeFilters))
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    @if($query)
                        <span class="inline-flex items-center gap-1 bg-white border border-gray-300 px-3 py-1 rounded-full text-sm">
                            {{ $query }}
                            <a href="{{ route('search', array_merge(request()->except('query'), ['type' => $type])) }}"
                                class="hover:text-gray-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </span>
                    @endif
                    @foreach($categoryIds as $categoryId)
                        @php $cat = \App\Models\Category::find($categoryId); @endphp
                        @if($cat)
                            <span class="inline-flex items-center gap-1 bg-white border border-gray-300 px-3 py-1 rounded-full text-sm">
                                {{ $cat->name }}
                                <a href="{{ route('search', array_merge(request()->all(), ['categories' => array_diff($categoryIds, [$categoryId])])) }}"
                                    class="hover:text-gray-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </span>
                        @endif
                    @endforeach
                    @if(!empty($categoryIds) || !empty($attributeFilters))
                        <a href="{{ route('search', ['query' => $query, 'type' => $type]) }}"
                            class="text-sm text-teal-600 hover:underline ml-2">Clear filters</a>
                    @endif
                </div>
            @endif

            {{-- Category Links --}}
            <div class="mb-6 pb-4 border-b">
                <div class="flex flex-wrap gap-x-8 gap-y-2">
                    @foreach($categories->take(8) as $category)
                        <a href="{{ route('search', ['query' => $query, 'type' => 'product', 'categories' => [$category->id]]) }}"
                            class="text-teal-600 hover:underline">{{ $category->name }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Results Count --}}
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-600">{{ $results->total() }} results.</p>
                <a href="#" class="text-sm text-gray-500 hover:underline flex items-center gap-1">
                    Search results
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </a>
            </div>
        @endif

        @if($results->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">No results found{{ $query ? ' for "' . $query . '"' : '' }}.</p>
                <a href="{{ route('home') }}" class="text-teal-600 hover:underline mt-4 inline-block">Go back home</a>
            </div>
        @else
            @if($type === 'product')
                {{-- Product Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-x-2 gap-y-6">
                    @foreach($results as $product)
                        <div class="grid-item group">
                            <a href="{{ route('products.show', $product) }}" class="block cursor-pointer">
                                <div class="relative aspect-[20/27] overflow-hidden bg-gray-100 mb-2">
                                    <img src="{{ $product->getFeaturedImageUrl('preview') }}" class="object-cover w-full h-full"
                                        alt="{{ $product->name }}">

                                    {{-- Heart Icon (Bottom Right, Vinted Style) --}}
                                    <button
                                                        id="like-btn-{{ $product->id }}"
                                         class="absolute bottom-2 right-2 p-1.5 bg-white rounded-full shadow hover:bg-gray-50 text-gray-400 hover:text-red-500 transition-colors z-10"
                                        aria-label="Favourite" onclick="event.preventDefault(); toggleLike({{ $product->id }});">
                                        <svg class="w-5 h-5" fill="{{ $product->isFavorited() ? '#ef4444' : 'none' }}"
                                            stroke="{{ $product->isFavorited() ? '#ef4444' : 'currentColor' }}" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Card Information --}}
                                <div class="px-1">
                                    {{-- Line 1: Brand (or Name if no brand) --}}
                                    <p class="text-[13px] text-gray-500 uppercase truncate">
                                        {{ $product->brand ?? $product->name }}
                                    </p>

                                    {{-- Line 2: Size & Condition --}}
                                    <p class="text-[12px] text-gray-400 truncate">
                                        @php
                                            $meta = [];
                                            if ($product->size)
                                                $meta[] = $product->size;
                                            if ($product->condition)
                                                $meta[] = $product->condition;
                                        @endphp
                                        {{ implode(' · ', $meta) }}
                                    </p>

                                    {{-- Line 3: Price --}}
                                    <p class="text-[14px] font-semibold text-gray-900 mt-1">{{ $product->price }} MAD</p>

                                    {{-- Line 4: Fees --}}
                                    <p class="text-[10px] text-teal-600">
                                        {{ number_format($product->price * 1.05 + 10, 2) }} MAD incl.
                                    </p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $results->links() }}
                </div>
            @elseif($type === 'user')
                {{-- User Results --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($results as $user)
                        <div class="flex items-center p-4 bg-white border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 mr-4">
                                <img class="w-12 h-12 rounded-full object-cover"
                                    src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) }}"
                                    alt="{{ $user->full_name }}">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $user->full_name }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                            </div>
                            <div>
                                <a href="{{ route('vendor.show', $user) }}"
                                    class="text-teal-600 hover:text-teal-700 text-sm font-medium">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $results->links() }}
                </div>
            @endif
        @endif
    </div>
    </div>

    @push('scripts')
        <script>
            function toggleLike(productId) {
                const button = document.getElementById(`like-btn-${productId}`);
                if (!button) return;

                const svg = button.querySelector('svg');
                const isLiked = svg.classList.contains('!text-red-500');

                // Optimistic UI Update
                if (isLiked) {
                    svg.classList.remove('!text-red-500', '!fill-current', '!stroke-current');
                    svg.setAttribute('fill', 'none');
                    svg.setAttribute('stroke', 'currentColor');
                } else {
                    svg.classList.add('!text-red-500', '!fill-current', '!stroke-current');
                    svg.setAttribute('fill', '#ef4444');
                    svg.setAttribute('stroke', '#ef4444');
                }

                // AJAX Request
                fetch(`/products/${productId}/favorite`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (response.status === 401) {
                            window.location.href = '/login';
                            return;
                        }
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        // Confirm server state matches UI
                        if (data.liked) {
                            svg.classList.add('!text-red-500', '!fill-current', '!stroke-current');
                            svg.setAttribute('fill', '#ef4444');
                            svg.setAttribute('stroke', '#ef4444');
                        } else {
                            svg.classList.remove('!text-red-500', '!fill-current', '!stroke-current');
                            svg.setAttribute('fill', 'none');
                            svg.setAttribute('stroke', 'currentColor');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Revert UI on error
                        if (isLiked) {
                            svg.classList.add('!text-red-500', '!fill-current', '!stroke-current');
                            svg.setAttribute('fill', '#ef4444');
                            svg.setAttribute('stroke', '#ef4444');
                        } else {
                            svg.classList.remove('!text-red-500', '!fill-current', '!stroke-current');
                            svg.setAttribute('fill', 'none');
                            svg.setAttribute('stroke', 'currentColor');
                        }
                    });
            }
        </script>
    @endpush
@endsection