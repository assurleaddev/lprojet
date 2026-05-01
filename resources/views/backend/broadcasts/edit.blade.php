<x-layouts.backend-layout>
    <x-slot name="title">Edit Broadcast</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Broadcast</h2>
        <a href="{{ route('admin.broadcasts.index') }}" class="btn btn-secondary">← Back</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form --}}
        <div class="lg:col-span-2">
            <x-card>
                <div class="p-6">
                    <form action="{{ route('admin.broadcasts.update', $broadcast) }}" method="POST" enctype="multipart/form-data"
                        class="space-y-5" id="broadcast-form">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pre-text <span class="text-gray-400 font-normal">(optional — appears above title)</span></label>
                            <input type="text" name="pre_text" value="{{ old('pre_text', $broadcast->pre_text) }}"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-gray-900 focus:border-gray-900 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g. New feature available">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $broadcast->title) }}"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-gray-900 focus:border-gray-900 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g. From electronics to earnings...">
                            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message Body <span class="text-red-500">*</span></label>
                            <textarea name="body" rows="5"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-gray-900 focus:border-gray-900 dark:bg-gray-700 dark:text-white"
                                placeholder="Write your message here...">{{ old('body', $broadcast->body) }}</textarea>
                            @error('body') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">After-text <span class="text-gray-400 font-normal">(optional — appears below body)</span></label>
                            <input type="text" name="after_text" value="{{ old('after_text', $broadcast->after_text) }}"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-gray-900 focus:border-gray-900 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g. Happy selling!">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image <span class="text-gray-400 font-normal">(optional)</span></label>
                            @if($broadcast->image_path)
                                <div class="mb-2 flex items-center gap-3">
                                    <img src="{{ asset('storage/' . $broadcast->image_path) }}" alt="Current image" class="h-16 rounded-md object-cover border border-gray-200">
                                    <span class="text-xs text-gray-500">Current image — upload a new one to replace it</span>
                                </div>
                            @endif
                            <input type="file" name="image" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                            @error('image') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Button Label <span class="text-gray-400 font-normal">(optional)</span></label>
                                <input type="text" name="button_label" value="{{ old('button_label', $broadcast->button_label) }}"
                                    class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-gray-900 focus:border-gray-900 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g. List now">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Button URL <span class="text-gray-400 font-normal">(optional)</span></label>
                                <input type="text" name="button_url" value="{{ old('button_url', $broadcast->button_url) }}"
                                    class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-gray-900 focus:border-gray-900 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g. /sell">
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2 border-t dark:border-gray-600">
                            <button type="submit" name="action" value="draft"
                                class="px-4 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                                Save Draft
                            </button>
                            <button type="submit" name="action" value="send"
                                onclick="return confirm('Send this broadcast to all users now?')"
                                class="px-4 py-2 text-sm font-medium rounded-md bg-gray-900 text-white hover:bg-gray-800 transition-colors">
                                Send to All Users
                            </button>
                        </div>
                    </form>
                </div>
            </x-card>
        </div>

        {{-- Preview --}}
        <div>
            <x-card>
                <div class="p-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Preview</h3>

                    <div class="border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden text-sm">
                        <div id="preview-image-wrapper" class="{{ $broadcast->image_path ? '' : 'hidden' }}">
                            <img id="preview-image"
                                src="{{ $broadcast->image_path ? asset('storage/' . $broadcast->image_path) : '' }}"
                                alt="" class="w-full h-36 object-cover">
                        </div>

                        <div class="p-4 space-y-2">
                            <div class="flex items-center gap-2">
                                <img src="{{ config('settings.platform_avatar') ?? config('settings.site_logo_lite') ?? asset('images/logo/lara-dashboard.png') }}"
                                    alt="{{ config('app.name') }}" class="w-7 h-7 rounded-full object-cover shrink-0">
                                <span class="font-semibold text-gray-900 dark:text-white text-xs">{{ config('app.name') }}</span>
                                <svg class="w-3.5 h-3.5 text-gray-900" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                            <p id="preview-pre-text" class="text-xs text-gray-500 italic {{ $broadcast->pre_text ? '' : 'hidden' }}">{{ $broadcast->pre_text }}</p>
                            <p id="preview-title" class="font-semibold text-gray-900 dark:text-white">{{ $broadcast->title ?: 'Title appears here' }}</p>
                            <p id="preview-body" class="text-gray-600 dark:text-gray-300 text-xs leading-relaxed">{{ $broadcast->body ?: 'Message body appears here...' }}</p>
                            <p id="preview-after-text" class="text-xs text-gray-500 italic {{ $broadcast->after_text ? '' : 'hidden' }}">{{ $broadcast->after_text }}</p>
                            <div id="preview-btn-wrapper" class="{{ $broadcast->button_label ? '' : 'hidden' }} pt-1">
                                <a id="preview-btn" href="{{ $broadcast->button_url ?: '#' }}"
                                    class="inline-block w-full text-center bg-gray-900 text-white text-xs font-medium py-2 px-4 rounded-lg">
                                    {{ $broadcast->button_label ?: 'Button label' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <script>
        const fields = {
            title:        { input: '[name="title"]',        preview: 'preview-title',      fallback: 'Title appears here' },
            body:         { input: '[name="body"]',         preview: 'preview-body',       fallback: 'Message body appears here...' },
            pre_text:     { input: '[name="pre_text"]',     preview: 'preview-pre-text',   fallback: null },
            after_text:   { input: '[name="after_text"]',   preview: 'preview-after-text', fallback: null },
        };

        Object.values(fields).forEach(({ input, preview, fallback }) => {
            const el = document.querySelector(input);
            if (!el) return;
            el.addEventListener('input', () => {
                const p = document.getElementById(preview);
                if (fallback) {
                    p.textContent = el.value || fallback;
                } else {
                    p.textContent = el.value;
                    p.classList.toggle('hidden', !el.value);
                }
            });
        });

        document.querySelector('[name="button_label"]').addEventListener('input', function () {
            const wrapper = document.getElementById('preview-btn-wrapper');
            document.getElementById('preview-btn').textContent = this.value;
            wrapper.classList.toggle('hidden', !this.value);
        });
        document.querySelector('[name="button_url"]').addEventListener('input', function () {
            document.getElementById('preview-btn').href = this.value || '#';
        });
        document.querySelector('[name="image"]').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('preview-image').src = e.target.result;
                document.getElementById('preview-image-wrapper').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });
    </script>
</x-layouts.backend-layout>
