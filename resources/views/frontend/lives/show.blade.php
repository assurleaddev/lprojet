@extends('layouts.app')

@section('before_head')
<style>
    #agora-video-container { background: #111; position: relative; }
    #agora-video-container video { width: 100%; height: 100%; object-fit: cover; }
    .bid-pulse { animation: bidPop .4s ease; }
    @keyframes bidPop { 0%{transform:scale(1)} 50%{transform:scale(1.08)} 100%{transform:scale(1)} }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('lives.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-bold text-gray-900 text-lg truncate">{{ $live->title }}</h1>
        <span id="status-badge" class="ml-auto flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full
            {{ $live->status === 'live' ? 'bg-red-100 text-red-600' : ($live->status === 'ended' ? 'bg-gray-100 text-gray-500' : 'bg-yellow-100 text-yellow-700') }}">
            @if($live->status === 'live')
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span> LIVE
            @elseif($live->status === 'ended')
                {{ __('Ended') }}
            @else
                {{ __('Upcoming') }}
            @endif
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Video --}}
        <div class="lg:col-span-2 space-y-4">
            <div id="agora-video-container" class="w-full rounded-xl overflow-hidden bg-gray-900" style="aspect-ratio:16/9">
                <div id="video-placeholder" class="w-full h-full flex flex-col items-center justify-center text-gray-500 gap-3">
                    <svg class="w-16 h-16 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.862v6.276a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm" id="video-status-text">
                        @if($live->status === 'ended') {{ __('This live has ended.') }}
                        @elseif($live->status === 'scheduled') {{ __('Waiting for seller to go live...') }}
                        @else {{ __('Connecting...') }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Seller controls --}}
            @if(auth()->id() === $live->seller_id)
                <div class="flex gap-3">
                    @if($live->status === 'scheduled')
                        <button id="btn-go-live"
                            class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2">
                            <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                            {{ __('Go Live') }}
                        </button>
                    @elseif($live->status === 'live')
                        <button id="btn-end-live"
                            class="flex-1 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-semibold rounded-lg transition-colors">
                            {{ __('End Live') }}
                        </button>
                    @endif
                </div>
            @endif

            {{-- Product info --}}
            @if($live->product)
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex gap-4 items-center">
                    <img src="{{ $live->product->getFeaturedImageUrl('preview') }}"
                         class="w-16 h-16 rounded-lg object-cover border border-gray-100" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ $live->product->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $live->product->brand?->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">{{ __('Listed at') }}</p>
                        <p class="font-bold text-gray-900">{{ number_format($live->product->price, 2) }} MAD</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Bid Panel --}}
        <div class="space-y-4">

            {{-- Current Bid --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">
                    {{ $live->current_bid ? __('Current Bid') : __('Starting Bid') }}
                </p>
                <p id="current-bid" class="text-3xl font-black text-gray-900">
                    {{ number_format($live->current_bid ?? $live->starting_bid, 2) }} MAD
                </p>
                <p id="current-bidder" class="text-xs text-gray-400 mt-1">
                    @if($live->currentBidder) {{ $live->currentBidder->username }} @endif
                </p>

                {{-- Countdown --}}
                <div id="countdown-wrap" class="{{ $live->status !== 'live' || !$live->countdown_ends_at ? 'hidden' : '' }} mt-4">
                    <div class="relative w-16 h-16 mx-auto">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                            <circle id="countdown-ring" cx="18" cy="18" r="15.9" fill="none"
                                    stroke="#ef4444" stroke-width="3" stroke-dasharray="100 100"
                                    stroke-linecap="round" style="transition:stroke-dasharray .9s linear"/>
                        </svg>
                        <span id="countdown-number" class="absolute inset-0 flex items-center justify-center text-xl font-black text-gray-900">10</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ __('seconds left') }}</p>
                </div>
            </div>

            {{-- Bid action --}}
            @if($live->status === 'live' && auth()->id() !== $live->seller_id)
                <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                    @auth
                        <div class="flex items-center gap-2">
                            <input type="number" id="bid-input" step="10" min="{{ $live->min_next_bid }}"
                                   value="{{ $live->min_next_bid }}"
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-gray-900">
                            <span class="text-sm text-gray-400 shrink-0">MAD</span>
                        </div>
                        <button id="btn-bid"
                            class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-lg transition-colors text-sm">
                            {{ __('Place Bid') }}
                        </button>
                    @else
                        <a href="{{ route('login') }}"
                           class="block w-full py-3 bg-gray-900 text-white font-bold rounded-lg text-center text-sm">
                            {{ __('Sign in to bid') }}
                        </a>
                    @endauth
                </div>
            @elseif($live->status === 'ended')
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                    <p class="text-sm font-semibold text-green-800">{{ __('Auction ended') }}</p>
                    @if($live->currentBidder)
                        <p class="text-xs text-green-600 mt-1">
                            🏆 {{ $live->currentBidder->username }} — {{ number_format($live->current_bid, 2) }} MAD
                        </p>
                    @else
                        <p class="text-xs text-green-600 mt-1">{{ __('No bids were placed.') }}</p>
                    @endif
                </div>
            @endif

            {{-- Bid history --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 px-4 py-3 border-b border-gray-100">
                    {{ __('Bid History') }}
                </p>
                <ul id="bid-history" class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                    @forelse($live->bids->sortByDesc('created_at') as $bid)
                        <li class="flex items-center justify-between px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <img src="{{ $bid->user->avatar_url }}" class="w-6 h-6 rounded-full object-cover" alt="">
                                <span class="text-sm text-gray-700">{{ $bid->user->username }}</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900">{{ number_format($bid->amount, 2) }} MAD</span>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-xs text-gray-400" id="no-bids-msg">{{ __('No bids yet. Be the first!') }}</li>
                    @endforelse
                </ul>
            </div>

            {{-- Seller info --}}
            <div class="flex items-center gap-3 bg-white rounded-xl border border-gray-200 p-4">
                <img src="{{ $live->seller->avatar_url }}" class="w-10 h-10 rounded-full object-cover border border-gray-100" alt="">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $live->seller->username }}</p>
                    <p class="text-xs text-gray-400">{{ __('Seller') }}</p>
                </div>
                <a href="{{ route('vendor.show', $live->seller) }}"
                   class="ml-auto text-xs text-gray-500 hover:text-gray-900 hover:underline">{{ __('View profile') }}</a>
            </div>
        </div>
    </div>
</div>

@section('after_body')
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.22.0.js"></script>
<script>
(function () {
    const LIVE_ID        = {{ $live->id }};
    const SELLER_ID      = {{ $live->seller_id }};
    const CURRENT_USER   = {{ auth()->id() ?? 'null' }};
    const IS_SELLER      = CURRENT_USER === SELLER_ID;
    const STATUS         = '{{ $live->status }}';
    const CSRF           = document.querySelector('meta[name="csrf-token"]').content;
    const TOKEN_URL      = '{{ route('lives.agora-token', $live) }}';
    const BID_URL        = '{{ route('lives.bid', $live) }}';
    const GO_LIVE_URL    = '{{ route('lives.go-live', $live) }}';
    const END_LIVE_URL   = '{{ route('lives.end', $live) }}';

    let client, localTracks = [];
    let countdownInterval = null;
    let countdownEndsAt   = {{ $live->countdown_ends_at ? '"' . $live->countdown_ends_at->toISOString() . '"' : 'null' }};

    // ── Agora Setup ──────────���───────────────────────────────────────────────
    async function initAgora() {
        if (STATUS === 'ended') return;
        if (STATUS === 'scheduled' && !IS_SELLER) return;

        const res   = await fetch(TOKEN_URL);
        const data  = await res.json();

        client = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
        await client.setClientRole(IS_SELLER ? 'host' : 'audience');
        await client.join(data.app_id, data.channel, data.token, data.uid);

        if (IS_SELLER) {
            localTracks = await AgoraRTC.createMicrophoneAndCameraTracks();
            document.getElementById('video-placeholder').style.display = 'none';
            const videoDiv = document.createElement('div');
            videoDiv.id = 'local-video';
            videoDiv.style.cssText = 'width:100%;height:100%';
            document.getElementById('agora-video-container').appendChild(videoDiv);
            localTracks[1].play('local-video');
            await client.publish(localTracks);
        }

        client.on('user-published', async (user, mediaType) => {
            await client.subscribe(user, mediaType);
            if (mediaType === 'video') {
                document.getElementById('video-placeholder').style.display = 'none';
                const videoDiv = document.createElement('div');
                videoDiv.id = 'remote-video';
                videoDiv.style.cssText = 'width:100%;height:100%';
                document.getElementById('agora-video-container').appendChild(videoDiv);
                user.videoTrack.play('remote-video');
            }
            if (mediaType === 'audio') user.audioTrack.play();
        });

        client.on('user-unpublished', () => {
            const el = document.getElementById('remote-video');
            if (el) el.remove();
            document.getElementById('video-placeholder').style.display = 'flex';
        });
    }

    // ── Countdown ─────────────────────────────────��─────────────────────���────
    function startCountdown(isoString) {
        countdownEndsAt = isoString;
        const wrap = document.getElementById('countdown-wrap');
        wrap.classList.remove('hidden');
        clearInterval(countdownInterval);

        countdownInterval = setInterval(() => {
            const remaining = Math.max(0, Math.round((new Date(countdownEndsAt) - Date.now()) / 1000));
            document.getElementById('countdown-number').textContent = remaining;
            const pct = (remaining / 10) * 100;
            document.getElementById('countdown-ring').setAttribute('stroke-dasharray', `${pct} 100`);
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                wrap.classList.add('hidden');
            }
        }, 500);
    }

    if (countdownEndsAt && STATUS === 'live') startCountdown(countdownEndsAt);

    // ── Pusher real-time ──────────────────────────────────────────────────────
    Echo.channel('live.' + LIVE_ID)
        .listen('BidPlaced', (e) => {
            // Update bid display
            const bidEl = document.getElementById('current-bid');
            bidEl.textContent = parseFloat(e.current_bid).toFixed(2) + ' MAD';
            bidEl.classList.add('bid-pulse');
            setTimeout(() => bidEl.classList.remove('bid-pulse'), 400);

            document.getElementById('current-bidder').textContent = e.bidder_username;

            // Update bid input minimum
            const input = document.getElementById('bid-input');
            if (input) {
                input.min   = e.min_next_bid;
                input.value = e.min_next_bid;
            }

            // Prepend to bid history
            const list = document.getElementById('bid-history');
            const noMsg = document.getElementById('no-bids-msg');
            if (noMsg) noMsg.remove();
            const li = document.createElement('li');
            li.className = 'flex items-center justify-between px-4 py-2.5 bg-yellow-50 transition-colors';
            li.innerHTML = `<span class="text-sm text-gray-700">${e.bidder_username}</span>
                            <span class="text-sm font-bold text-gray-900">${parseFloat(e.current_bid).toFixed(2)} MAD</span>`;
            list.prepend(li);
            setTimeout(() => li.classList.remove('bg-yellow-50'), 1500);

            startCountdown(e.countdown_ends_at);
        })
        .listen('LiveStatusChanged', (e) => {
            if (e.status === 'live') {
                document.getElementById('status-badge').innerHTML = `<span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse mr-1"></span> LIVE`;
                document.getElementById('status-badge').className = 'ml-auto flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-600';
                document.getElementById('video-status-text').textContent = 'Connecting...';
                if (!IS_SELLER) initAgora();
            }
            if (e.status === 'ended') {
                clearInterval(countdownInterval);
                document.getElementById('countdown-wrap')?.classList.add('hidden');
                document.getElementById('status-badge').textContent = '{{ __('Ended') }}';
                document.getElementById('status-badge').className = 'ml-auto flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500';
                if (client) client.leave();
            }
        });

    // ── Place Bid ───────────────────────────��─────────────────────────────────
    document.getElementById('btn-bid')?.addEventListener('click', async () => {
        const amount = parseFloat(document.getElementById('bid-input').value);
        const btn    = document.getElementById('btn-bid');
        btn.disabled = true;
        btn.textContent = '...';
        try {
            const res = await fetch(BID_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ amount })
            });
            if (res.status === 401) { Livewire.dispatch('open-login-popup'); return; }
            const data = await res.json();
            if (!data.ok) alert(data.message ?? 'Error placing bid');
        } catch (e) { console.error(e); }
        finally { btn.disabled = false; btn.textContent = '{{ __('Place Bid') }}'; }
    });

    // ── Seller: Go Live ───────────────────────────────────────────────────────
    document.getElementById('btn-go-live')?.addEventListener('click', async () => {
        await fetch(GO_LIVE_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
        await initAgora();
        document.getElementById('btn-go-live').remove();
    });

    // ── Seller: End Live ──────────────────────────��───────────────────────────
    document.getElementById('btn-end-live')?.addEventListener('click', async () => {
        if (!confirm('{{ __('End this live auction now?') }}')) return;
        for (const t of localTracks) t.close();
        if (client) await client.leave();
        await fetch(END_LIVE_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    });

    // Auto-init for live rooms
    if (STATUS === 'live' && CURRENT_USER) initAgora();
})();
</script>
@endsection
@endsection
