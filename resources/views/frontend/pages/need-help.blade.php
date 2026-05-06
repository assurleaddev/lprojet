@extends('layouts.app')

@section('title', __('Need help?'))

@section('content')
<div class="shell px-4 md:px-6 py-12 max-w-3xl mx-auto text-center">
    <h1 class="text-4xl font-light text-center mb-8">{{ __('Need help?') }}</h1>

    <p class="text-gray-600 text-sm leading-relaxed mb-2">{{ __('Our team is here to support you at every step.') }}</p>
    <p class="text-gray-600 text-sm leading-relaxed">{{ __('Contact us anytime if you need assistance.') }}</p>
</div>
@endsection
