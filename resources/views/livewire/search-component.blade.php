<div class="container mx-auto px-6 md:px-12 py-6">
    @if($type === 'product')
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">
                @if(!empty($categoryIds) && $firstCat = \App\Models\Category::find($categoryIds[0]))
                    {{ $firstCat->name }}
                @else
                    {{ __('Articles') }}
                @endif
            </h1>
            <!-- Save Search (Placeholder) -->
             @auth
                <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                    {{ __('Save search') }}
                </button>
            @endauth
        </div>

        {{-- Filtrer Chips (Active Filtrers) --}}
        @if($query || !empty($categoryIds) || !empty($selectedBrands) || !empty($selectedConditions) || !empty($selectedAttributes) || $minPrice || $maxPrice)
            <div class="flex flex-wrap items-center gap-2 mb-4">
                 @if($query)
                    <span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-300 text-gray-900 px-3 py-1 rounded-full text-sm font-medium">
                        "{{ $query }}"
                        <button wire:click="removeFilter('query', null)" class="hover:text-gray-900 focus:outline-none">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </span>
                @endif
                
                @foreach($categoryIds as $catId)
                     @php $cat = \App\Models\Category::find($catId); @endphp
                     @if($cat)
                        <span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-300 px-3 py-1 rounded-full text-sm">
                            {{ $cat->name }}
                            <button wire:click="removeFilter('category', {{ $catId }})" class="hover:text-gray-700 focus:outline-none">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                     @endif
                @endforeach

                 @foreach($selectedBrands as $brandId)
                     @php $brand = $brands->firstWhere('id', $brandId); @endphp
                     @if($brand)
                        <span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-300 px-3 py-1 rounded-full text-sm">
                            {{ $brand->name }}
                            <button wire:click="removeFilter('brand', {{ $brandId }})" class="hover:text-gray-700 focus:outline-none">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                     @endif
                @endforeach
                
                 @foreach($selectedConditions as $condition)
                    <span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-300 px-3 py-1 rounded-full text-sm capitalize">
                        {{ str_replace('_', ' ', $condition) }}
                        <button wire:click="removeFilter('condition', '{{ $condition }}')" class="hover:text-gray-700 focus:outline-none">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </span>
                @endforeach

                  @foreach($selectedAttributes as $attrId => $options)
                    @if(is_array($options))
                        @foreach($options as $optionId => $isSelected)
                            @if($isSelected)
                                 @php 
                                    $option = \App\Models\Option::find($optionId);
                                 @endphp
                                 @if($option)
                                    <span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-300 px-3 py-1 rounded-full text-sm">
                                        {{ $option->value }}
                                        <button wire:click="removeFilter('attribute', {{ $optionId }})" class="hover:text-gray-700 focus:outline-none">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </span>
                                 @endif
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if($minPrice || $maxPrice)
                     <span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-300 px-3 py-1 rounded-full text-sm">
                        {{ $minPrice ?? '0' }} - {{ $maxPrice ?? '∞' }} MAD
                        <button wire:click="removeFilter('price', null)" class="hover:text-gray-700 focus:outline-none">
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </span>
                @endif
                
                <button wire:click="clearAllFilters" class="text-sm text-gray-700 hover:underline ml-2">Clear all</button>
            </div>
        @endif

        {{-- Filtrer Bar --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
             {{-- Category Filtrer --}}
             {{-- We can reuse existing component or inline it. Reusing is better but needs wire:model support or events --}}
             {{-- Existing component uses URL nav. We should probably inline it or update it. 
                  For speed, let's use a simpler inline dropdown here or modify the existing one to emit events.
                  Let's inline a simple version for full control since we are in a Livewire component now. --}}
            <div x-data="{ open: false }" class="relative">
                 <button @click="open = !open"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 {{ !empty($categoryIds) ? 'border-teal-600 ring-1 ring-teal-600' : '' }}">
                    <span>{{ __('Category') }}</span>
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                 </button>
                  <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                       {{-- Header --}}
                        <div class="flex items-center justify-between p-3 border-b border-gray-100 bg-gray-50">
                            <div class="flex items-center gap-2">
                                @if($this->browsingCategory)
                                    <button wire:click.stop="browseCategory({{ $this->browsingCategory->parent_id }})" class="p-1 hover:bg-gray-200 rounded-full transition-colors">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                    </button>
                                    <span class="font-semibold text-gray-900 text-sm line-clamp-1">{{ $this->browsingCategory->name }}</span>
                                @else
                                    <span class="font-semibold text-gray-900 text-sm">{{ __('Categories') }}</span>
                                @endif
                            </div>
                            <button @click="open = false" class="text-xs text-gray-500 hover:text-gray-900 font-medium">{{ __('Close') }}</button>
                        </div>

                        {{-- List --}}
                        <div class="max-h-80 overflow-y-auto">
                            @if($this->browsingCategory)
                                {{-- All in Current --}}
                                <button wire:click="selectCategory({{ $this->browsingCategory->id }}); open = false" class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 border-b border-gray-50 text-left group">
                                    <span class="text-gray-900 font-medium text-sm">{{ __('All in :name', ['name' => $this->browsingCategory->name]) }}</span>
                                    <div class="w-5 h-5 rounded-full border border-gray-300 {{ in_array($this->browsingCategory->id, $categoryIds) ? 'bg-teal-600 border-teal-600' : '' }}"></div>
                                </button>
                            @else
                                {{-- All Categories --}}
                                 <button wire:click="clearAllFilters(); open = false" class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 border-b border-gray-50 text-left group">
                                    <span class="text-gray-500 font-medium text-sm">{{ __('All Categories') }}</span>
                                </button>
                            @endif

                            @foreach($this->browsingCategories as $category)
                                <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 border-b border-gray-50 cursor-pointer"
                                     @if($category->children->count() > 0) wire:click.stop="browseCategory({{ $category->id }})" @else wire:click="selectCategory({{ $category->id }}); open = false" @endif>
                                    <span class="text-sm text-gray-700 {{ in_array($category->id, $categoryIds) ? 'font-bold text-gray-900' : '' }}">
                                        {{ $category->translated_name }}
                                    </span>
                                    @if($category->children->count() > 0)
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    @endif
                                </div>
                            @endforeach
                            
                            @if($this->browsingCategories->isEmpty())
                                 <div class="p-4 text-center text-gray-500 text-xs italic">No sub-categories</div>
                            @endif
                        </div>
                  </div>
            </div>

            {{-- Size Filtrer --}}
            @if($sizeAttributes->isNotEmpty())
                 <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50">
                        <span>{{ __('Taille') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto p-4">
                         <div class="space-y-4">
                            @foreach($sizeAttributes as $attribute)
                                <div>
                                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">{{ $attribute->name }}</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($attribute->options as $option)
                                            <label class="flex items-center text-sm cursor-pointer hover:text-gray-700">
                                                <input type="checkbox" wire:model.live="selectedAttributes.{{ $attribute->id }}.{{ $option->id }}" class="rounded text-gray-700 mr-2 border-gray-300 focus:ring-gray-500">
                                                <span>{{ $option->value }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Brand Filtrer --}}
             @if($brands->isNotEmpty())
                <div x-data="{ open: false, search: '', brandNames: @js($brands->pluck('name')->map(fn($n) => strtolower($n))->values()) }" class="relative">
                    <button @click="open = !open; if(open) $nextTick(() => $refs.brandSearch.focus())"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 {{ !empty($selectedBrands) ? 'border-teal-600 ring-1 ring-teal-600' : '' }}">
                        <span>{{ __('Marque') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false; search = ''" style="display: none;" class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-2">
                        <input x-ref="brandSearch" type="text" x-model="search" placeholder="{{ __('Search brands...') }}"
                            class="w-full px-3 py-2 mb-2 border border-gray-200 rounded text-sm focus:outline-none focus:border-gray-400" @click.stop>
                        <div class="max-h-64 overflow-y-auto">
                            @foreach($brands as $brand)
                                <label x-show="search === '' || @js(strtolower($brand->name)).includes(search.toLowerCase())"
                                    class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" wire:model.live="selectedBrands" value="{{ $brand->id }}" class="rounded text-gray-700 mr-2 border-gray-300 focus:ring-gray-500">
                                    <span class="text-sm">{{ $brand->name }}</span>
                                </label>
                            @endforeach
                            <p x-show="search !== '' && !brandNames.some(n => n.includes(search.toLowerCase()))"
                                class="text-xs text-gray-400 text-center py-3">{{ __('No brands found') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Condition Filtrer --}}
            @if(count($conditions) > 0)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 {{ !empty($selectedConditions) ? 'border-teal-600 ring-1 ring-teal-600' : '' }}">
                        <span>{{ __('État') }}</span>
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                     <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden p-2">
                         @foreach($conditions as $condition)
                            <label class="flex items-center py-2 px-2 cursor-pointer hover:bg-gray-50 rounded">
                                <input type="checkbox" wire:model.live="selectedConditions" value="{{ $condition }}" class="rounded text-gray-700 mr-2 border-gray-300 focus:ring-gray-500">
                                <span class="capitalize">{{ str_replace('_', ' ', $condition) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Color Filtrer --}}
             @if($colorAttribute)
                <div x-data="{ open: false }" class="relative">
                     <button @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 {{ isset($selectedAttributes[$colorAttribute->id]) ? 'border-teal-600 ring-1 ring-teal-600' : '' }}">
                        <span>{{ __('Couleur') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-y-auto p-3">
                         <div class="grid grid-cols-2 gap-2">
                             @foreach($colorAttribute->options as $option)
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                                     <input type="checkbox" wire:model.live="selectedAttributes.{{ $colorAttribute->id }}.{{ $option->id }}" class="rounded text-gray-700 mr-2 border-gray-300 focus:ring-gray-500">
                                     <div class="flex items-center gap-1">
                                         <span class="w-4 h-4 rounded-full border border-gray-200" style="background-color: {{ $option->value }}"></span>
                                         <span class="text-sm text-gray-700">{{ $option->name ?? $option->value }}</span>
                                     </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

             {{-- Price Filtrer --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 {{ ($minPrice || $maxPrice) ? 'border-teal-600 ring-1 ring-teal-600' : '' }}">
                    <span>{{ __('Prix') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                 <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-lg p-4">
                     <div class="flex items-center gap-2">
                         <div class="flex-1">
                             <label class="text-xs text-gray-500 block mb-1">De</label>
                             <input type="number" wire:model.live.debounce.500ms="minPrice" placeholder="Min" class="w-full border-gray-300 rounded-md text-sm focus:ring-gray-500 focus:border-gray-500">
                         </div>
                         <div class="flex-1">
                             <label class="text-xs text-gray-500 block mb-1">À</label>
                             <input type="number" wire:model.live.debounce.500ms="maxPrice" placeholder="Max" class="w-full border-gray-300 rounded-md text-sm focus:ring-gray-500 focus:border-gray-500">
                         </div>
                     </div>
                </div>
            </div>
             
             {{-- Sort By --}}
            <div x-data="{ open: false }" class="relative ml-auto">
                <button @click="open = !open"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50">
                    <span>
                        @if($sort === 'newest') Plus récents d'abord
                        @elseif($sort === 'price_asc') Prix : croissant
                        @elseif($sort === 'price_desc') Prix : décroissant
                        @else Relevance
                        @endif
                    </span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 z-50 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg">
                    <div class="p-2">
                        <button wire:click="$set('sort', 'newest'); open=false" class="block w-full text-left px-4 py-2 hover:bg-gray-50 rounded text-sm">{{ __('Newest first') }}</button>
                        <button wire:click="$set('sort', 'price_asc'); open=false" class="block w-full text-left px-4 py-2 hover:bg-gray-50 rounded text-sm">Prix : croissant</button>
                        <button wire:click="$set('sort', 'price_desc'); open=false" class="block w-full text-left px-4 py-2 hover:bg-gray-50 rounded text-sm">Prix : décroissant</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Results Grid --}}
        @if($results->isEmpty())
             <div class="text-center py-12">
                <p class="text-gray-500 text-lg">{{ __('No results found.') }}</p>
                <button wire:click="clearAllFilters" class="text-gray-700 hover:underline mt-4">{{ __('Clear filters') }}</button>
            </div>
        @else
             <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-x-2 gap-y-6">
                @foreach($results as $product)
                    <div class="grid-item relative">
                        <div class="used-image-wrapper">
                            <a href="{{ route('products.show', $product) }}" class="absolute inset-0 z-10 cursor-pointer block"></a>
                            <img data-src="{{ $product->getFeaturedImageUrl('preview') }}"
                                src="{{ $product->getFeaturedImageUrl('preview') }}" class="lazy used-image-content"
                                alt="{{ $product->name }}">

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
                @endforeach
            </div>
            <div class="mt-8">
                {{ $results->links() }}
            </div>
        @endif

    @else
        {{-- Member search results --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold">{{ __('Members') }}</h1>
        </div>

        @if($results->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">{{ __('No members found.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($results as $user)
                    <a href="{{ route('vendor.show', $user) }}"
                        class="flex flex-col items-center text-center p-4 bg-white border border-gray-100 rounded-xl hover:shadow-md transition">
                        @if($user->avatar_id && $user->avatar)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}"
                                class="w-16 h-16 rounded-full object-cover mb-3">
                        @else
                            <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-xl font-bold mb-3"
                                style="background-color: var(--brand)">
                                {{ strtoupper(substr($user->username, 0, 2)) }}
                            </div>
                        @endif
                        <p class="text-sm font-semibold text-gray-900 truncate w-full">{{ $user->username }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $user->products()->where('status', 'approved')->count() }} {{ __('items') }}</p>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $results->links() }}
            </div>
        @endif

    @endif
</div>
