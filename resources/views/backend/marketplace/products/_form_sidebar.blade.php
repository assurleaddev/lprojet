{{-- Publish Card --}}
<x-card>
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Publish</h3>
        @can('marketplace.products.approve')
            <div class="mb-4">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="pending" @selected(old('status', $product->status ?? 'pending') == 'pending')>Pending
                    </option>
                    <option value="approved" @selected(old('status', $product->status ?? '') == 'approved')>Approved</option>
                    <option value="sold" @selected(old('status', $product->status ?? '') == 'sold')>Sold</option>
                </select>
            </div>
        @endcan
    </div>
    <div class="p-6 flex justify-between items-center">
        <a href="{{ route('admin.marketplace.products.index') }}"
            class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Cancel</a>
        <button type="submit" class="btn btn-primary">
            {{ isset($product) ? 'Update Product' : 'Save Product' }}
        </button>
    </div>
</x-card>

{{-- Details Card --}}
<div class="mt-6">
    <x-card>
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Details</h3>
            <div class="mb-4">
                <label for="price" class="form-label">Price (MAD)</label>
                <input type="number" id="price" name="price" value="{{ old('price', $product->price ?? '') }}"
                    class="form-input" required step="0.01">
                @error('price')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
            </div>
            <div>
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-input" required>
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
                @error('category_id')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
            </div>
        </div>
    </x-card>
</div>

{{-- Attributes Card --}}
<div class="mt-6">
    <x-card>
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Attributes</h3>
            <div id="attributes-container" class="space-y-4">
                {{-- Attributes will be loaded here dynamically --}}
            </div>
            @error('options')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
        </div>
    </x-card>
</div>

{{-- Product Images Card --}}
<div class="mt-6">
    <x-card>
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Product Images</h3>
            <input type="file" id="images" name="images[]" class="form-input" multiple accept="image/*">
            <p class="text-sm text-gray-500 mt-1">Upload between 3 and 7 images.</p>
            @error('images')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
            @error('images.*')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
        </div>
        @if(isset($product) && $product->images->count() > 0)
            <div class="p-6">
                <div id="image-gallery" class="grid grid-cols-3 gap-4">
                    @foreach($product->images as $image)
                        <div class="relative group" id="image-container-{{ $image->id }}">
                            <img src="{{ asset('storage/' . $image->path) }}" alt="Product Image"
                                class="w-full h-auto rounded-md object-cover">
                            <button type="button"
                                class="delete-image-btn absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 leading-none opacity-0 group-hover:opacity-100 transition-opacity"
                                data-url="{{ route('admin.marketplace.products.images.destroy', $image->id) }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-card>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const attributesContainer = document.getElementById('attributes-container');
            // Store the product's currently selected options if we are on the edit page
            const selectedOptions = @json(isset($product) ? $product->options->pluck('id') : []);

            categorySelect.addEventListener('change', function () {
                const categoryId = this.value;
                attributesContainer.innerHTML = '<p>Loading attributes...</p>'; // Show a loading message

                if (!categoryId) {
                    attributesContainer.innerHTML = ''; // Clear if no category is selected
                    return;
                }

                fetch(`/admin/marketplace/categories/${categoryId}/attributes`)
                    .then(response => response.json())
                    .then(attributes => {
                        attributesContainer.innerHTML = ''; // Clear loading message
                        if (attributes.length === 0) {
                            attributesContainer.innerHTML = '<p class="text-gray-500">No attributes for this category.</p>';
                        } else {
                            attributes.forEach(attribute => {
                                let optionsHtml = `<option value="">Select ${attribute.name}</option>`;
                                attribute.options.forEach(option => {
                                    // Check if this option was previously selected for the product
                                    const isSelected = selectedOptions.includes(option.id) ? 'selected' : '';
                                    optionsHtml += `<option value="${option.id}" ${isSelected}>${option.value}</option>`;
                                });

                                const attributeHtml = `
                                    <div>
                                        <label for="attribute-${attribute.id}" class="font-semibold text-gray-700 dark:text-gray-300">${attribute.name}</label>
                                        <select name="options[]" id="attribute-${attribute.id}" class="form-input mt-1">
                                            ${optionsHtml}
                                        </select>
                                    </div>
                                `;
                                attributesContainer.innerHTML += attributeHtml;
                            });
                        }
                    })
                    .catch(error => {
                        attributesContainer.innerHTML = '<p class="text-red-500">Could not load attributes.</p>';
                        console.error('Error fetching attributes:', error);
                    });
            });

            // If a category is already selected on page load (e.g., on edit page), trigger the change event
            if (categorySelect.value) {
                categorySelect.dispatchEvent(new Event('change'));
            }
        });


        document.getElementById('image-gallery')?.addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.delete-image-btn');

            if (deleteButton) {
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
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the image container from the view
                                imageContainer.remove();
                                // You can add a toast notification here for a better UX
                                alert(data.message);
                            } else {
                                alert('Error: Could not delete the image.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the image.');
                        });
                }
            }
        });
    </script>
@endpush