@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Start a Live Auction') }}</h1>

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
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product') }} <span class="text-red-500">*</span></label>
            @if($products->isEmpty())
                <p class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    {{ __('You have no approved products. List one first.') }}
                    <a href="{{ route('items.create') }}" class="text-gray-900 font-semibold hover:underline ml-1">{{ __('List a product') }}</a>
                </p>
            @else
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

        <button type="submit" {{ $products->isEmpty() ? 'disabled' : '' }}
                class="w-full py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            {{ __('Create Live Room') }}
        </button>
    </form>
</div>

<script>
document.querySelectorAll('.product-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.product-card').forEach(c => c.classList.remove('border-gray-900', '!border-gray-900'));
        if (radio.checked) radio.closest('label').querySelector('.product-card').classList.add('!border-gray-900');
    });
    if (radio.checked) radio.closest('label').querySelector('.product-card').classList.add('!border-gray-900');
});
</script>
@endsection
