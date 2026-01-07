@extends('layouts.app')

@section('title', 'Sell an item')

@section('content')
    <div class="mx-auto max-w-[800px] px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Sell an item</h1>

        <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" id="item-form">
            @csrf

            <!-- Photo Upload Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
                <div class="mb-4">
                    <span class="block font-semibold mb-1">Photos</span>
                    <span class="text-sm text-gray-500">Add up to 20 photos. <span class="text-teal-600">See
                            tips.</span></span>
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 min-h-[200px] flex flex-wrap gap-4"
                    id="drop-zone">
                    <!-- Upload Button -->
                    <div class="w-32 h-32 flex items-center justify-center border border-teal-500 rounded cursor-pointer hover:bg-teal-50 transition-colors relative"
                        id="upload-btn-container">
                        <input type="file" name="images[]" id="image-input" multiple accept="image/*"
                            class="absolute inset-0 opacity-0 cursor-pointer">
                        <div class="text-center">
                            <svg class="w-8 h-8 text-teal-500 mx-auto mb-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="text-teal-500 font-semibold text-sm">Upload photos</span>
                        </div>
                    </div>

                    <!-- Preview Container (Sortable) -->
                    <div id="image-previews" class="contents">
                        <!-- Images will be appended here -->
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Drag and drop to reorder. The first photo will be the main one.</p>
            </div>

            <!-- Item Details -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6 space-y-6">
                <div>
                    <label class="block font-semibold mb-1">Title</label>
                    <input type="text" name="title" value="{{ $duplicateProduct->name ?? '' }}"
                        class="w-full border border-gray-300 rounded-md p-2.5 focus:ring-teal-500 focus:border-teal-500"
                        placeholder="e.g. White COS Jumper" required>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Describe your item</label>
                    <textarea name="description" rows="4"
                        class="w-full border border-gray-300 rounded-md p-2.5 focus:ring-teal-500 focus:border-teal-500"
                        placeholder="e.g. only worn a few times, true to size"
                        required>{{ $duplicateProduct->description ?? '' }}</textarea>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6 space-y-6">
                <div>
                    <label class="block font-semibold mb-1">Category</label>
                    <livewire:category-selector name="category_id" :value="$duplicateProduct->category_id ?? null" />
                </div>

                <div x-data="brandDropdown">
                    <label class="block font-semibold mb-1">Brand</label>
                    <div class="relative">
                        <button @click="toggle" type="button"
                            class="w-full border border-gray-300 rounded-md p-2.5 text-left">
                            <span x-text="selectedLabel || 'Select Brand'" class="block truncate"></span>
                        </button>
                        <div x-show="open" @click.outside="close()" x-cloak
                            class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-hidden">
                            <input x-ref="searchInput" x-model="search" type="text" placeholder="Search brands..."
                                class="w-full p-2 border-b focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <div class="max-h-48 overflow-y-auto">
                                <template x-for="option in filteredOptions" :key="option.value">
                                    <div @click="select(option.value, option.label)"
                                        class="p-2 hover:bg-teal-50 cursor-pointer" x-text="option.label"></div>
                                </template>
                                <div x-show="filteredOptions.length === 0" class="p-2 text-gray-500 text-sm">No brands found
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="brand_id" :value="selected">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Condition</label>
                    <select name="condition"
                        class="w-full border border-gray-300 rounded-md p-2.5 focus:ring-teal-500 focus:border-teal-500">
                        <option value="">Select Condition</option>
                        @foreach($conditions as $cond)
                            <option value="{{ $cond }}">{{ ucwords(str_replace('_', ' ', $cond)) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Dynamic Attributes Container -->
                <div id="dynamic-attributes" class="space-y-6">
                    <!-- Attributes will be injected here -->
                </div>

            </div>

            <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6 space-y-6">
                <div>
                    <label class="block font-semibold mb-1">Price</label>
                    <div class="relative">
                        <input type="number" name="price" step="0.01" value="{{ $duplicateProduct->price ?? '' }}"
                            class="w-full border border-gray-300 rounded-md py-2.5 pl-12 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="0.00" required>
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">MAD</span>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-teal-600 text-white font-bold py-3 rounded-md hover:bg-teal-700 transition-colors">Upload</button>
        </form>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script>
            // Alpine.js Searchable Select Component
            document.addEventListener('alpine:init', () => {
                Alpine.data('brandDropdown', () => ({
                    open: false,
                    search: '',
                    selected: null,
                    selectedLabel: 'Select Brand',
                    options: @json($brands->map(fn($b) => ['value' => $b->id, 'label' => $b->name])),

                    init() {
                        window.addEventListener('set-brand-selection', (e) => {
                            this.select(e.detail.value, e.detail.label);
                        });
                    },

                    get filteredOptions() {
                        if (!this.search) return this.options;
                        return this.options.filter(opt =>
                            opt.label.toLowerCase().includes(this.search.toLowerCase())
                        );
                    },

                    select(value, label) {
                        this.selected = value;
                        this.selectedLabel = label;
                        this.open = false;
                        this.search = '';
                    },

                    toggle() {
                        this.open = !this.open;
                        if (this.open) {
                            this.$nextTick(() => this.$refs.searchInput.focus());
                        }
                    },

                    close() {
                        this.open = false;
                    },

                    // Helper to programmatically select (for repost)
                    setSelection(value, label) {
                        this.selected = value;
                        this.selectedLabel = label;
                    }
                }));
            });

            document.addEventListener('DOMContentLoaded', function () {
                // --- Dynamic Attributes Logic ---
                // Removed ID selector as element is gone
                const attributesContainer = document.getElementById('dynamic-attributes');

                // Reusable function to fetch and render attributes
                function fetchAndRenderAttributes(categoryId) {
                    attributesContainer.innerHTML = ''; // Clear existing
                    if (!categoryId) return Promise.resolve();

                    return fetch(`/items/categories/${categoryId}/attributes`)
                        .then(response => response.json())
                        .then(attributes => {
                            attributes.forEach(attr => {
                                const attrName = attr.name || `Attribute #${attr.id}`;
                                const div = document.createElement('div');
                                div.className = 'mb-4';

                                const label = document.createElement('label');
                                label.className = 'block font-semibold mb-2';
                                label.innerText = attrName;
                                div.appendChild(label);

                                if (attr.type === 'color') {
                                    // Render color swatches
                                    const colorContainer = document.createElement('div');
                                    colorContainer.className = 'flex flex-wrap gap-2';
                                    (attr.options || []).forEach(option => {
                                        const colorStyle = option.value.toLowerCase();
                                        const labelEl = document.createElement('label');
                                        labelEl.className = 'cursor-pointer group relative';
                                        labelEl.innerHTML = `
                                                            <input type="checkbox" name="options[${attr.id}][]" value="${option.id}" class="peer sr-only color-option">
                                                            <div class="w-10 h-10 rounded-full border-2 border-gray-300 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-teal-500 shadow-sm transition" style="background-color: ${colorStyle};"></div>
                                                            <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-10 pointer-events-none">${option.value}</span>
                                                        `;
                                        colorContainer.appendChild(labelEl);
                                    });
                                    div.appendChild(colorContainer);
                                } else if (attr.type === 'radio') {
                                    // Render radio buttons
                                    const radioContainer = document.createElement('div');
                                    radioContainer.className = 'grid grid-cols-2 gap-2';
                                    (attr.options || []).forEach(option => {
                                        const labelEl = document.createElement('label');
                                        labelEl.className = 'inline-flex items-center space-x-2 p-2 border border-gray-200 rounded hover:bg-teal-50 transition cursor-pointer';
                                        labelEl.innerHTML = `
                                                            <input type="radio" name="options[${attr.id}]" value="${option.id}" class="form-radio text-teal-600 focus:ring-teal-500">
                                                            <span class="text-sm">${option.value}</span>
                                                        `;
                                        radioContainer.appendChild(labelEl);
                                    });
                                    div.appendChild(radioContainer);
                                } else {
                                    // Render select dropdown (default)
                                    if (attr.options && attr.options.length > 0) {
                                        const select = document.createElement('select');
                                        select.name = `options[${attr.id}]`;
                                        select.className = 'w-full border border-gray-300 rounded-md p-2.5 focus:ring-teal-500 focus:border-teal-500';

                                        const defaultOption = document.createElement('option');
                                        defaultOption.value = '';
                                        defaultOption.innerText = `Select ${attrName}`;
                                        select.appendChild(defaultOption);

                                        attr.options.forEach(opt => {
                                            const option = document.createElement('option');
                                            option.value = opt.id;
                                            option.innerText = opt.value;
                                            select.appendChild(option);
                                        });
                                        div.appendChild(select);
                                    }
                                }
                                attributesContainer.appendChild(div);
                            });
                            return attributes; // Return attributes for chaining
                        })
                        .catch(error => {
                            console.error('Error fetching attributes:', error);
                            return Promise.reject(error); // Propagate error
                        });
                }

                // Event listener for Livewire Category Selector
                window.addEventListener('category-selected', event => {
                    fetchAndRenderAttributes(event.detail.id);
                });

                // --- Image Upload Logic ---
                const imageInput = document.getElementById('image-input');
                const imagePreviews = document.getElementById('image-previews');
                const form = document.getElementById('item-form');
                let uploadedFiles = []; // Store File objects

                // Initialize Sortable
                new Sortable(document.getElementById('drop-zone'), {
                    animation: 150,
                    handle: '.preview-item', // Drag handle
                    draggable: '.preview-item',
                    onEnd: function (evt) {
                        // Reorder uploadedFiles array based on DOM order is handled at submit time
                        // by reading the DOM order, so we don't strictly need to update the array here
                        // unless we want to keep them in sync.
                    }
                });

                imageInput.addEventListener('change', function (e) {
                    const files = Array.from(e.target.files);
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    const maxTotalSize = 8 * 1024 * 1024; // 8MB

                    let currentTotalSize = uploadedFiles.reduce((acc, file) => acc + file.size, 0);

                    files.forEach((file, i) => {
                        if (file.size > maxSize) {
                            alert(`File "${file.name}" is too large. Maximum size is 2MB.`);
                            return;
                        }

                        if (currentTotalSize + file.size > maxTotalSize) {
                            alert(`Total upload size cannot exceed 8MB. "${file.name}" was skipped.`);
                            return;
                        }

                        // Assign a temp ID to track this file
                        file.tempId = Date.now() + i;
                        uploadedFiles.push(file);
                        currentTotalSize += file.size;

                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const div = document.createElement('div');
                            div.className = 'preview-item w-32 h-32 relative border border-gray-200 rounded overflow-hidden cursor-move group';
                            div.dataset.index = file.tempId;

                            div.innerHTML = `
                                                                                                                <img src="${e.target.result}" class="w-full h-full object-cover">
                                                                                                                <button type="button" class="absolute top-1 right-1 bg-white rounded-full p-1 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity remove-btn">
                                                                                                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                                                                </button>
                                                                                                            `;

                            // Insert before the upload button
                            document.getElementById('drop-zone').insertBefore(div, document.getElementById('upload-btn-container'));

                            // Remove handler
                            div.querySelector('.remove-btn').addEventListener('click', function () {
                                div.remove();
                                uploadedFiles = uploadedFiles.filter(f => f.tempId !== file.tempId);
                            });
                        }
                        reader.readAsDataURL(file);
                    });

                    // Reset input so same files can be selected again if needed
                    imageInput.value = '';
                });

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const formData = new FormData(form);

                    // Remove any existing 'images[]' from FormData (from the input field)
                    formData.delete('images[]');

                    // Re-append files in the correct order based on DOM
                    const previewItems = document.querySelectorAll('.preview-item');
                    previewItems.forEach(item => {
                        const tempId = parseInt(item.dataset.index);
                        const file = uploadedFiles.find(f => f.tempId === tempId);
                        if (file) {
                            formData.append('images[]', file);
                        }
                    });

                    // Submit via fetch or XHR
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Uploading...';

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => {
                            if (response.redirected) {
                                window.location.href = response.url;
                            } else {
                                return response.json().then(data => {
                                    if (data.errors) {
                                        let errorMsg = 'Validation Error:\n';
                                        for (const [key, messages] of Object.entries(data.errors)) {
                                            errorMsg += messages.join('\n') + '\n';
                                        }
                                        alert(errorMsg);
                                    } else if (data.message) {
                                        alert(data.message);
                                    } else {
                                        // Fallback if no redirect
                                        window.location.reload();
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred. Please try again.');
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerText = 'Upload';
                        });
                });

                // --- Duplicate Product Pre-fill Logic (Repost Feature) ---
                @if($duplicateProduct)
                    // Pre-select brand via event
                    window.dispatchEvent(new CustomEvent('set-brand-selection', {
                        detail: {
                            value: {{ $duplicateProduct->brand_id ?? 'null' }},
                            label: '{{ $duplicateProduct->brand->name ?? '' }}'
                        }
                    }));

                    // Pre-select condition
                    const conditionSelect = document.querySelector('select[name="condition"]');
                    if (conditionSelect) {
                        conditionSelect.value = '{{ $duplicateProduct->condition }}';
                    }

                    // Pre-select category and trigger attribute loading
                    const selectedCategoryId = {{ $duplicateProduct->category_id }};
                    if (selectedCategoryId && categorySelect) {
                        categorySelect.value = selectedCategoryId;

                        // Load attributes and then pre-select options
                        fetchAndRenderAttributes(selectedCategoryId).then(() => {
                            const selectedOptionIds = @json($duplicateProduct->options->pluck('id'));

                            // Pre-select checkboxes and radio buttons
                            selectedOptionIds.forEach(optionId => {
                                const input = document.querySelector(`input[name="options[]"][value="${optionId}"]`);
                                if (input) {
                                    input.checked = true;
                                    // Trigger visual update for color swatches
                                    if (input.classList.contains('color-option')) {
                                        input.parentElement.classList.add('ring-2', 'ring-teal-500');
                                    }
                                }

                                // Handle select dropdowns
                                document.querySelectorAll(`select[name^="options"] option[value="${optionId}"]`).forEach(opt => {
                                    opt.parentElement.value = optionId;
                                });
                            });
                        });
                    }

                    // Display duplicate product images
                    const duplicateImages = @json($duplicateProduct->getMedia('products')->map(fn($m) => $m->getUrl()));
                    const dropZone = document.getElementById('drop-zone');

                    if (duplicateImages.length > 0 && dropZone) {
                        duplicateImages.forEach((imageUrl, index) => {
                            const div = document.createElement('div');
                            div.className = 'preview-item w-32 h-32 relative border border-gray-200 rounded overflow-hidden group';
                            div.innerHTML = `
                                            <img src="${imageUrl}" class="w-full h-full object-cover">
                                            <div class="absolute top-1 right-1 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                                From original
                                            </div>
                                            <input type="hidden" name="duplicate_images[]" value="${imageUrl}">
                                        `;
                            const uploadBtnContainer = dropZone.querySelector('.upload-btn-container'); // Need class check, previously ID logic was used
                            if (uploadBtnContainer) {
                                dropZone.insertBefore(div, uploadBtnContainer);
                            } else {
                                // Fallback if regular upload btn logic changed
                                dropZone.appendChild(div);
                            }
                        });
                    }
                @endif
                    });
        </script>
    @endpush
@endsection