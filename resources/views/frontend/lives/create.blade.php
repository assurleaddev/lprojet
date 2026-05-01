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

    <form action="{{ route('lives.store') }}" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title') }} <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="100"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                   placeholder="{{ __('e.g. Selling my vintage jacket live!') }}">
            @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional — you can set it during the live') }})</span></label>
            @if(!$products->isEmpty())
                <div class="grid grid-cols-2 gap-3" id="product-picker">
                    @foreach($products as $product)
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="product_id" value="{{ $product->id }}"
                                   class="sr-only product-radio" {{ old('product_id') == $product->id ? 'checked' : '' }}>
                            <div class="border-2 border-gray-200 rounded-lg overflow-hidden transition-all group-hover:border-gray-400 product-card">
                                <img src="{{ $product->getFeaturedImageUrl('preview') }}"
                                     class="w-full h-24 object-cover" alt="{{ $product->name }}">
                                <div class="p-2">
                                    <p class="text-xs font-semibold text-gray-900 truncate">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($product->price, 2) }} MAD</p>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('product_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Starting Bid') }} <span class="text-red-500">*</span></label>
            <div class="relative">
                <input type="number" name="starting_bid" value="{{ old('starting_bid') }}" required min="1" step="0.01"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 pr-14"
                       placeholder="50.00">
                <span class="absolute inset-y-0 right-3 flex items-center text-sm text-gray-400">MAD</span>
            </div>
            @error('starting_bid')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="w-full py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition-colors">
            {{ __('Create Live Room') }}
        </button>
    </form>
</div>

<script>
// Product picker highlight
document.querySelectorAll('.product-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.product-card').forEach(c => c.classList.remove('border-gray-900', '!border-gray-900'));
        if (radio.checked) radio.closest('label').querySelector('.product-card').classList.add('!border-gray-900');
    });
    if (radio.checked) radio.closest('label').querySelector('.product-card').classList.add('!border-gray-900');
});

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
