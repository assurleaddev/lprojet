@extends('layouts.app')

@section('title', 'Edit Your Item')

@section('content')
    <div class="mx-auto max-w-[800px] px-4 py-8">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Your Item</h1>

        <form action="{{ route('items.update', $product) }}" method="POST" enctype="multipart/form-data" id="item-form">
            @csrf
            @method('PUT')

            <!-- Photo Upload Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
                <div class="mb-4">
                    <span class="block font-semibold mb-1">Photos</span>
                    <span class="text-sm text-gray-500">Add up to 20 photos. <span class="text-teal-600">See
                            tips.</span></span>
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 min-h-[200px] flex flex-wrap gap-4"
                    id="drop-zone">
                    {{-- Display existing images --}}
                    @foreach($product->media as $media)
                        <div class="existing-image w-32 h-32 relative border border-gray-200 rounded overflow-hidden group">
                            <img src="{{ $media->getUrl() }}" class="w-full h-full object-cover">
                            <div class="absolute top-1 right-1 bg-white rounded-full p-1 shadow-sm">
                                <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    @endforeach

                    {{-- Upload Button --}}
                    <div class="w-32 h-32 flex items-center justify-center border border-teal-500 rounded cursor-pointer hover:bg-teal-50 transition-colors relative"
                        id="upload-btn-container">
                        <input type="file" name="images[]" id="image-input" multiple accept="image/*"
                            class="absolute inset-0 opacity-0 cursor-pointer">
                        <div class="text-center">
                            <svg class="w-8 h-8 text-teal-500 mx-auto mb-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="text-teal-500 font-semibold text-sm">Add more</span>
                        </div>
                    </div>

                    {{-- Preview Container (Sortable) --}}
                    <div id="image-previews" class="contents">
                        <!-- Images will be appended here -->
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Drag and drop to reorder. The first photo will be the main one.</p>
            </div>

            <!-- Item Details -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6 space-y-6">
                <div>
                    <label class="block font-semibold mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $product->name) }}"
                        class="w-full border border-gray-300 rounded-md p-2.5 focus:ring-teal-500 focus:border-teal-500"
                        placeholder="e.g., Zara Floral Dress" required>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Describe your item</label>
                    <textarea name="description" rows="6"
                        class="w-full border border-gray-300 rounded-md p-2.5 focus:ring-teal-500 focus:border-teal-500"
                        placeholder="e.g., brand, size, pattern, color, defects"
                        required>{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6 space-y-6">
                <div>
                    <label class="block font-semibold mb-1">Category</label>
                    <livewire:category-selector name="category_id" :value="old('category_id', $product->category_id)" />
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
                            <option value="{{ $cond }}" {{ old('condition', $product->condition) == $cond ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $cond)) }}
                            </option>
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
                        <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}"
                            class="w-full border border-gray-300 rounded-md py-2.5 pl-12 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="0.00" required>
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">MAD</span>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-teal-600 text-white font-bold py-3 rounded-md hover:bg-teal-700 transition-colors">Update
                Product</button>
        </form>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script>
            // Alpine.js Searchable Select Component
            function searchableSelect(config) {
                return {
                    open: false,
                    search: '',
                    selected: config.value || '',
                    selectedLabel: config.label || '',
                    options: config.options || [],

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
                    }
                };
            }

            // Initialize brand dropdown data with pre-selected value
            window.brandDropdown = searchableSelect({
                id: 'brand_id',
                options: @json($brands->map(fn($b) => ['value' => $b->id, 'label' => $b->name])),
                value: '{{ old('brand_id', $product->brand_id) }}',
                label: '{{ old('brand_id', $product->brand_id) ? $brands->firstWhere('id', old('brand_id', $product->brand_id))?->name : '' }}',
                placeholder: 'Select Brand'
            });

            document.addEventListener('DOMContentLoaded', function () {
                // --- Dynamic Attributes Logic ---
                const attributesContainer = document.getElementById('dynamic-attributes');

                // Event listener
                window.addEventListener('category-selected', function (event) {
                    const categoryId = event.detail.id;
                    attributesContainer.innerHTML = ''; // Clear existing


                    // One-time listener for closing dropdowns
                    if (!window.multiselectListenerAdded) {
                        document.addEventListener('click', (e) => {
                            document.querySelectorAll('.custom-multiselect-container .absolute').forEach(list => {
                                const container = list.closest('.custom-multiselect-container');
                                if (container && !container.contains(e.target)) {
                                    list.classList.add('hidden');
                                }
                            });
                        });
                        window.multiselectListenerAdded = true;
                    }

                    if (categoryId) {
                        fetch(`/items/categories/${categoryId}/attributes`)
                            .then(response => response.json())
                            .then(attributes => {
                                const selectedOptions = @json($selectedOptions ?? []);

                                attributes.forEach(attr => {
                                    const attrName = attr.name || `Attribute #${attr.id}`;
                                    const div = document.createElement('div');
                                    div.className = 'mb-4';

                                    const label = document.createElement('label');
                                    label.className = 'block font-semibold mb-2';
                                    label.innerText = attrName;
                                    div.appendChild(label);

                                    if (attr.type === 'color') {
                                        // Custom Multi-select Dropdown for Colors (Max 2)
                                        const dropdown = document.createElement('div');
                                        dropdown.className = 'relative custom-multiselect-container';

                                        // Toggle Button
                                        const btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.className = 'w-full border border-gray-300 rounded-md p-2.5 text-left bg-white flex justify-between items-center focus:ring-teal-500 focus:border-teal-500';

                                        // Dropdown List
                                        const list = document.createElement('div');
                                        list.className = 'absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto hidden';

                                        let initialLabels = [];

                                        // Options
                                        (attr.options || []).forEach(option => {
                                            const isSelected = selectedOptions.includes(option.id);
                                            if (isSelected) initialLabels.push(option.value);

                                            const row = document.createElement('label');
                                            row.className = 'flex items-center px-4 py-2 hover:bg-teal-50 cursor-pointer border-b border-gray-50 last:border-0';

                                            const checkbox = document.createElement('input');
                                            checkbox.type = 'checkbox';
                                            checkbox.name = `options[${attr.id}][]`;
                                            checkbox.value = option.id;
                                            checkbox.checked = isSelected;
                                            checkbox.className = 'w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 mr-3 color-checkbox';
                                            checkbox.dataset.label = option.value;

                                            checkbox.addEventListener('change', () => {
                                                const checked = list.querySelectorAll('input[type="checkbox"]:checked');

                                                if (checked.length > 2) {
                                                    checkbox.checked = false;
                                                    alert('You can select a maximum of 2 colors.');
                                                    return;
                                                }

                                                // Update Button Text Logic
                                                const span = btn.querySelector('span');
                                                if (checked.length > 0) {
                                                    const names = Array.from(checked).map(c => c.dataset.label);
                                                    span.innerText = names.join(', ');
                                                    span.classList.remove('text-gray-500');
                                                    span.classList.add('text-gray-900');
                                                } else {
                                                    span.innerText = `Select ${attrName} (Max 2)`;
                                                    span.classList.remove('text-gray-900');
                                                    span.classList.add('text-gray-500');
                                                }
                                            });

                                            const colorCircle = document.createElement('span');
                                            colorCircle.className = 'w-6 h-6 rounded-full border border-gray-200 mr-3';
                                            colorCircle.style.backgroundColor = option.value.toLowerCase();

                                            const text = document.createElement('span');
                                            text.className = 'text-sm text-gray-700';
                                            text.innerText = option.value;

                                            row.appendChild(checkbox);
                                            row.appendChild(colorCircle);
                                            row.appendChild(text);
                                            list.appendChild(row);
                                        });

                                        // Initial Button State
                                        if (initialLabels.length > 0) {
                                            btn.innerHTML = `<span class="truncate text-gray-900">${initialLabels.join(', ')}</span>`;
                                        } else {
                                            btn.innerHTML = `<span class="truncate text-gray-500">Select ${attrName} (Max 2)</span>`;
                                        }
                                        btn.innerHTML += `<svg class="w-4 h-4 text-gray-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;

                                        btn.addEventListener('click', () => {
                                            const isHidden = list.classList.contains('hidden');
                                            document.querySelectorAll('.custom-multiselect-container .absolute').forEach(el => el.classList.add('hidden'));
                                            if (isHidden) list.classList.remove('hidden');
                                        });

                                        dropdown.appendChild(btn);
                                        dropdown.appendChild(list);
                                        div.appendChild(dropdown);
                                    } else if (attr.type === 'radio') {
                                        // Render radio buttons
                                        const radioContainer = document.createElement('div');
                                        radioContainer.className = 'grid grid-cols-2 gap-2';
                                        (attr.options || []).forEach(option => {
                                            const isSelected = selectedOptions.includes(option.id);
                                            const labelEl = document.createElement('label');
                                            labelEl.className = 'inline-flex items-center space-x-2 p-2 border border-gray-200 rounded hover:bg-teal-50 transition cursor-pointer';
                                            labelEl.innerHTML = `
                                                                                <input type="radio" name="options[${attr.id}]" value="${option.id}" ${isSelected ? 'checked' : ''} class="form-radio text-teal-600 focus:ring-teal-500">
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
                                                option.selected = selectedOptions.includes(opt.id);
                                                select.appendChild(option);
                                            });
                                            div.appendChild(select);
                                        }
                                    }

                                    attributesContainer.appendChild(div);
                                });
                            })
                            .catch(error => console.error('Error fetching attributes:', error));
                    }
                });

                // Trigger category change on page load (via Livewire? Wait, Livewire handles this)
                // But we still need attributes if page loads with selection.
                // Livewire component does NOT emit on mount.
                // So we can manually trigger if needed, or let component dispatch "init-selection".
                // Simplest: Check input value on logic
                const catInput = document.querySelector('input[name="category_id"]');
                if (catInput && catInput.value) {
                    window.dispatchEvent(new CustomEvent('category-selected', { detail: { id: catInput.value } }));
                }


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
            });
        </script>
    @endpush
@endsection