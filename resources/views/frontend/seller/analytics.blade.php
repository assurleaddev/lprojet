@extends('layouts.app')

@section('title', __('My Analytics'))

@section('content')
<main class="shell px-4 md:px-6 py-6 max-w-6xl mx-auto flex flex-col" style="height: calc(100vh - var(--header-h, 80px)); overflow: hidden;">
    <livewire:seller-analytics />
</main>
<script>
    (function() {
        var h = document.getElementById('main-header');
        if (h) document.documentElement.style.setProperty('--header-h', h.offsetHeight + 'px');
    })();
</script>
@endsection
