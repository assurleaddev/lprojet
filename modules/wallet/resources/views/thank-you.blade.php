@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-16 flex flex-col items-center justify-center text-center">
        <div class="bg-green-100 p-4 rounded-full mb-6">
            <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('Thank You for Your Order!') }}</h1>
        <p class="text-lg text-gray-600 mb-8">{{ __('Your payment was successful. The vendor has been notified to prepare your package.') }}</p>

        <div class="flex gap-4">
            <a href="{{ route('chat.dashboard') }}"
                class="px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-800 transition">
                {{ __('Go to Messages') }}
            </a>
        </div>
    </div>
@endsection