<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main Content Area -->
        <div class="lg:col-span-4 space-y-6">
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-3 space-y-2 sm:p-4">
                <div class="col-span-12">
                    <x-media-selector name="featured_image" :label="__('Featured Image')" :model="$product ?? null" collection="featured" />
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
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                    @if($category->children)
                                        @foreach($category->children as $child)
                                            <option value="{{ $child->id }}" @selected(old('category_id', $product->category_id ?? '') == $child->id)>
                                                &nbsp;&nbsp;&nbsp;{{ $child->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                            @error('category_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 p-3 space-y-2 sm:p-4">
                <label class="form-label">Attributes</label>
                {{-- This container will be filled by our JavaScript --}}
                <div id="attributes-container" class="space-y-4">
                    Select a category first
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
    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('category_id');
        const attributesContainer = document.getElementById('attributes-container');

        // If editing: currently selected option IDs (flat list)
        const selectedOptions = @json(isset($product) ? $product->options->pluck('id') : []);

        categorySelect.addEventListener('change', function () {
            const categoryId = this.value;
            attributesContainer.innerHTML = '<p>Loading attributes...</p>';

            if (!categoryId) {
                attributesContainer.innerHTML = '';
                return;
            }

            fetch(`/admin/marketplace/categories/${categoryId}/attributes`)
                .then(r => r.json())
                .then(attributes => {
                    attributesContainer.innerHTML = '';

                    if (!Array.isArray(attributes) || attributes.length === 0) {
                        attributesContainer.innerHTML = '<p class="text-gray-500">No attributes for this category.</p>';
                        return;
                    }

                    attributes.forEach(attribute => {
                        // Build a SELECT (single select by default)
                        // If you need multi-select for a given attribute, set `const isMultiple = true;`
                        // or detect from API: e.g., const isMultiple = !!attribute.allow_multiple;
                        const isMultiple = false; // change per your rules

                        const selectId = `attribute-${attribute.id}`;
                        const selectName = isMultiple ? `options[${attribute.id}][]` : `options[${attribute.id}]`;

                        let optionsHtml = `<option value="">Select ${attribute.name}</option>`;
                        (attribute.options || []).forEach(option => {
                            const isSel = Array.isArray(selectedOptions) && selectedOptions.includes(option.id) ? 'selected' : '';
                            optionsHtml += `<option value="${option.id}" ${isSel}>${option.value}</option>`;
                        });

                        const attributeHtml = `
                            <div class="mb-3">
                                <label for="${selectId}" class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">
                                    ${attribute.name}
                                </label>
                                <select
                                    id="${selectId}"
                                    name="${selectName}"
                                    class="form-control w-full"
                                    ${isMultiple ? 'multiple' : ''}
                                >
                                    ${optionsHtml}
                                </select>
                                ${isMultiple ? '<small class="text-xs text-gray-500">Hold Ctrl/Cmd to select multiple</small>' : ''}
                            </div>
                        `;

                        attributesContainer.insertAdjacentHTML('beforeend', attributeHtml);
                    });
                })
                .catch(error => {
                    attributesContainer.innerHTML = '<p class="text-red-500">Could not load attributes.</p>';
                    console.error('Error fetching attributes:', error);
                });
        });

        // Trigger once on load (edit page)
        if (categorySelect?.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    });

    // Image gallery delete (unchanged)
    document.getElementById('image-gallery')?.addEventListener('click', function (event) {
        const deleteButton = event.target.closest('.delete-image-btn');
        if (!deleteButton) return;

        event.preventDefault();

        if (confirm('Are you sure you want to delete this image?')) {
            const url = deleteButton.dataset.url;
            const imageContainer = deleteButton.closest('.relative.group');

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    imageContainer.remove();
                    alert(data.message);
                } else {
                    alert('Error: Could not delete the image.');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('An error occurred while deleting the image.');
            });
        }
    });
    </script>
@endpush