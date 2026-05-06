@extends('layouts.app')

@section('title', __('How to buy?'))

@section('content')
<div class="shell px-4 md:px-6 py-12 max-w-3xl mx-auto">
    <h1 class="text-4xl font-light text-center mb-12">{{ __('How to buy?') }}</h1>

    <div class="space-y-6">
        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">1.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Find your piece') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Browse our streetwear and vintage selection.') }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">2.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Check the details') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Review photos, size, condition, and description.') }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">3.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Place your order') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Add to cart and complete purchase.') }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">4.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Delivery') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Receive your order securely.') }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">5.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Confirmation') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Confirm once received.') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
