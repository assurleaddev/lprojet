@extends('layouts.app')

@section('title', __('Add an Address') . ' - ' . config('app.name'))

@section('content')
    <div class="shell py-16 px-4">
        <div class="max-w-md mx-auto text-center">

            <div class="mb-8 flex justify-center">
                <svg viewBox="0 0 220 220" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-56 h-56">
                    <!-- Background circle -->
                    <circle cx="110" cy="110" r="100" fill="var(--brand)" fill-opacity="0.08"/>

                    <!-- House body -->
                    <rect x="65" y="115" width="90" height="65" rx="4" fill="var(--brand)" fill-opacity="0.15" stroke="var(--brand)" stroke-width="2.5"/>

                    <!-- Roof -->
                    <path d="M55 118 L110 68 L165 118" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="var(--brand)" fill-opacity="0.1"/>

                    <!-- Door -->
                    <rect x="95" y="145" width="30" height="35" rx="3" fill="white" stroke="var(--brand)" stroke-width="2"/>
                    <circle cx="121" cy="163" r="2.5" fill="var(--brand)"/>

                    <!-- Window left -->
                    <rect x="72" y="128" width="22" height="20" rx="2" fill="white" stroke="var(--brand)" stroke-width="1.5"/>
                    <line x1="83" y1="128" x2="83" y2="148" stroke="var(--brand)" stroke-width="1" opacity="0.5"/>
                    <line x1="72" y1="138" x2="94" y2="138" stroke="var(--brand)" stroke-width="1" opacity="0.5"/>

                    <!-- Window right -->
                    <rect x="126" y="128" width="22" height="20" rx="2" fill="white" stroke="var(--brand)" stroke-width="1.5"/>
                    <line x1="137" y1="128" x2="137" y2="148" stroke="var(--brand)" stroke-width="1" opacity="0.5"/>
                    <line x1="126" y1="138" x2="148" y2="138" stroke="var(--brand)" stroke-width="1" opacity="0.5"/>

                    <!-- Plus badge (top-right) -->
                    <circle cx="162" cy="62" r="20" fill="var(--brand)"/>
                    <line x1="162" y1="52" x2="162" y2="72" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
                    <line x1="152" y1="62" x2="172" y2="62" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
                </svg>
            </div>

            <h1 class="text-2xl font-extrabold text-gray-900 mb-3">
                {{ __('No delivery address yet') }}
            </h1>

            <p class="text-base text-gray-500 mb-8 leading-relaxed">
                {{ __('You need to add a delivery address before you can complete your purchase.') }}
            </p>

            <a href="{{ route('settings.postage', ['redirect_to' => $checkoutUrl]) }}"
                class="inline-flex items-center justify-center gap-2 px-8 py-3 text-base font-bold rounded-xl text-white transition-all duration-200 shadow-md active:scale-95"
                style="background-color: var(--brand)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add an address') }}
            </a>
        </div>
    </div>
@endsection
