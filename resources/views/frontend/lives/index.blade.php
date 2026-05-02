@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Live Auctions') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('Bid on items in real time') }}</p>
        </div>
        @auth
            <a href="{{ route('lives.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-sm font-semibold rounded-lg hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.862v6.276a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                {{ __('Go Live') }}
            </a>
        @endauth
    </div>

    @if($lives->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <svg class="w-24 h-24 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.862v6.276a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-400 font-medium">{{ __('No live auctions right now') }}</p>
            <p class="text-gray-300 text-sm mt-1">{{ __('Check back soon or start your own!') }}</p>
        </div>
    @else
        <div id="lives-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($lives as $live)
                <a href="{{ route('lives.show', $live) }}" data-live-id="{{ $live->id }}" class="group block bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative aspect-video bg-gray-100">
                        @if($live->product)
                            <img src="{{ $live->product->getFeaturedImageUrl('preview') }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.862v6.276a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        {{-- Status badge --}}
                        @if($live->status === 'live')
                            <span data-badge class="absolute top-2 left-2 flex items-center gap-1 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                                LIVE
                            </span>
                        @else
                            <span data-badge class="absolute top-2 left-2 bg-gray-800/70 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                {{ __('Upcoming') }}
                            </span>
                        @endif
                    </div>

                    <div class="p-4">
                        <p class="font-semibold text-gray-900 truncate">{{ $live->title }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <img src="{{ $live->seller->avatar_url }}" class="w-5 h-5 rounded-full object-cover" alt="">
                            <span class="text-xs text-gray-500">{{ $live->seller->username }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-xs text-gray-400">{{ __('Starting at') }}</span>
                            <span class="font-bold text-gray-900 text-sm">{{ number_format($live->starting_bid, 2) }} MAD</span>
                        </div>
                        @if($live->current_bid)
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-400">{{ __('Current bid') }}</span>
                                <span class="font-bold text-brand text-sm">{{ number_format($live->current_bid, 2) }} MAD</span>
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection

@section('after_body')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-live-id]').forEach(function (card) {
        const id = card.dataset.liveId;
        Echo.channel('live.' + id).listen('LiveStatusChanged', function (e) {
            if (e.status === 'ended') {
                card.style.transition = 'opacity .4s ease, transform .4s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(.95)';
                setTimeout(function () {
                    card.remove();
                    const grid = document.getElementById('lives-grid');
                    if (grid && grid.children.length === 0) location.reload();
                }, 400);
            }
            if (e.status === 'live') {
                const badge = card.querySelector('[data-badge]');
                if (badge) {
                    badge.className = 'absolute top-2 left-2 flex items-center gap-1 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full';
                    badge.innerHTML = '<span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span> LIVE';
                }
            }
        });
    });
});
</script>
@endsection
