<div class="container mx-auto px-6 md:px-12 py-6">
    @if($type === 'product')
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">
                @if(!empty($categoryIds) && $firstCat = \App\Models\Category::find($categoryIds[0]))
                    {{ $firstCat->name }}
                @else
                    {{ __('Items') }}
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

        {{-- Filter Chips (Active Filters) --}}
        @if($query || !empty($categoryIds) || !empty($selectedBrands) || !empty($selectedConditions) || !empty($selectedAttributes) || $minPrice || $maxPrice)
            <div class="flex flex-wrap items-center gap-2 mb-4">
                 @if($query)
                    <span class="inline-flex items-center gap-1 bg-teal-50 border border-teal-200 text-teal-700 px-3 py-1 rounded-full text-sm font-medium">
                        "{{ $query }}"
                        <button wire:click="removeFilter('query', null)" class="hover:text-teal-900 focus:outline-none">
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
                
                <button wire:click="clearAllFilters" class="text-sm text-teal-600 hover:underline ml-2">Clear all</button>
            </div>
        @endif

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
             {{-- Category Filter --}}
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
                  <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-10 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
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
                                    <span class="text-sm text-gray-700 {{ in_array($category->id, $categoryIds) ? 'font-bold text-teal-700' : '' }}">
                                        {{ $category->name }}
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

            {{-- Size Filter --}}
            @if($sizeAttributes->isNotEmpty())
                 <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50">
                        <span>{{ __('Taille') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-10 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto p-4">
                         <div class="space-y-4">
                            @foreach($sizeAttributes as $attribute)
                                <div>
                                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">{{ $attribute->name }}</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($attribute->options as $option)
                                            <label class="flex items-center text-sm cursor-pointer hover:text-teal-600">
                                                <input type="checkbox" wire:model.live="selectedAttributes.{{ $attribute->id }}.{{ $option->id }}" class="rounded text-teal-600 mr-2 border-gray-300 focus:ring-teal-500">
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

            {{-- Brand Filter --}}
             @if($brands->isNotEmpty())
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 {{ !empty($selectedBrands) ? 'border-teal-600 ring-1 ring-teal-600' : '' }}">
                        <span>{{ __('Marque') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-10 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-y-auto p-2">
                         <input type="text" placeholder="Search brands..." class="w-full px-3 py-2 mb-2 border rounded text-sm focus:outline-none focus:border-teal-500" @click.stop>
                         {{-- Ideal place for a computed property for searching brands inside the dropdown, but simpler loop for now --}}
                         @foreach($brands as $brand)
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                <input type="checkbox" wire:model.live="selectedBrands" value="{{ $brand->id }}" class="rounded text-teal-600 mr-2 border-gray-300 focus:ring-teal-500">
                                <span class="text-sm">{{ $brand->name }}</span>
                            </label>
                         @endforeach
                    </div>
                </div>
            @endif

            {{-- Condition Filter --}}
            @if(count($conditions) > 0)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 {{ !empty($selectedConditions) ? 'border-teal-600 ring-1 ring-teal-600' : '' }}">
                        <span>{{ __('État') }}</span>
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                     <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-10 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden p-2">
                         @foreach($conditions as $condition)
                            <label class="flex items-center py-2 px-2 cursor-pointer hover:bg-gray-50 rounded">
                                <input type="checkbox" wire:model.live="selectedConditions" value="{{ $condition }}" class="rounded text-teal-600 mr-2 border-gray-300 focus:ring-teal-500">
                                <span class="capitalize">{{ str_replace('_', ' ', $condition) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Color Filter --}}
             @if($colorAttribute)
                <div x-data="{ open: false }" class="relative">
                     <button @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 {{ isset($selectedAttributes[$colorAttribute->id]) ? 'border-teal-600 ring-1 ring-teal-600' : '' }}">
                        <span>{{ __('Couleur') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-10 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-y-auto p-3">
                         <div class="grid grid-cols-2 gap-2">
                             @foreach($colorAttribute->options as $option)
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-1 rounded">
                                     <input type="checkbox" wire:model.live="selectedAttributes.{{ $colorAttribute->id }}.{{ $option->id }}" class="rounded text-teal-600 mr-2 border-gray-300 focus:ring-teal-500">
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

             {{-- Price Filter --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50 {{ ($minPrice || $maxPrice) ? 'border-teal-600 ring-1 ring-teal-600' : '' }}">
                    <span>{{ __('Prix') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                 <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-10 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-lg p-4">
                     <div class="flex items-center gap-2">
                         <div class="flex-1">
                             <label class="text-xs text-gray-500 block mb-1">De</label>
                             <input type="number" wire:model.live.debounce.500ms="minPrice" placeholder="Min" class="w-full border-gray-300 rounded-md text-sm focus:ring-teal-500 focus:border-teal-500">
                         </div>
                         <div class="flex-1">
                             <label class="text-xs text-gray-500 block mb-1">À</label>
                             <input type="number" wire:model.live.debounce.500ms="maxPrice" placeholder="Max" class="w-full border-gray-300 rounded-md text-sm focus:ring-teal-500 focus:border-teal-500">
                         </div>
                     </div>
                </div>
            </div>
             
             {{-- Sort By --}}
            <div x-data="{ open: false }" class="relative ml-auto">
                <button @click="open = !open"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full hover:bg-gray-50">
                    <span>
                        @if($sort === 'newest') Newest first
                        @elseif($sort === 'price_asc') Price: Low to high
                        @elseif($sort === 'price_desc') Price: High to low
                        @else Relevance
                        @endif
                    </span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg">
                    <div class="p-2">
                        <button wire:click="$set('sort', 'newest'); open=false" class="block w-full text-left px-4 py-2 hover:bg-gray-50 rounded text-sm">Newest first</button>
                        <button wire:click="$set('sort', 'price_asc'); open=false" class="block w-full text-left px-4 py-2 hover:bg-gray-50 rounded text-sm">Price: Low to high</button>
                        <button wire:click="$set('sort', 'price_desc'); open=false" class="block w-full text-left px-4 py-2 hover:bg-gray-50 rounded text-sm">Price: High to low</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Results Grid --}}
        @if($results->isEmpty())
             <div class="text-center py-12">
                <p class="text-gray-500 text-lg">No results found.</p>
                <button wire:click="clearAllFilters" class="text-teal-600 hover:underline mt-4">Clear filters</button>
            </div>
        @else
             <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-x-2 gap-y-6">
                @foreach($results as $product)
                     <div class="grid-item group">
                        <a href="{{ route('products.show', $product) }}" class="block cursor-pointer">
                            <div class="relative aspect-[20/27] overflow-hidden bg-gray-100 mb-2">
                                <img src="{{ $product->getFeaturedImageUrl('preview') }}" class="object-cover w-full h-full" alt="{{ $product->name }}">
                                {{-- Heart Icon - Note: Need to verify if toggleLike JS works with Livewire updates or use Livewire action --}}
                                <button onclick="event.preventDefault(); toggleLike({{ $product->id }});" id="like-btn-{{ $product->id }}"
                                    class="absolute bottom-2 right-2 p-1.5 bg-white rounded-full shadow hover:bg-gray-50 text-gray-400 hover:text-red-500 transition-colors z-10">
                                     <svg class="w-5 h-5 {{ $product->isFavorited() ? '!text-red-500 !fill-current' : '' }}" fill="{{ $product->isFavorited() ? '#ef4444' : 'none' }}" stroke="{{ $product->isFavorited() ? '#ef4444' : 'currentColor' }}" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-1">
                                <p class="text-[13px] text-gray-500 uppercase truncate">{{ $product->brand->name ?? $product->name }}</p>
                                <p class="text-[12px] text-gray-400 truncate">
                                    @php
                                        $meta = [];
                                        if ($product->size) $meta[] = $product->size;
                                        if ($product->condition) $meta[] = str_replace('_', ' ', $product->condition);
                                    @endphp
                                    {{ implode(' · ', $meta) }}
                                </p>
                                <p class="text-[14px] font-semibold text-gray-900 mt-1">{{ $product->price }} MAD</p>
                                <p class="text-[10px] text-teal-600">{{ number_format($product->price * 1.05 + 10, 2) }} MAD incl.</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $results->links() }}
            </div>
        @endif
    
    @endif
</div>
