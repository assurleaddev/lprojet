<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main Content Area -->
        <div class="lg:col-span-4 space-y-6">
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-3 space-y-2 sm:p-4">
                <div class="col-span-12">
                <div class="col-span-12">
                     <x-media-selector 
                        name="featured_image" 
                        :label="__('Featured Image')" 
                        :existingMedia="$featuredMedia ?? []"
                        :existingAltText="$product->name ?? ''"
                        height="300px"
                    />
                </div>
                </div>
                <x-media-selector
                    name="images"
                    label="{{ __('Product images') }}"
                    :multiple="true"
                    :min="3"
                    :max="7"
                    allowedTypes="images"
                    :existingMedia="isset($product)
                        ? $existingMedia
                        : []"
                    :existingAltText="isset($product) ? $product->name : ''"
                    removeCheckboxName="remove_product_image"
                    removeCheckboxLabel="{{ __('Remove product image') }}"
                    :showPreview="true"
                    class="mt-1"
                />
            </div>
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="p-5 space-y-4 sm:p-6">
                    <div>
                        <!-- Product Name -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label for="title"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Name') }}</label>
                                @can('ai_content.generate')
                                <div x-data="{ aiModalOpen: false }">
                                    @include('backend.pages.posts.partials.ai-content-generator')
                                </div>
                                @endcan
                            </div>
                            <input type="text" name="name" id="name" required  maxlength="255"
                                class="form-control" value="{!! old('name', $product->name ?? '') !!}">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label for="content"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Content') }}</label>
                        <textarea name="description" id="description" rows="10">{!! old('description', $product->description ?? '') !!}</textarea>
                    </div>

                    <div class="space-y-1 flex flex-col md:flex-row md:space-x-4 md:space-y-0">
                        <div class="w-100 md:w-1/2">
                            <label for="price"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Price') }}</label>
                            <input type="number" name="price" id="price" step="0.01" min="0" required
                                class="form-control" value="{{ old('price', $product->price ?? '') }}">
                        </div>
                        <div class="w-100 md:w-1/2">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            {{-- ALPINE SEARCHABLE SELECT FOR CATEGORY --}}
                            @php
                                $categoryOptions = [];
                                foreach($categories as $category) {
                                    if ($category->children && $category->children->count()) {
                                        foreach($category->children as $child) {
                                            if ($child->children && $child->children->count()) {
                                                foreach($child->children as $grandChild) {
                                                    $categoryOptions[] = ['value' => $grandChild->id, 'label' => $child->name . ' > ' . $grandChild->name];
                                                }
                                            } else {
                                                $categoryOptions[] = ['value' => $child->id, 'label' => $child->name];
                                            }
                                        }
                                    } else {
                                        $categoryOptions[] = ['value' => $category->id, 'label' => $category->name];
                                    }
                                }
                            @endphp
                            
                            <div x-data="searchableSelect({ 
                                    options: {{ json_encode($categoryOptions) }}, 
                                    selected: '{{ old('category_id', $product->category_id ?? '') }}', 
                                    id: 'category_id',
                                    placeholder: 'Search Category...' 
                                })" class="relative">
                                
                                <input type="hidden" name="category_id" id="category_id" :value="selected">
                                
                                <button type="button" @click="toggle()" class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <span class="block truncate" x-text="selectedLabel"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                          <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                                
                                <div x-show="open" @click.away="close()" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                    <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-2 py-1 border-b border-gray-200 dark:border-gray-700">
                                        <input x-ref="searchInput" x-model="search" type="text" class="w-full border-0 focus:ring-0 p-1 text-sm bg-gray-50 dark:bg-gray-700 rounded" placeholder="Type to filter...">
                                    </div>
                                    <ul class="pt-1">
                                        <template x-for="option in filteredOptions" :key="option.value">
                                            <li @click="select(option.value)" class="text-gray-900 dark:text-gray-200 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white group">
                                                <span class="font-normal block truncate" x-text="option.label"></span>
                                                <span x-show="selected == option.value" class="text-blue-600 group-hover:text-white absolute inset-y-0 right-0 flex items-center pr-4">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </li>
                                        </template>
                                        <li x-show="filteredOptions.length === 0" class="text-gray-500 py-2 pl-3 pr-9">No options found</li>
                                    </ul>
                                </div>
                            </div>
                            @error('category_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    
                    {{-- Row for Brand and Condition --}}
                    <div class="space-y-1 flex flex-col md:flex-row md:space-x-4 md:space-y-0 mt-4">
                        <div class="w-100 md:w-1/2">
                            <label for="brand_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
                            @php
                                $brandOptions = $brands->map(function($brand) {
                                    return ['value' => $brand->id, 'label' => $brand->name];
                                })->values()->toArray();
                            @endphp
                             <div x-data="searchableSelect({ 
                                    options: {{ json_encode($brandOptions) }}, 
                                    selected: '{{ old('brand_id', $product->brand_id ?? '') }}', 
                                    id: 'brand_id',
                                    placeholder: 'Search Brand...'
                                })" class="relative">
                                <input type="hidden" name="brand_id" id="brand_id" :value="selected">
                                <button type="button" @click="toggle()" class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <span class="block truncate" x-text="selectedLabel"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </span>
                                </button>
                                <div x-show="open" @click.away="close()" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                    <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-2 py-1 border-b border-gray-200 dark:border-gray-700">
                                        <input x-ref="searchInput" x-model="search" type="text" class="w-full border-0 focus:ring-0 p-1 text-sm bg-gray-50 dark:bg-gray-700 rounded" placeholder="Filter brands...">
                                    </div>
                                    <ul class="pt-1">
                                        <template x-for="option in filteredOptions" :key="option.value">
                                            <li @click="select(option.value)" class="text-gray-900 dark:text-gray-200 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white group">
                                                <span class="font-normal block truncate" x-text="option.label"></span>
                                                <span x-show="selected == option.value" class="text-blue-600 group-hover:text-white absolute inset-y-0 right-0 flex items-center pr-4"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></span>
                                            </li>
                                        </template>
                                        <li x-show="filteredOptions.length === 0" class="text-gray-500 py-2 pl-3 pr-9">No brands found</li>
                                    </ul>
                                </div>
                            </div>
                            @error('brand_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        <div class="w-100 md:w-1/2">
                            <label for="condition" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condition</label>
                            <select id="condition" name="condition" class="form-control">
                                <option value="">Select condition</option>
                                @php
                                    $conditions = [
                                        'New with tags',
                                        'New without tags',
                                        'Very good',
                                        'Good',
                                        'Satisfactory'
                                    ];
                                @endphp
                                @foreach($conditions as $cond)
                                    <option value="{{ $cond }}" @selected(old('condition', $product->condition ?? '') == $cond)>
                                        {{ $cond }}
                                    </option>
                                @endforeach
                            </select>
                             @error('condition')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-3 space-y-2 sm:p-4">
                <label class="form-label">Attributes</label>
                {{-- This container will be filled by our JavaScript --}}
                <div id="attributes-container" class="space-y-4">
                    <p class="text-gray-500 italic">Select a category to load attributes.</p>
                </div>
                @error('options')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
            </div>

            <!-- Status and Visibility -->
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="px-4 py-3 sm:px-6 border-b border-gray-100 dark:border-gray-800">
                    @can('product.aprove')
                        <h3 class="text-base font-medium text-gray-700 dark:text-white">{{ __('Status & Visibility') }}</h3>
                    @endcan
                    
                </div>
                <div class="p-3 space-y-2 sm:p-4">
                    <!-- Status with Combobox -->
                    @can('product.aprove')
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="pending" @selected(old('status', $product->status ?? '') == 'pending')>Pending</option>
                            <option value="approved" @selected(old('status', $product->status ?? '') == 'approved')>Approved</option>
                            <option value="sold" @selected(old('status', $product->status ?? '') == 'sold')>Sold</option>
                        </select>
                    @endcan
                    

                    <div class="mt-4">
                        <x-buttons.submit-buttons cancelUrl="{{ route('admin.products.index') }}" />
                    </div>
                    {{-- {!! Hook::applyFilters(PostFilterHook::POST_FORM_AFTER_SUBMIT_BUTTONS, '') !!} --}}
                </div>
            </div>

            {{-- <x-advanced-fields :post-meta="isset($post) ? $post->getAllMeta() : []" /> --}}
        </div>

    </div>
@push('scripts')
    <x-quill-editor :editor-id="'description'" height="200px" maxHeight="-1" />

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('searchableSelect', (config) => ({
            options: config.options || [], 
            selected: config.selected || null,
            open: false,
            search: '',
            placeholder: config.placeholder || 'Select option',

            get filteredOptions() {
                if (this.search === '') return this.options;
                return this.options.filter(option => 
                    String(option.label).toLowerCase().includes(this.search.toLowerCase())
                );
            },
            
            get selectedLabel() {
                if (!this.selected) return this.placeholder;
                const opt = this.options.find(o => o.value == this.selected);
                return opt ? opt.label : this.placeholder;
            },

            select(value) {
                this.selected = value;
                this.open = false;
                this.search = '';
                // If it's category, notify loader
                if (config.id === 'category_id') {
                    window.loadAttributes(value);
                }
            },
            
            toggle() {
                if (this.open) {
                    this.open = false;
                } else {
                    this.open = true;
                    this.search = '';
                    this.$nextTick(() => { this.$refs.searchInput.focus(); });
                }
            },
            
            close() {
                this.open = false;
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', function () {
        const attributesContainer = document.getElementById('attributes-container');
        
        window.loadAttributes = function(categoryId) {
             console.log('Loading attributes for category:', categoryId);
             attributesContainer.innerHTML = '<div class="animate-pulse flex space-x-4"><div class="flex-1 space-y-4 py-1"><div class="h-4 bg-gray-200 rounded w-3/4"></div><div class="space-y-2"><div class="h-4 bg-gray-200 rounded"></div><div class="h-4 bg-gray-200 rounded w-5/6"></div></div></div></div>'; 
             
            if (!categoryId) {
                attributesContainer.innerHTML = '<p class="text-gray-500 italic">Select a category first</p>';
                return;
            }

            fetch(`/admin/marketplace/categories/${categoryId}/attributes`)
                .then(r => r.json())
                .then(attributes => {
                    console.log('Fetched Attributes:', attributes);
                    attributesContainer.innerHTML = '';

                    if (!Array.isArray(attributes) || attributes.length === 0) {
                        attributesContainer.innerHTML = '<p class="text-gray-500 italic">No specific attributes found for this category.</p>';
                        return;
                    }

                    attributes.forEach(attribute => {
                        const attrName = attribute.name || `Attribute #${attribute.id}`;
                        const selectId = `attribute-${attribute.id}`;
                        const selectName = `options[${attribute.id}]`; 
                        let inputHtml = '';

                        if (attribute.type === 'color') {
                            inputHtml = `<div class="flex flex-wrap gap-2 mt-2">`;
                            (attribute.options || []).forEach(option => {
                                const isSel = (window.selectedOptionIds || []).includes(option.id) ? 'checked' : '';
                                const colorName = `options[${attribute.id}][]`; 
                                const colorStyle = option.value.toLowerCase(); 
                                inputHtml += `
                                    <label class="cursor-pointer group relative">
                                        <input type="checkbox" name="${colorName}" value="${option.id}" class="peer sr-only" ${isSel}>
                                        <div class="w-8 h-8 rounded-full border border-gray-300 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-blue-500 flex items-center justify-center shadow-sm" style="background-color: ${colorStyle};"></div>
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-10 pointer-events-none">${option.value}</span>
                                    </label>
                                `;
                            });
                            inputHtml += `</div>`;
                        } else if (attribute.type === 'radio') {
                             inputHtml = `<div class="grid grid-cols-2 lg:grid-cols-3 gap-2 mt-2">`;
                            (attribute.options || []).forEach(option => {
                                const isSel = (window.selectedOptionIds || []).includes(option.id) ? 'checked' : '';
                                inputHtml += `
                                    <label class="inline-flex items-center space-x-2 p-2 border border-gray-200 rounded hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 cursor-pointer">
                                        <input type="radio" name="${selectName}" value="${option.id}" class="form-radio text-blue-600 focus:ring-blue-500" ${isSel}>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">${option.value}</span>
                                    </label>
                                `;
                            });
                            inputHtml += `</div>`;
                        } else {
                            let optionsHtml = `<option value="">Select ${attrName}</option>`;
                            (attribute.options || []).forEach(option => {
                                const isSel = (window.selectedOptionIds || []).includes(option.id) ? 'selected' : '';
                                optionsHtml += `<option value="${option.id}" ${isSel}>${option.value}</option>`;
                            });
                            inputHtml = `
                                <div class="relative mt-1">
                                    <select id="${selectId}" name="${selectName}" class="form-control w-full appearance-none pr-8">
                                        ${optionsHtml}
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                                        <svg class="fill-current h-4 w-4" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                    </div>
                                </div>
                            `;
                        }

                        const attributeHtml = `
                            <div class="mb-5 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 transition hover:shadow-md">
                                <label for="${selectId}" class="block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    ${attrName}
                                </label>
                                ${inputHtml}
                            </div>
                        `;
                        attributesContainer.insertAdjacentHTML('beforeend', attributeHtml);
                    });
                })
                .catch(error => {
                    attributesContainer.innerHTML = '<div class="text-red-500 p-2">Failed to load attributes.</div>';
                    console.error('Error:', error);
                });
        };

        window.selectedOptionIds = @json(isset($product) ? $product->options->pluck('id') : []);
        
        // Initial load check
        const initCatId = "{{ old('category_id', $product->category_id ?? '') }}";
        if (initCatId) {
            window.loadAttributes(initCatId);
        }
    });

    // Image Delete logic
    document.getElementById('image-gallery')?.addEventListener('click', function (event) {
        const btn = event.target.closest('.delete-image-btn');
        if (!btn) return;
        event.preventDefault();
        if (confirm('Delete this image?')) {
            fetch(btn.dataset.url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(r => r.json()).then(data => {
                if (data.success) { btn.closest('.relative.group').remove(); } 
                else { alert('Error deleting image.'); }
            });
        }
    });
    </script>
@endpush