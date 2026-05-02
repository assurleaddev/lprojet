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

        <button type="submit"
                class="w-full py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition-colors">
            {{ __('Create Live Room') }}
        </button>
    </form>
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
