@extends('layouts.app')

@section('before_head')
<style>
/* ── Full-screen feed — break out of the app layout ── */
#main-header { display: none !important; }
body > main { padding: 0 !important; margin: 0 !important; }
html, body { height: 100%; margin: 0; overflow: hidden; }

#live-feed {
    position: fixed;
    inset: 0;
    overflow-y: scroll;
    scroll-snap-type: y mandatory;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    z-index: 50;
}
#live-feed::-webkit-scrollbar { display: none; }

/* ── Individual live screen ── */
.live-screen {
    position: relative;
    height: 100dvh;
    width: 100%;
    scroll-snap-align: start;
    scroll-snap-stop: always;
    background: #000;
    overflow: hidden;
}
.live-video-wrap {
    position: absolute; inset: 0;
    background: #111;
}
.live-video-wrap video { width: 100%; height: 100%; object-fit: cover; }
.live-screen::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.7) 0%, transparent 40%, rgba(0,0,0,.2) 100%);
    pointer-events: none;
    z-index: 1;
}

/* ── Sidebar ── */
.live-sidebar {
    position: absolute; right: 14px; bottom: 140px; z-index: 10;
    display: flex; flex-direction: column; align-items: center; gap: 20px;
}
.side-btn { display: flex; flex-direction: column; align-items: center; gap: 4px; color: #fff; cursor: pointer; user-select: none; }
.side-btn svg { filter: drop-shadow(0 1px 2px rgba(0,0,0,.5)); }
.side-btn span { font-size: 11px; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,.5); }

/* ── Product card ── */
.live-product {
    position: absolute; left: 12px; bottom: 200px; z-index: 10; width: 200px;
}
.product-card-live {
    display: flex; align-items: center; gap: 8px;
    background: rgba(0,0,0,.55); backdrop-filter: blur(6px);
    border-radius: 12px; padding: 8px; cursor: pointer;
}
.product-card-live img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.prod-name { color: #fff; font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.prod-bid  { color: #fbbf24; font-size: 11px; font-weight: 700; }

/* ── Bid bar ── */
.bid-bar {
    position: absolute; bottom: 130px; left: 12px; right: 12px; z-index: 10;
    display: flex; gap: 8px; align-items: center;
}
.bid-bar input {
    flex: 1; background: rgba(255,255,255,.15); backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.3); border-radius: 24px;
    padding: 10px 16px; color: #fff; font-size: 14px; font-weight: 600; outline: none;
}
.bid-bar input::placeholder { color: rgba(255,255,255,.55); }
.bid-bar button {
    background: #ef4444; color: #fff; border: none; border-radius: 24px;
    padding: 10px 20px; font-size: 14px; font-weight: 700; cursor: pointer; white-space: nowrap;
}
.bid-bar button:disabled { opacity: .5; cursor: not-allowed; }

/* ── Countdown ── */
.countdown-bar {
    position: absolute; bottom: 120px; left: 12px; right: 80px; z-index: 10; text-align: center;
}
.countdown-inner {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(0,0,0,.55); backdrop-filter: blur(6px);
    border-radius: 20px; padding: 6px 16px; color: #fbbf24; font-size: 13px; font-weight: 700;
}

/* ── Comments ── */
.live-comments {
    position: absolute; bottom: 185px; left: 12px; right: 80px; z-index: 10;
    max-height: 210px; overflow-y: auto;
    mask-image: linear-gradient(transparent 0%, black 30%);
    scrollbar-width: none; pointer-events: none;
}
.live-comments::-webkit-scrollbar { display: none; }
.comment-item { display: flex; align-items: flex-start; gap: 6px; margin-bottom: 6px; pointer-events: auto; animation: slideIn .25s ease; }
@keyframes slideIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
.comment-item img { width: 22px; height: 22px; border-radius: 50%; object-fit: cover; flex-shrink: 0; margin-top: 2px; }
.comment-bubble { background: rgba(0,0,0,.45); backdrop-filter: blur(4px); border-radius: 12px; padding: 5px 10px; color: #fff; font-size: 12px; line-height: 1.4; }
.comment-bubble .c-user { font-weight: 700; margin-right: 4px; color: #fde68a; }

/* ── Comment input ── */
.comment-input-wrap {
    position: absolute; bottom: 70px; left: 12px; right: 80px; z-index: 10;
    display: flex; gap: 8px;
}
.comment-input-wrap input {
    flex: 1; background: rgba(255,255,255,.12); backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.25); border-radius: 24px;
    padding: 9px 14px; color: #fff; font-size: 13px; outline: none;
}
.comment-input-wrap input::placeholder { color: rgba(255,255,255,.5); }
.comment-input-wrap button {
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
    border-radius: 50%; width: 38px; height: 38px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; cursor: pointer; flex-shrink: 0;
}

/* ── Seller bar ── */
.seller-bar {
    position: absolute; top: 16px; left: 12px; right: 12px; z-index: 10;
    display: flex; align-items: center; justify-content: space-between;
}
.seller-info { display: flex; align-items: center; gap: 8px; }
.seller-info img { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #fff; object-fit: cover; }
.seller-name { color: #fff; font-size: 14px; font-weight: 700; text-shadow: 0 1px 3px rgba(0,0,0,.5); }
.live-badge { display: flex; align-items: center; gap: 4px; background: #ef4444; color: #fff; font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 6px; }
.live-badge .dot { width: 6px; height: 6px; background: #fff; border-radius: 50%; animation: pulse 1.2s infinite; }
@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.3; } }
.ctrl-btn { background: rgba(0,0,0,.5); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,.2); border-radius: 20px; color: #fff; font-size: 12px; font-weight: 600; padding: 6px 14px; cursor: pointer; white-space: nowrap; }
.ctrl-btn.danger { background: rgba(220,38,38,.7); }
.ctrl-btn.go-live-btn { background: rgba(22,163,74,.8); }
.ctrl-btn:disabled { opacity: .5; cursor: not-allowed; }

/* ── Winner overlay ── */
.winner-overlay { position: absolute; inset: 0; z-index: 30; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(0,0,0,.7); animation: fadeIn .3s ease; }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
.winner-trophy { font-size: 64px; animation: bounce .6s ease infinite alternate; }
@keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-12px); } }
.winner-text { color: #fff; font-size: 22px; font-weight: 800; text-align: center; margin-top: 12px; }
.winner-sub  { color: #fbbf24; font-size: 16px; font-weight: 700; margin-top: 4px; }

/* ── Floating hearts ── */
.heart-float { position: absolute; bottom: 80px; right: 60px; z-index: 20; pointer-events: none; overflow: hidden; width: 50px; height: 200px; }
.heart { position: absolute; font-size: 22px; animation: floatHeart 1.8s ease-out forwards; }
@keyframes floatHeart { 0% { opacity:1; transform:translateY(0) scale(.8); } 50% { opacity:1; transform:translateY(-80px) scale(1.1); } 100% { opacity:0; transform:translateY(-180px) scale(.6); } }

/* ── Product sheet ── */
.product-sheet { position: absolute; bottom: 0; left: 0; right: 0; z-index: 40; background: #111; border-radius: 20px 20px 0 0; padding: 20px 16px; transform: translateY(100%); transition: transform .3s ease; max-height: 60dvh; overflow-y: auto; }
.product-sheet.open { transform: translateY(0); }
.sheet-handle { width: 36px; height: 4px; background: rgba(255,255,255,.25); border-radius: 2px; margin: 0 auto 16px; }

/* ── Unmute button ── */
.unmute-btn { position: absolute; inset: 0; z-index: 20; display: flex; align-items: center; justify-content: center; }
.unmute-inner { background: rgba(0,0,0,.6); border: 2px solid rgba(255,255,255,.4); border-radius: 50px; color: #fff; padding: 12px 24px; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; }
</style>
@endsection

@section('content')
<div id="live-feed">
    @foreach($orderedLives as $liveItem)
    @php
        $isSeller = Auth::id() === $liveItem->seller_id;
        $isFirst  = $loop->first;
    @endphp
    <div class="live-screen"
         data-live-id="{{ $liveItem->id }}"
         data-status="{{ $liveItem->status }}"
         data-auction="{{ $liveItem->auction_status }}"
         data-is-seller="{{ $isSeller ? '1' : '0' }}"
         data-token-url="{{ route('lives.agora-token', $liveItem) }}"
         data-bid-url="{{ route('lives.bid', $liveItem) }}"
         data-comment-url="{{ route('lives.comment', $liveItem) }}"
         data-like-url="{{ route('lives.like', $liveItem) }}"
         data-go-live-url="{{ $isSeller ? route('lives.go-live', $liveItem) : '' }}"
         data-end-live-url="{{ $isSeller ? route('lives.end', $liveItem) : '' }}"
         data-close-auction-url="{{ $isSeller ? route('lives.close-auction', $liveItem) : '' }}"
         data-set-product-url="{{ $isSeller ? route('lives.set-product', $liveItem) : '' }}"
         data-min-next-bid="{{ $liveItem->min_next_bid }}"
         data-countdown="{{ $liveItem->countdown_ends_at ? $liveItem->countdown_ends_at->toISOString() : '' }}"
         data-likes="{{ $liveItem->likes_count }}"
         data-has-liked="{{ ($isFirst && $hasLiked) ? '1' : '0' }}">

        {{-- Video area --}}
        <div class="live-video-wrap" id="video-wrap-{{ $liveItem->id }}"></div>

        {{-- Seller bar --}}
        <div class="seller-bar">
            <div class="seller-info">
                <img src="{{ $liveItem->seller->avatar_url }}" alt="">
                <span class="seller-name">{{ $liveItem->seller->username }}</span>
                @if($liveItem->status === 'live')
                    <span class="live-badge"><span class="dot"></span> LIVE</span>
                @else
                    <span style="background:rgba(0,0,0,.5);color:#fff;font-size:11px;font-weight:600;padding:3px 8px;border-radius:6px;">{{ __('Upcoming') }}</span>
                @endif
            </div>
            @if($isSeller)
            <div style="display:flex;gap:6px;align-items:center;">
                @if($liveItem->status === 'scheduled')
                    <button class="ctrl-btn go-live-btn js-go-live">{{ __('Go Live') }}</button>
                @endif
                @if($liveItem->status === 'live')
                    @if($liveItem->auction_status === 'active')
                        <button class="ctrl-btn js-close-auction">{{ __('Close Bid') }}</button>
                    @endif
                    <button class="ctrl-btn js-open-product-sheet">🛍 {{ __('Set Product') }}</button>
                    <button class="ctrl-btn danger js-end-live">{{ __('End') }}</button>
                @endif
            </div>
            @endif
        </div>

        {{-- Product card --}}
        <div class="live-product js-product-card" style="{{ $liveItem->auction_status !== 'active' ? 'display:none;' : '' }}">
            <div class="product-card-live">
                @if($liveItem->product)
                    <img src="{{ $liveItem->product->getFeaturedImageUrl('preview') }}" alt="" class="js-prod-img">
                @else
                    <img src="" alt="" class="js-prod-img" style="display:none;">
                @endif
                <div style="min-width:0;overflow:hidden;">
                    <div class="prod-name js-prod-name">{{ $liveItem->product?->name ?? '' }}</div>
                    <div class="prod-bid js-bid-display">
                        @if($liveItem->current_bid)
                            {{ number_format($liveItem->current_bid, 2) }} MAD
                        @elseif($liveItem->product)
                            {{ __('Start') }}: {{ number_format($liveItem->starting_bid, 2) }} MAD
                        @endif
                    </div>
                    <div style="color:rgba(255,255,255,.7);font-size:10px;" class="js-bidder-name">{{ $liveItem->currentBidder ? 'by ' . $liveItem->currentBidder->username : '' }}</div>
                </div>
            </div>
        </div>

        {{-- Countdown --}}
        <div class="countdown-bar js-countdown-bar" style="{{ !$liveItem->countdown_ends_at ? 'display:none;' : '' }}">
            <div class="countdown-inner">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="js-countdown-text">{{ __('Closing in') }} ...</span>
            </div>
        </div>

        {{-- Bid bar --}}
        @auth
        <div class="bid-bar js-bid-bar" style="{{ ($liveItem->auction_status !== 'active' || $isSeller) ? 'display:none;' : '' }}">
            <input type="number" class="js-bid-input" step="10" min="{{ $liveItem->min_next_bid }}"
                   placeholder="{{ __('Min') }} {{ number_format($liveItem->min_next_bid, 2) }} MAD">
            <button class="js-bid-btn">{{ __('Bid') }}</button>
        </div>
        @endauth

        {{-- Comments --}}
        <div class="live-comments js-comments">
            @if($isFirst)
                @foreach($recentComments as $comment)
                <div class="comment-item">
                    <img src="{{ $comment->user->avatar_url }}" alt="">
                    <div class="comment-bubble"><span class="c-user">{{ $comment->user->username }}</span>{{ $comment->content }}</div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Comment input --}}
        @auth
        <div class="comment-input-wrap">
            <input type="text" class="js-comment-input" maxlength="200" placeholder="{{ __('Say something…') }}">
            <button class="js-comment-send">
                <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </div>
        @endauth

        {{-- Sidebar --}}
        <div class="live-sidebar">
            @auth
            <div class="side-btn js-like-btn">
                <svg style="width:28px;height:28px;" class="js-heart-icon" fill="{{ ($isFirst && $hasLiked) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/>
                </svg>
                <span class="js-likes-count">{{ $liveItem->likes_count }}</span>
            </div>
            @endauth
            <div class="side-btn js-share-btn">
                <svg style="width:28px;height:28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span>{{ __('Share') }}</span>
            </div>
        </div>

        {{-- Floating hearts --}}
        <div class="heart-float js-heart-float"></div>

        {{-- Unmute --}}
        <div class="unmute-btn js-unmute" style="display:none;">
            <div class="unmute-inner">
                <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                {{ __('Tap to unmute') }}
            </div>
        </div>

        {{-- Winner overlay --}}
        <div class="winner-overlay js-winner" style="display:none;">
            <div class="winner-trophy">🏆</div>
            <div class="winner-text js-winner-text"></div>
            <div class="winner-sub js-winner-sub"></div>
        </div>

        {{-- Product sheet (seller only) --}}
        @if($isSeller && $isFirst)
        <div class="product-sheet js-product-sheet">
            <div class="sheet-handle"></div>
            <p style="color:#fff;font-size:15px;font-weight:700;margin-bottom:12px;">{{ __('Select Product for Auction') }}</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                @foreach($sellerProducts as $sp)
                <label style="cursor:pointer;">
                    <input type="radio" name="sheet-product-{{ $liveItem->id }}" value="{{ $sp->id }}"
                           data-name="{{ $sp->name }}" data-price="{{ $sp->price }}"
                           data-img="{{ $sp->getFeaturedImageUrl('preview') }}"
                           class="js-sheet-radio" style="display:none;">
                    <div class="js-sheet-card" style="border:2px solid rgba(255,255,255,.15);border-radius:10px;overflow:hidden;transition:border-color .15s;">
                        <img src="{{ $sp->getFeaturedImageUrl('preview') }}" style="width:100%;height:70px;object-fit:cover;" alt="">
                        <div style="padding:6px 8px;">
                            <p style="color:#fff;font-size:11px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $sp->name }}</p>
                            <p style="color:rgba(255,255,255,.5);font-size:10px;">{{ number_format($sp->price, 2) }} MAD</p>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            <div style="margin-top:14px;display:flex;gap:8px;align-items:center;">
                <input type="number" class="js-sheet-bid" min="1" step="1" placeholder="{{ __('Starting bid (MAD)') }}"
                       style="flex:1;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.25);border-radius:20px;padding:9px 14px;color:#fff;font-size:13px;outline:none;">
                <button class="js-sheet-confirm" style="background:#ef4444;color:#fff;border:none;border-radius:20px;padding:10px 20px;font-weight:700;font-size:13px;cursor:pointer;white-space:nowrap;">
                    {{ __('Start Auction') }}
                </button>
            </div>
            <button class="js-sheet-close" style="margin-top:10px;width:100%;background:rgba(255,255,255,.08);border:none;border-radius:20px;padding:10px;color:#fff;font-size:13px;cursor:pointer;">
                {{ __('Cancel') }}
            </button>
        </div>
        @endif

    </div>
    @endforeach
</div>
@endsection

@section('after_body')
<script>
document.addEventListener('DOMContentLoaded', function () {
    (function () {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const IS_AUTH = {!! Auth::check() ? 'true' : 'false' !!};

        async function apiFetch(url, body) {
            const r = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            return r.json();
        }

        const screenState = new Map();
        function getState(id) {
            if (!screenState.has(id)) screenState.set(id, { client: null, localTracks: [], countdownTimer: null, auctionEnding: false });
            return screenState.get(id);
        }

        function escHtml(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // ── Countdown ────────────────────────────────────────────────
        function startCountdown(screen, endsAtIso) {
            const id = screen.dataset.liveId;
            const st = getState(id);
            const bar = screen.querySelector('.js-countdown-bar');
            const txt = screen.querySelector('.js-countdown-text');
            const isSeller = screen.dataset.isSeller === '1';
            clearInterval(st.countdownTimer);
            if (!endsAtIso) { bar.style.display = 'none'; return; }
            bar.style.display = '';
            st.countdownTimer = setInterval(() => {
                const remaining = Math.round((new Date(endsAtIso) - Date.now()) / 1000);
                if (remaining <= 0) {
                    clearInterval(st.countdownTimer);
                    bar.style.display = 'none';
                    if (isSeller && !st.auctionEnding) {
                        st.auctionEnding = true;
                        triggerCloseAuction(screen);
                    }
                    return;
                }
                txt.textContent = {!! json_encode(__('Closing in')) !!} + ' ' + remaining + 's';
            }, 500);
        }

        // ── Winner overlay ───────────────────────────────────────────
        function showWinner(screen, username, amount) {
            const ov = screen.querySelector('.js-winner');
            screen.querySelector('.js-winner-text').textContent = username
                ? '🎉 ' + username + ' ' + {!! json_encode(__('won the auction!')) !!}
                : {!! json_encode(__('Auction closed — no bids.')) !!};
            screen.querySelector('.js-winner-sub').textContent = amount ? amount + ' MAD' : '';
            ov.style.display = 'flex';
            setTimeout(() => { ov.style.display = 'none'; }, 10000);
        }

        // ── Append comment ───────────────────────────────────────────
        function appendComment(screen, c) {
            const box = screen.querySelector('.js-comments');
            const div = document.createElement('div');
            div.className = 'comment-item';
            div.innerHTML = '<img src="' + escHtml(c.avatar_url) + '" alt=""><div class="comment-bubble"><span class="c-user">' + escHtml(c.username) + '</span>' + escHtml(c.content) + '</div>';
            box.appendChild(div);
            box.scrollTop = box.scrollHeight;
            while (box.children.length > 80) box.removeChild(box.firstChild);
        }

        // ── Floating hearts ──────────────────────────────────────────
        function spawnHeart(screen) {
            const container = screen.querySelector('.js-heart-float');
            const hearts = ['❤️', '🧡', '💛', '💚', '💙', '💜'];
            const h = document.createElement('span');
            h.className = 'heart';
            h.textContent = hearts[Math.floor(Math.random() * hearts.length)];
            h.style.left = (Math.random() * 20) + 'px';
            container.appendChild(h);
            setTimeout(() => h.remove(), 1900);
        }

        // ── Pusher bindings ──────────────────────────────────────────
        function bindPusherChannel(screen) {
            const id = screen.dataset.liveId;
            const ch = Echo.channel('live.' + id);

            ch.listen('BidPlaced', (e) => {
                const bidDisplay = screen.querySelector('.js-bid-display');
                const bidderName = screen.querySelector('.js-bidder-name');
                const bidInput = screen.querySelector('.js-bid-input');
                if (bidDisplay) bidDisplay.textContent = parseFloat(e.current_bid).toFixed(2) + ' MAD';
                if (bidderName) bidderName.textContent = 'by ' + (e.bidder_username || '');
                if (bidInput) {
                    bidInput.min = e.min_next_bid;
                    bidInput.placeholder = {!! json_encode(__('Min')) !!} + ' ' + parseFloat(e.min_next_bid).toFixed(2) + ' MAD';
                }
                startCountdown(screen, e.countdown_ends_at);
            });

            ch.listen('AuctionProductChanged', (e) => {
                const pc = screen.querySelector('.js-product-card');
                const pn = screen.querySelector('.js-prod-name');
                const pi = screen.querySelector('.js-prod-img');
                const bd = screen.querySelector('.js-bid-display');
                const bn = screen.querySelector('.js-bidder-name');
                const bidBar = screen.querySelector('.js-bid-bar');
                const isSeller = screen.dataset.isSeller === '1';
                if (pn) pn.textContent = e.product_name || '';
                if (pi && e.product_image) { pi.src = e.product_image; pi.style.display = ''; }
                if (bd) bd.textContent = {!! json_encode(__('Start')) !!} + ': ' + parseFloat(e.starting_bid).toFixed(2) + ' MAD';
                if (bn) bn.textContent = '';
                if (pc) pc.style.display = '';
                if (bidBar && !isSeller) bidBar.style.display = '';
                screen.dataset.auction = 'active';
                screen.querySelector('.js-countdown-bar').style.display = 'none';
                clearInterval(getState(id).countdownTimer);
            });

            ch.listen('AuctionClosed', (e) => {
                const bidBar = screen.querySelector('.js-bid-bar');
                const pc = screen.querySelector('.js-product-card');
                if (bidBar) bidBar.style.display = 'none';
                if (pc) pc.style.display = 'none';
                screen.dataset.auction = 'idle';
                const bar = screen.querySelector('.js-countdown-bar');
                if (bar) bar.style.display = 'none';
                clearInterval(getState(id).countdownTimer);
                getState(id).auctionEnding = false;
                showWinner(screen, e.winner_username, e.winning_bid ? parseFloat(e.winning_bid).toFixed(2) : null);
            });

            ch.listen('CommentPosted', (e) => appendComment(screen, e));

            ch.listen('LiveStatusChanged', (e) => {
                if (e.status === 'ended') {
                    screen.remove();
                    const feed = document.getElementById('live-feed');
                    if (feed && feed.children.length === 0) window.location.href = {!! json_encode(route('lives.index')) !!};
                }
            });
        }

        // ── Agora ────────────────────────────────────────────────────
        async function initAgora(screen) {
            const id = screen.dataset.liveId;
            const st = getState(id);
            if (st.client) return;

            const isSeller = screen.dataset.isSeller === '1';
            const wrap = document.getElementById('video-wrap-' + id);

            let tokenData;
            try {
                const r = await fetch(screen.dataset.tokenUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
                tokenData = await r.json();
            } catch (e) { return; }

            const client = AgoraRTC.createClient({ mode: 'live', codec: 'vp8' });
            st.client = client;

            AgoraRTC.onAudioAutoplayFailed = () => {
                const btn = screen.querySelector('.js-unmute');
                if (btn) btn.style.display = 'flex';
            };

            try {
                if (isSeller) {
                    await client.setClientRole('host');
                    await client.join(tokenData.app_id, tokenData.channel, tokenData.token, tokenData.uid);
                    const [audioTrack, videoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
                    st.localTracks = [audioTrack, videoTrack];
                    videoTrack.play(wrap);
                    if (screen.dataset.status === 'live') {
                        await client.publish([audioTrack, videoTrack]);
                    }
                } else {
                    await client.setClientRole('audience');
                    await client.join(tokenData.app_id, tokenData.channel, tokenData.token, tokenData.uid);
                }
            } catch (e) { console.error('Agora join error', e); return; }

            client.on('user-published', async (user, mediaType) => {
                await client.subscribe(user, mediaType);
                if (mediaType === 'video') {
                    const div = document.createElement('div');
                    div.id = 'agora-remote-' + user.uid;
                    div.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;';
                    wrap.innerHTML = '';
                    wrap.appendChild(div);
                    user.videoTrack.play(div);
                }
                if (mediaType === 'audio') {
                    try { user.audioTrack.play(); }
                    catch (e) {
                        const btn = screen.querySelector('.js-unmute');
                        if (btn) btn.style.display = 'flex';
                    }
                }
            });

            client.on('user-unpublished', (user, mediaType) => {
                if (mediaType === 'video') {
                    const div = document.getElementById('agora-remote-' + user.uid);
                    if (div) div.remove();
                }
            });
        }

        async function teardownAgora(screen) {
            const id = screen.dataset.liveId;
            const st = getState(id);
            if (!st.client) return;
            st.localTracks.forEach(t => { try { t.stop(); t.close(); } catch (e) {} });
            try { await st.client.leave(); } catch (e) {}
            st.client = null;
            st.localTracks = [];
        }

        // ── Seller actions ───────────────────────────────────────────
        async function triggerCloseAuction(screen) {
            const url = screen.dataset.closeAuctionUrl;
            if (!url) return;
            try { await apiFetch(url, {}); } catch (e) {}
        }

        async function triggerGoLive(screen) {
            const url = screen.dataset.goLiveUrl;
            if (!url) return;
            await apiFetch(url, {});
            screen.dataset.status = 'live';
            const st = getState(screen.dataset.liveId);
            if (st.client && st.localTracks.length) {
                try { await st.client.publish(st.localTracks); } catch (e) {}
            }
        }

        // ── Bind screen interactions ─────────────────────────────────
        function bindScreen(screen) {
            bindPusherChannel(screen);

            const cdIso = screen.dataset.countdown;
            if (cdIso && Date.now() < new Date(cdIso)) startCountdown(screen, cdIso);

            // Go Live
            const goBtn = screen.querySelector('.js-go-live');
            if (goBtn) {
                goBtn.addEventListener('click', async () => {
                    goBtn.disabled = true;
                    await triggerGoLive(screen);
                    goBtn.style.display = 'none';
                });
            }

            // End Live
            const endBtn = screen.querySelector('.js-end-live');
            if (endBtn) {
                endBtn.addEventListener('click', async () => {
                    if (!confirm({!! json_encode(__('End this live auction now?')) !!})) return;
                    endBtn.disabled = true;
                    try {
                        await apiFetch(screen.dataset.endLiveUrl, {});
                        await teardownAgora(screen);
                        window.location.href = {!! json_encode(route('lives.index')) !!};
                    } catch (e) { endBtn.disabled = false; }
                });
            }

            // Close auction
            const closeAuctionBtn = screen.querySelector('.js-close-auction');
            if (closeAuctionBtn) {
                closeAuctionBtn.addEventListener('click', async () => {
                    closeAuctionBtn.disabled = true;
                    await triggerCloseAuction(screen);
                    closeAuctionBtn.disabled = false;
                });
            }

            // Like
            const likeBtn = screen.querySelector('.js-like-btn');
            if (likeBtn && IS_AUTH) {
                likeBtn.addEventListener('click', async () => {
                    spawnHeart(screen);
                    const res = await apiFetch(screen.dataset.likeUrl, {});
                    if (res.ok !== undefined) {
                        const icon = likeBtn.querySelector('.js-heart-icon');
                        icon.setAttribute('fill', res.liked ? 'currentColor' : 'none');
                        likeBtn.querySelector('.js-likes-count').textContent = res.likes_count;
                    }
                });
            }

            // Share
            const shareBtn = screen.querySelector('.js-share-btn');
            if (shareBtn) {
                shareBtn.addEventListener('click', () => {
                    const url = window.location.origin + '/lives/' + screen.dataset.liveId;
                    if (navigator.share) navigator.share({ url }).catch(() => {});
                    else { navigator.clipboard?.writeText(url); alert({!! json_encode(__('Link copied!')) !!}); }
                });
            }

            // Bid
            const bidBtn = screen.querySelector('.js-bid-btn');
            const bidInput = screen.querySelector('.js-bid-input');
            if (bidBtn && IS_AUTH) {
                bidBtn.addEventListener('click', async () => {
                    const amount = parseFloat(bidInput.value);
                    if (!amount) return;
                    bidBtn.disabled = true;
                    try {
                        const res = await apiFetch(screen.dataset.bidUrl, { amount });
                        if (!res.ok) alert(res.message || 'Error');
                        else bidInput.value = '';
                    } catch (e) {}
                    bidBtn.disabled = false;
                });
                bidInput.addEventListener('keydown', e => { if (e.key === 'Enter') bidBtn.click(); });
            }

            // Comment
            const commentInput = screen.querySelector('.js-comment-input');
            const commentSend = screen.querySelector('.js-comment-send');
            if (commentSend && IS_AUTH) {
                const sendComment = async () => {
                    const content = commentInput.value.trim();
                    if (!content) return;
                    commentInput.value = '';
                    appendComment(screen, {
                        avatar_url: {!! json_encode(Auth::user()?->avatar_url ?? '') !!},
                        username: {!! json_encode(Auth::user()?->username ?? '') !!},
                        content,
                    });
                    apiFetch(screen.dataset.commentUrl, { content }).catch(() => {});
                };
                commentSend.addEventListener('click', sendComment);
                commentInput.addEventListener('keydown', e => { if (e.key === 'Enter') sendComment(); });
            }

            // Unmute
            const unmuteBtn = screen.querySelector('.js-unmute');
            if (unmuteBtn) {
                unmuteBtn.addEventListener('click', () => {
                    const st = getState(screen.dataset.liveId);
                    if (st.client) {
                        st.client.remoteUsers.forEach(u => { try { u.audioTrack?.play(); } catch (e) {} });
                    }
                    unmuteBtn.style.display = 'none';
                });
            }

            // Product sheet
            const sheetBtn = screen.querySelector('.js-open-product-sheet');
            const sheet = screen.querySelector('.js-product-sheet');
            if (sheetBtn && sheet) {
                const sheetClose = sheet.querySelector('.js-sheet-close');
                const sheetConfirm = sheet.querySelector('.js-sheet-confirm');

                sheetBtn.addEventListener('click', () => sheet.classList.add('open'));
                sheetClose.addEventListener('click', () => sheet.classList.remove('open'));

                sheet.querySelectorAll('.js-sheet-radio').forEach(r => {
                    r.addEventListener('change', () => {
                        sheet.querySelectorAll('.js-sheet-card').forEach(c => c.style.borderColor = 'rgba(255,255,255,.15)');
                        r.closest('label').querySelector('.js-sheet-card').style.borderColor = '#ef4444';
                    });
                });

                sheetConfirm.addEventListener('click', async () => {
                    const checked = sheet.querySelector('.js-sheet-radio:checked');
                    const bidVal = parseFloat(sheet.querySelector('.js-sheet-bid').value);
                    if (!checked) { alert({!! json_encode(__('Select a product first.')) !!}); return; }
                    if (!bidVal || bidVal < 1) { alert({!! json_encode(__('Enter a starting bid.')) !!}); return; }
                    sheetConfirm.disabled = true;
                    try {
                        const res = await apiFetch(screen.dataset.setProductUrl, { product_id: checked.value, starting_bid: bidVal });
                        if (res.ok) {
                            sheet.classList.remove('open');
                            const pi = screen.querySelector('.js-prod-img');
                            const pn = screen.querySelector('.js-prod-name');
                            const bd = screen.querySelector('.js-bid-display');
                            const bn = screen.querySelector('.js-bidder-name');
                            const pc = screen.querySelector('.js-product-card');
                            const bidBar = screen.querySelector('.js-bid-bar');
                            if (pn) pn.textContent = checked.dataset.name;
                            if (pi) { pi.src = checked.dataset.img; pi.style.display = ''; }
                            if (bd) bd.textContent = {!! json_encode(__('Start')) !!} + ': ' + bidVal.toFixed(2) + ' MAD';
                            if (bn) bn.textContent = '';
                            if (pc) pc.style.display = '';
                            if (bidBar) bidBar.style.display = 'none'; // seller doesn't bid
                        } else { alert(res.message || 'Error'); }
                    } catch (e) {}
                    sheetConfirm.disabled = false;
                });
            }
        }

        // ── IntersectionObserver ─────────────────────────────────────
        const initedScreens = new Set();
        const feed = document.getElementById('live-feed');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(async entry => {
                const screen = entry.target;
                const id = screen.dataset.liveId;
                if (entry.isIntersecting) {
                    if (!initedScreens.has(id)) {
                        initedScreens.add(id);
                        bindScreen(screen);
                    }
                    initAgora(screen);
                } else {
                    teardownAgora(screen);
                }
            });
        }, { root: feed, threshold: 0.5 });

        document.querySelectorAll('.live-screen').forEach(s => observer.observe(s));
    })();
});
</script>
@endsection
