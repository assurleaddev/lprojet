@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Start a Live Auction') }}</h1>

    {{-- Camera preview & permission check --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <p class="text-sm font-medium text-gray-700 mb-3">{{ __('Camera & Microphone Preview') }}</p>
        <div id="cam-preview" class="relative w-full rounded-lg overflow-hidden bg-gray-900" style="aspect-ratio:16/9">
            <div id="cam-placeholder" class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-gray-400">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.862v6.276a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <p class="text-xs" id="cam-status">{{ __('Requesting camera access…') }}</p>
            </div>
            <video id="cam-video" class="w-full h-full object-cover" autoplay muted playsinline style="display:none"></video>
        </div>
    </div>

    <form action="{{ route('lives.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf

        {{-- Thumbnail --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Thumbnail') }} <span class="text-red-500">*</span></label>
            <div id="thumb-drop"
                 class="relative flex flex-col items-center justify-center gap-3 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-gray-500 transition-colors overflow-hidden"
                 style="aspect-ratio:16/9; background:#f9fafb;">
                {{-- Preview image --}}
                <img id="thumb-preview" src="" alt="" class="absolute inset-0 w-full h-full object-cover hidden">
                {{-- Placeholder --}}
                <div id="thumb-placeholder" class="flex flex-col items-center gap-2 text-gray-400 z-10">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-600">{{ __('Click or drag a photo') }}</p>
                    <p class="text-xs text-gray-400">{{ __('JPG, PNG, WEBP — max 4 MB') }}</p>
                </div>
                {{-- Change overlay shown after selection --}}
                <div id="thumb-change" class="absolute inset-0 bg-black/40 flex items-center justify-center hidden z-20">
                    <span class="text-white text-sm font-semibold">{{ __('Change photo') }}</span>
                </div>
                <input type="file" id="thumb-input" name="thumbnail" accept="image/*" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
            </div>
            @error('thumbnail')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title') }} <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="100"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                   placeholder="{{ __('e.g. Selling my vintage jacket live!') }}">
            @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Product Selection --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Products for this Live') }} <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-gray-500 mb-3">{{ __('Select which products will be available for bidding. Set a minimum pre-bid for each.') }}</p>

            @error('product_ids')<p class="text-xs text-red-500 mb-2">{{ $message }}</p>@enderror

            @if($products->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-400">
                    {{ __('You have no approved products. Get some products approved first.') }}
                </div>
            @else
                {{-- Search --}}
                <div class="relative mb-3">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                    </svg>
                    <input type="text" id="product-search" placeholder="{{ __('Search products…') }}"
                           class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                </div>

                {{-- Selection counter --}}
                <p class="text-xs text-gray-500 mb-2">
                    <span id="selected-count">0</span> {{ __('product(s) selected') }}
                </p>

                {{-- Product grid --}}
                <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[420px] overflow-y-auto pr-1">
                    @foreach($products as $product)
                        @php $thumb = $product->images->first()?->image_url ?? null; @endphp
                        <div class="product-card relative rounded-xl border-2 border-gray-200 bg-white cursor-pointer select-none transition-all hover:border-gray-400"
                             data-product-id="{{ $product->id }}"
                             data-name="{{ strtolower($product->name) }}">

                            {{-- Checkbox hidden input --}}
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                                   class="product-checkbox sr-only"
                                   {{ collect(old('product_ids', []))->contains($product->id) ? 'checked' : '' }}>

                            {{-- Checkmark badge --}}
                            <div class="check-badge absolute top-2 right-2 w-5 h-5 rounded-full bg-gray-900 text-white items-center justify-center hidden z-10">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>

                            {{-- Thumbnail --}}
                            <div class="w-full rounded-t-[10px] overflow-hidden bg-gray-100" style="aspect-ratio:1/1">
                                @if($thumb)
                                    <img src="{{ $thumb }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="p-2">
                                <p class="text-xs font-medium text-gray-800 line-clamp-2 leading-tight">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ number_format($product->price, 2) }} MAD</p>

                                {{-- Pre-bid minimum input (shown when selected) --}}
                                <div class="pre-bid-row mt-2 hidden">
                                    <label class="block text-[10px] text-gray-500 mb-0.5">{{ __('Min pre-bid') }}</label>
                                    <div class="flex items-center gap-1">
                                        <input type="number" name="pre_bid_min[{{ $product->id }}]"
                                               min="1" step="0.01"
                                               value="{{ old('pre_bid_min.' . $product->id, 1) }}"
                                               placeholder="1.00"
                                               class="pre-bid-input w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-gray-900"
                                               onclick="event.stopPropagation()">
                                        <span class="text-[10px] text-gray-400 shrink-0">MAD</span>
                                    </div>
                                    @error('pre_bid_min.' . $product->id)
                                        <p class="text-[10px] text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <button type="submit"
                class="w-full py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition-colors">
            {{ __('Create Live Room') }}
        </button>
    </form>

    <script>
    (function () {
        const input = document.getElementById('thumb-input');
        const preview = document.getElementById('thumb-preview');
        const placeholder = document.getElementById('thumb-placeholder');
        const change = document.getElementById('thumb-change');
        const drop = document.getElementById('thumb-drop');

        function showPreview(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                change.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        input.addEventListener('change', () => showPreview(input.files[0]));

        drop.addEventListener('dragover', e => { e.preventDefault(); drop.style.borderColor = '#111'; });
        drop.addEventListener('dragleave', () => { drop.style.borderColor = ''; });
        drop.addEventListener('drop', e => {
            e.preventDefault();
            drop.style.borderColor = '';
            const file = e.dataTransfer.files[0];
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                showPreview(file);
            }
        });
    })();

    // Product selection
    (function () {
        const cards = document.querySelectorAll('.product-card');
        const countEl = document.getElementById('selected-count');
        const searchEl = document.getElementById('product-search');

        function updateCount() {
            const n = document.querySelectorAll('.product-checkbox:checked').length;
            if (countEl) countEl.textContent = n;
        }

        cards.forEach(card => {
            const cb = card.querySelector('.product-checkbox');
            const badge = card.querySelector('.check-badge');
            const preBidRow = card.querySelector('.pre-bid-row');
            const preBidInput = card.querySelector('.pre-bid-input');

            function setSelected(selected) {
                cb.checked = selected;
                if (selected) {
                    card.classList.add('border-gray-900', 'bg-gray-50');
                    card.classList.remove('border-gray-200');
                    badge.classList.remove('hidden');
                    badge.classList.add('flex');
                    preBidRow.classList.remove('hidden');
                    if (preBidInput && !preBidInput.value) preBidInput.value = '1';
                } else {
                    card.classList.remove('border-gray-900', 'bg-gray-50');
                    card.classList.add('border-gray-200');
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                    preBidRow.classList.add('hidden');
                }
                updateCount();
            }

            // Restore old() state on page load
            if (cb.checked) setSelected(true);

            card.addEventListener('click', e => {
                if (e.target.closest('.pre-bid-row')) return;
                setSelected(!cb.checked);
            });
        });

        if (searchEl) {
            searchEl.addEventListener('input', () => {
                const q = searchEl.value.toLowerCase().trim();
                cards.forEach(card => {
                    const match = !q || card.dataset.name.includes(q);
                    card.style.display = match ? '' : 'none';
                });
            });
        }
    })();
    </script>
</div>

<script>
// Request camera + mic permissions and show preview
(async function () {
    const statusEl = document.getElementById('cam-status');
    const videoEl  = document.getElementById('cam-video');
    const placeholder = document.getElementById('cam-placeholder');
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        videoEl.srcObject = stream;
        videoEl.style.display = 'block';
        placeholder.style.display = 'none';
        // Stop tracks when navigating away (form submit keeps them otherwise)
        window.addEventListener('beforeunload', () => stream.getTracks().forEach(t => t.stop()));
    } catch (err) {
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            statusEl.textContent = '{{ __('Permission denied — please allow camera & microphone in your browser settings.') }}';
        } else {
            statusEl.textContent = '{{ __('No camera found. Make sure a webcam is connected.') }}';
        }
    }
})();
</script>
@endsection
