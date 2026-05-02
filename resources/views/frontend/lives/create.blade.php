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
