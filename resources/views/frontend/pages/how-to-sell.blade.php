@extends('layouts.app')

@section('title', __('How to sell?'))

@section('content')
<div class="shell px-4 md:px-6 py-12 max-w-3xl mx-auto">
    <h1 class="text-4xl font-light text-center mb-12">{{ __('How to sell?') }}</h1>

    <div class="space-y-6">
        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">1.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Create your account') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Sign up for free.') }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">2.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('List your item') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Add photos, description, size, condition.') }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">3.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Set your price') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Choose a fair price.') }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">4.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Receive orders') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Buyers can purchase easily.') }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">5.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Ship your item') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Send via delivery partners.') }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <span class="text-2xl font-black text-gray-900 w-8 shrink-0">6.</span>
            <div>
                <h2 class="text-base font-bold mb-1">{{ __('Get paid') }}</h2>
                <p class="text-gray-600 text-sm leading-relaxed">{{ __('Receive payment after confirmation.') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
