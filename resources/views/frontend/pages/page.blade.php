@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="shell px-4 md:px-6 py-12 max-w-3xl mx-auto">
    <h1 class="text-3xl font-semibold text-gray-900 mb-8">{{ $page->title }}</h1>

    @if($page->excerpt)
        <p class="text-gray-500 text-base mb-6 leading-relaxed">{{ $page->excerpt }}</p>
    @endif

    <div class="prose prose-gray max-w-none text-sm leading-relaxed">
        {!! $page->content !!}
    </div>
</div>
@endsection
