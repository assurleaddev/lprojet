@extends('layouts.app')

@section('title', __('Product Unavailable') . ' - ' . config('app.name'))

@section('content')
    <div class="shell py-16 px-4">
        <div class="max-w-md mx-auto text-center">
            <!-- Modern SVG Illustration -->
            <div class="mb-8 flex justify-center">
                <svg class="w-64 h-64 text-teal-600/20" viewBox="0 0 200 200" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="100" r="90" fill="currentColor" />
                    <path d="M70 100C70 83.4315 83.4315 70 100 70C116.569 70 130 83.4315 130 100V140H70V100Z" fill="white"
                        fill-opacity="0.9" />
                    <rect x="75" y="105" width="50" height="30" rx="4" fill="#0D9488" />
                    <path d="M100 80V90M85 85L92 92M115 85L108 92" stroke="#0D9488" stroke-width="3"
                        stroke-linecap="round" />
                    <circle cx="100" cy="120" r="4" fill="white" />
                    <path d="M140 60L160 80M160 60L140 80" stroke="#0D9488" stroke-width="4" stroke-linecap="round" />
                </svg>
            </div>

            <h1 class="text-3xl font-extrabold text-gray-900 mb-4">
                {{ __('This item is no longer available') }}
            </h1>

            <p class="text-lg text-gray-600 mb-10 leading-relaxed">
                {{ __('The product you are looking for has already been sold or is currently under review. Don\'t worry, there are plenty of other great finds waiting for you!') }}
            </p>

            <a href="{{ route('home') }}"
                class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-bold rounded-xl text-white bg-teal-600 hover:bg-teal-700 transition-all duration-200 shadow-lg shadow-teal-600/20 active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Explore Other Items') }}
            </a>
        </div>
    </div>
@endsection