@extends('layouts.app')

@section('before_head')
<style>
/* ── Reset layout ── */
#main-header { display: none !important; }
body > main { padding: 0 !important; margin: 0 !important; }
html, body { height: 100%; margin: 0; overflow: hidden; background: #000; }

/* ── Feed container ── */
#live-feed-outer {
    position: fixed; inset: 0; z-index: 50;
    display: flex; align-items: stretch; justify-content: center;
    background: #111;
}
#live-feed-outer::before, #live-feed-outer::after { content: ''; flex: 1; background: #000; }
#live-feed {
    width: 100%; max-width: 480px; height: 100dvh;
    overflow-y: scroll; scroll-snap-type: y mandatory;
    -webkit-overflow-scrolling: touch; scrollbar-width: none;
    position: relative; flex-shrink: 0;
}
#live-feed::-webkit-scrollbar { display: none; }

/* ── Individual screen ── */
.live-screen {
    position: relative; height: 100dvh; width: 100%;
    scroll-snap-align: start; scroll-snap-stop: always;
    background: #000; overflow: hidden;
}
.live-video-wrap { position: absolute; inset: 0; background: #111; }
.live-video-wrap video { width: 100%; height: 100%; object-fit: cover; }
.live-screen::after {
    content: ''; position: absolute; inset: 0; pointer-events: none; z-index: 1;
    background: linear-gradient(to top, rgba(0,0,0,.92) 0%, rgba(0,0,0,.1) 38%, rgba(0,0,0,.25) 100%);
}

/* ── Top bar ── */
.live-top-bar {
    position: absolute; top: 0; left: 0; right: 0; z-index: 10;
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 12px 10px; gap: 8px;
}
.ltb-left { display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1; }
.ltb-back {
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(0,0,0,.45); backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.18); color: #fff;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; flex-shrink: 0;
}
.ltb-avatar { width: 42px; height: 42px; border-radius: 50%; border: 2px solid rgba(255,255,255,.9); object-fit: cover; flex-shrink: 0; }
.ltb-seller-info { min-width: 0; }
.ltb-username { color: #fff; font-size: 14px; font-weight: 800; display: block; text-shadow: 0 1px 3px rgba(0,0,0,.6); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
.ltb-sub { display: flex; align-items: center; gap: 5px; margin-top: 1px; }
.ltb-live-badge {
    display: inline-flex; align-items: center; gap: 4px;
    background: rgba(220,38,38,.85); color: #fff; font-size: 10px; font-weight: 800;
    padding: 2px 7px; border-radius: 5px;
}
.ltb-live-dot { width: 5px; height: 5px; border-radius: 50%; background: #fff; animation: dotPulse 1.2s infinite; flex-shrink: 0; }
@keyframes dotPulse { 0%,100% { opacity:1; } 50% { opacity:.3; } }
.ltb-upcoming-badge { display: inline-flex; align-items: center; background: rgba(0,0,0,.5); color: rgba(255,255,255,.8); font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 5px; }
.ltb-follow-btn {
    background: #fbbf24; color: #111; border: none; border-radius: 8px;
    font-size: 12px; font-weight: 800; padding: 5px 14px; cursor: pointer; flex-shrink: 0; white-space: nowrap;
}
.ltb-right { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.ctrl-btn { background: rgba(0,0,0,.5); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,.2); border-radius: 20px; color: #fff; font-size: 11px; font-weight: 700; padding: 5px 11px; cursor: pointer; white-space: nowrap; }
.ctrl-btn.danger { background: rgba(220,38,38,.75); border-color: transparent; }
.ctrl-btn.go-live-btn { background: rgba(22,163,74,.85); border-color: transparent; }
.ctrl-btn:disabled { opacity: .5; cursor: not-allowed; }
.viewer-pill {
    display: none; align-items: center; gap: 5px;
    background: rgba(220,38,38,.85); backdrop-filter: blur(4px);
    border-radius: 20px; padding: 5px 10px; color: #fff; font-size: 12px; font-weight: 700;
}
.viewer-pill.visible { display: flex; }
.viewer-bars { display: flex; gap: 2px; align-items: flex-end; height: 13px; }
.viewer-bars span { width: 3px; border-radius: 2px; background: #fff; }
.viewer-bars span:nth-child(1) { height: 6px; animation: barP 1.1s ease infinite 0s; }
.viewer-bars span:nth-child(2) { height: 11px; animation: barP 1.1s ease infinite .18s; }
.viewer-bars span:nth-child(3) { height: 8px; animation: barP 1.1s ease infinite .09s; }
@keyframes barP { 0%,100% { opacity:1; } 50% { opacity:.35; } }
.ltb-dropdown {
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(0,0,0,.45); backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.18); color: rgba(255,255,255,.8);
    display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0;
}

/* ── Right sidebar ── */
.live-sidebar-right {
    position: absolute; right: 10px; bottom: 178px; z-index: 11;
    display: flex; flex-direction: column; align-items: center; gap: 18px;
}
.side-btn { display: flex; flex-direction: column; align-items: center; gap: 4px; cursor: pointer; user-select: none; }
.side-icon-wrap {
    width: 46px; height: 46px; border-radius: 50%;
    background: rgba(30,30,30,.75); backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    position: relative; font-size: 22px;
}
.side-icon-wrap.has-img { overflow: hidden; font-size: 0; }
.side-icon-wrap.has-img img { width: 100%; height: 100%; object-fit: cover; }
.side-label { color: #fff; font-size: 11px; font-weight: 600; text-shadow: 0 1px 3px rgba(0,0,0,.6); }
.side-badge {
    position: absolute; top: -5px; right: -7px;
    background: #fbbf24; color: #111; font-size: 9px; font-weight: 900;
    padding: 1px 5px; border-radius: 10px; min-width: 16px; text-align: center; line-height: 14px;
    white-space: nowrap;
}
.side-badge.green { background: #22c55e; color: #fff; }

/* ── Comments ── */
.live-comments {
    position: absolute; bottom: 236px; left: 12px; right: 64px; z-index: 10;
    max-height: 210px; overflow-y: auto; scrollbar-width: none;
    mask-image: linear-gradient(transparent 0%, black 28%);
    pointer-events: none;
}
.live-comments::-webkit-scrollbar { display: none; }
.comment-item { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 9px; pointer-events: auto; animation: slideUp .22s ease; }
.comment-avatar-circle { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 800; }
.comment-body { min-width: 0; }
.comment-username { font-size: 12px; font-weight: 800; color: #fff; display: block; text-shadow: 0 1px 2px rgba(0,0,0,.5); }
.comment-text { font-size: 12px; color: rgba(255,255,255,.88); text-shadow: 0 1px 2px rgba(0,0,0,.4); line-height: 1.35; display: block; }
.comment-joined { font-size: 12px; color: rgba(255,255,255,.65); font-style: italic; display: block; }
.bid-event-item { display: flex; align-items: center; gap: 8px; margin-bottom: 9px; animation: slideUp .22s ease; pointer-events: auto; }
.bid-event-bubble { background: rgba(251,191,36,.18); border: 1px solid rgba(251,191,36,.35); backdrop-filter: blur(4px); border-radius: 12px; padding: 5px 11px; color: #fde68a; font-size: 12px; font-weight: 600; line-height: 1.35; }
@keyframes slideUp { from { opacity:0; transform:translateY(7px); } to { opacity:1; transform:translateY(0); } }

/* ── Comment input ── */
.comment-input-row {
    position: absolute; bottom: 186px; left: 12px; right: 64px; z-index: 11;
    display: flex; gap: 8px; align-items: center;
}
.comment-input-row input {
    flex: 1; background: rgba(255,255,255,.11); backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.2); border-radius: 22px;
    padding: 9px 16px; color: #fff; font-size: 13px; outline: none;
}
.comment-input-row input::placeholder { color: rgba(255,255,255,.42); }
.comment-input-row button {
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
    border-radius: 50%; width: 36px; height: 36px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; color: #fff; cursor: pointer;
}

/* ── Bottom auction panel ── */
.bottom-auction-panel {
    position: absolute; bottom: 0; left: 0; right: 0; z-index: 10;
    padding: 8px 12px 20px;
}
.countdown-bar {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(0,0,0,.55); backdrop-filter: blur(6px);
    border-radius: 18px; padding: 5px 13px;
    color: #fbbf24; font-size: 12px; font-weight: 700; margin-bottom: 6px;
}
.winner-row {
    display: flex; align-items: center; gap: 5px; margin-bottom: 6px;
    color: #fbbf24; font-size: 13px; font-weight: 700;
}
.product-card-h {
    display: flex; align-items: center; gap: 10px;
    background: rgba(255,255,255,.09); border-radius: 14px; padding: 9px 11px;
    margin-bottom: 8px;
}
.product-card-h img { width: 62px; height: 62px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
.prod-info-h { flex: 1; min-width: 0; }
.prod-name-h { color: #fff; font-size: 13px; font-weight: 800; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.prod-sub-h { color: rgba(255,255,255,.45); font-size: 11px; display: block; margin-top: 2px; }
.prod-bidder-h { color: #fbbf24; font-size: 11px; display: block; margin-top: 1px; }
.prod-price-h { text-align: right; flex-shrink: 0; }
.prod-amount { color: #fff; font-size: 15px; font-weight: 800; display: block; }
.prod-status-tag { font-size: 12px; font-weight: 800; display: block; margin-top: 2px; }
.prod-status-tag.active { color: #22c55e; }
.prod-status-tag.sold { color: #ef4444; }
.awaiting-btn {
    width: 100%; padding: 13px; background: rgba(255,255,255,.1); backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,.12); border-radius: 14px;
    color: rgba(255,255,255,.55); font-size: 14px; font-weight: 600; text-align: center;
}

/* ── Swipe-to-bid ── */
.bid-slider {
    height: 52px; border-radius: 26px;
    background: rgba(255,255,255,.11); backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,.18);
    overflow: hidden; user-select: none; touch-action: none; position: relative; margin-bottom: 8px;
}
.bid-slider-fill { position: absolute; inset: 0; border-radius: 26px; background: linear-gradient(90deg, rgba(239,68,68,.65) 0%, rgba(239,68,68,.4) 100%); width: 0; transition: width .08s linear; pointer-events: none; }
.bid-slider-thumb { position: absolute; top: 4px; left: 4px; width: 44px; height: 44px; border-radius: 50%; background: #ef4444; display: flex; align-items: center; justify-content: center; color: #fff; cursor: grab; will-change: transform; box-shadow: 0 2px 8px rgba(0,0,0,.4); transition: background .2s; }
.bid-slider-thumb:active { cursor: grabbing; }
.bid-slider-thumb svg { pointer-events: none; }
.bid-slider-label { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 700; pointer-events: none; padding-left: 54px; padding-right: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bid-slider.success .bid-slider-thumb { background: #22c55e; }
.bid-slider.success .bid-slider-fill { background: rgba(34,197,94,.5); }
.bid-slider.locked { pointer-events: none; opacity: .7; }

/* ── Floating hearts ── */
.heart-float { position: absolute; bottom: 200px; right: 62px; z-index: 20; pointer-events: none; overflow: hidden; width: 50px; height: 200px; }
.heart { position: absolute; font-size: 22px; animation: floatHeart 1.8s ease-out forwards; }
@keyframes floatHeart { 0% { opacity:1; transform:translateY(0) scale(.8); } 50% { opacity:1; transform:translateY(-80px) scale(1.1); } 100% { opacity:0; transform:translateY(-180px) scale(.6); } }

/* ── Product sheet ── */
.product-sheet { position: absolute; bottom: 0; left: 0; right: 0; z-index: 40; background: #111; border-radius: 20px 20px 0 0; padding: 20px 16px; transform: translateY(100%); transition: transform .3s ease; max-height: 62dvh; overflow-y: auto; }
.product-sheet.open { transform: translateY(0); }
.sheet-handle { width: 36px; height: 4px; background: rgba(255,255,255,.22); border-radius: 2px; margin: 0 auto 16px; }

/* ── Unmute ── */
.unmute-btn { position: absolute; inset: 0; z-index: 20; display: flex; align-items: center; justify-content: center; }
.unmute-inner { background: rgba(0,0,0,.6); border: 2px solid rgba(255,255,255,.4); border-radius: 50px; color: #fff; padding: 12px 24px; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; }

/* ── Top-up modal ── */
#topup-backdrop { position: fixed; inset: 0; z-index: 300; background: rgba(0,0,0,.7); backdrop-filter: blur(4px); display: flex; align-items: flex-end; justify-content: center; }
#topup-modal { width: 100%; max-width: 480px; background: #1a1a1a; border-radius: 24px 24px 0 0; padding: 24px 20px 36px; color: #fff; animation: sheetUp .3s ease; }
@keyframes sheetUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
.topup-title { font-size: 17px; font-weight: 800; margin-bottom: 2px; }
.topup-sub { font-size: 13px; color: rgba(255,255,255,.45); margin-bottom: 18px; }
.topup-current { display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,.06); border-radius:12px; padding:12px 16px; margin-bottom:18px; }
.topup-current-label { font-size:12px; color:rgba(255,255,255,.5); }
.topup-current-value { font-size:16px; font-weight:800; color:#fde68a; }
.topup-presets { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
.topup-preset { background:rgba(255,255,255,.08); border:2px solid rgba(255,255,255,.12); border-radius:12px; padding:12px 6px; text-align:center; color:#fff; font-size:15px; font-weight:700; cursor:pointer; transition:border-color .15s,background .15s; }
.topup-preset.active { border-color:#fbbf24; background:rgba(251,191,36,.15); color:#fbbf24; }
.topup-custom-wrap { position:relative; margin-bottom:18px; }
.topup-custom-wrap input { width:100%; background:rgba(255,255,255,.08); border:2px solid rgba(255,255,255,.12); border-radius:12px; padding:12px 52px 12px 16px; color:#fff; font-size:15px; font-weight:600; outline:none; box-sizing:border-box; }
.topup-custom-wrap input:focus { border-color:#fbbf24; }
.topup-custom-wrap input::placeholder { color:rgba(255,255,255,.35); }
.topup-custom-unit { position:absolute; right:14px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,.4); font-size:13px; font-weight:600; }
.fake-card { background:linear-gradient(135deg,#1c3f6e 0%,#0f2647 100%); border-radius:14px; padding:16px 18px; margin-bottom:18px; display:flex; flex-direction:column; gap:10px; }
.fake-card-number { font-size:14px; font-weight:700; letter-spacing:3px; color:#fff; }
.fake-card-row { display:flex; justify-content:space-between; align-items:center; }
.fake-card-label { font-size:10px; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:1px; }
.fake-card-value { font-size:12px; font-weight:600; color:#fff; }
.topup-confirm-btn { width:100%; padding:14px; background:#fbbf24; color:#111; border:none; border-radius:14px; font-size:15px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:opacity .2s; }
.topup-confirm-btn:disabled { opacity:.5; cursor:not-allowed; }
.topup-cancel-btn { width:100%; padding:11px; background:transparent; color:rgba(255,255,255,.35); border:none; font-size:13px; cursor:pointer; margin-top:6px; }
.spinner { width:20px; height:20px; border:2px solid rgba(0,0,0,.25); border-top-color:#111; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
</style>
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.22.0.js"></script>
@endsection

@section('content')
<div id="live-feed-outer">
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
         data-likes="{{ $liveItem->likes_count }}">

        {{-- Video --}}
        <div class="live-video-wrap" id="video-wrap-{{ $liveItem->id }}"></div>

        {{-- Top bar --}}
        <div class="live-top-bar">
            <div class="ltb-left">
                @if($isFirst)
                <a href="{{ route('lives.index') }}" class="ltb-back" aria-label="{{ __('Back') }}">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                @endif
                <img class="ltb-avatar" src="{{ $liveItem->seller->avatar_url }}" alt="">
                <div class="ltb-seller-info">
                    <span class="ltb-username">{{ $liveItem->seller->username }}</span>
                    <div class="ltb-sub">
                        @if($liveItem->status === 'live')
                            <span class="ltb-live-badge"><span class="ltb-live-dot"></span>LIVE</span>
                        @else
                            <span class="ltb-upcoming-badge">{{ __('Upcoming') }}</span>
                        @endif
                    </div>
                </div>
                @if(!$isSeller && Auth::check())
                    <button class="ltb-follow-btn">{{ __('Follow') }}</button>
                @endif
            </div>
            <div class="ltb-right">
                {{-- Seller controls --}}
                @if($isSeller)
                <div style="display:flex;gap:5px;align-items:center;">
                    <button class="ctrl-btn go-live-btn js-go-live" @if($liveItem->status !== 'scheduled') style="display:none;" @endif>{{ __('Go Live') }}</button>
                    <button class="ctrl-btn js-close-auction" @if($liveItem->auction_status !== 'active') style="display:none;" @endif>{{ __('Close Bid') }}</button>
                    <button class="ctrl-btn js-open-product-sheet" @if($liveItem->status !== 'live') style="display:none;" @endif>🛍</button>
                    <button class="ctrl-btn danger js-end-live" @if($liveItem->status !== 'live') style="display:none;" @endif>{{ __('End') }}</button>
                </div>
                @endif
                {{-- Viewer count pill --}}
                <div class="viewer-pill js-viewer-pill">
                    <div class="viewer-bars"><span></span><span></span><span></span></div>
                    <span class="js-viewer-count-num">0</span>
                </div>
                {{-- Dropdown --}}
                <div class="ltb-dropdown">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Right sidebar --}}
        <div class="live-sidebar-right">
            {{-- Like --}}
            @auth
            <div class="side-btn js-like-btn">
                <div class="side-icon-wrap" style="font-size:24px;">❤️</div>
                <span class="side-label js-likes-count">{{ $liveItem->likes_count }}</span>
            </div>
            @endauth
            {{-- Share --}}
            <div class="side-btn js-share-btn">
                <div class="side-icon-wrap">
                    <svg style="width:22px;height:22px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                </div>
                <span class="side-label">{{ __('Share') }}</span>
            </div>
            {{-- Wallet --}}
            @auth
            <div class="side-btn js-wallet-btn">
                <div class="side-icon-wrap" style="position:relative;">
                    <svg style="width:20px;height:20px;color:#fff;" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="side-badge green js-balance-badge">{{ number_format(app(\Modules\Wallet\Services\WalletService::class)->getBalance(Auth::user()), 0) }}</span>
                </div>
                <span class="side-label">{{ __('Wallet') }}</span>
            </div>
            @endauth
            {{-- Shop (seller: open sheet) --}}
            @if($isSeller && $isFirst)
            <div class="side-btn js-open-product-sheet">
                <div class="side-icon-wrap js-shop-icon-wrap" style="font-size:22px;">🛍️</div>
                <span class="side-label">{{ __('Shop') }}</span>
            </div>
            @endif
        </div>

        {{-- Comments --}}
        <div class="live-comments js-comments">
            @if($isFirst)
                @foreach($recentComments as $comment)
                @php $col = '#' . substr(md5($comment->user->username), 0, 6); @endphp
                <div class="comment-item">
                    <div class="comment-avatar-circle" style="background:{{ $col }};">{{ strtoupper(substr($comment->user->username, 0, 1)) }}</div>
                    <div class="comment-body">
                        <span class="comment-username">{{ $comment->user->username }}</span>
                        <span class="comment-text">{{ $comment->content }}</span>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Comment input --}}
        @auth
        <div class="comment-input-row">
            <input type="text" class="js-comment-input" maxlength="200" placeholder="{{ __('Say something…') }}">
            <button class="js-comment-send">
                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
        @endauth

        {{-- Bottom auction panel --}}
        <div class="bottom-auction-panel">
            {{-- Seller: countdown bar --}}
            <div class="countdown-bar js-countdown-bar" style="{{ (!$isSeller || !$liveItem->countdown_ends_at) ? 'display:none;' : '' }}">
                <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="js-countdown-text">...</span>
            </div>

            {{-- Winner row --}}
            <div class="winner-row js-winner-row" style="display:none;">
                🏅 <span class="js-winner-name"></span>&nbsp;{{ __('won!') }}
            </div>

            {{-- Product card horizontal --}}
            <div class="product-card-h js-product-card" style="{{ ($liveItem->auction_status !== 'active' || !$liveItem->product) ? 'display:none;' : '' }}">
                @if($liveItem->product)
                    <img src="{{ $liveItem->product->getFeaturedImageUrl('preview') }}" alt="" class="js-prod-img">
                @else
                    <img src="" alt="" class="js-prod-img" style="display:none;">
                @endif
                <div class="prod-info-h">
                    <span class="prod-name-h js-prod-name">{{ $liveItem->product?->name ?? '' }}</span>
                    <span class="prod-sub-h">{{ __('Item on live') }}</span>
                    <span class="prod-bidder-h js-bidder-name">{{ $liveItem->currentBidder ? 'by ' . $liveItem->currentBidder->username : '' }}</span>
                </div>
                <div class="prod-price-h">
                    <span class="prod-amount js-bid-display">
                        @if($liveItem->current_bid)
                            {{ number_format($liveItem->current_bid, 2) }} MAD
                        @elseif($liveItem->product)
                            {{ number_format($liveItem->starting_bid, 2) }} MAD
                        @endif
                    </span>
                    <span class="prod-status-tag active js-prod-status">{{ $liveItem->current_bid ? __('Top bid') : __('Start') }}</span>
                </div>
            </div>

            {{-- Bid slider (watcher only) --}}
            @auth
            @if(!$isSeller)
            <div class="bid-slider js-bid-bar" style="{{ $liveItem->auction_status !== 'active' ? 'display:none;' : '' }}" data-min-bid="{{ $liveItem->min_next_bid }}">
                <div class="bid-slider-fill js-slider-fill"></div>
                <div class="bid-slider-thumb js-slider-thumb">
                    <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <div class="bid-slider-label js-slider-label">{{ __('Slide to bid') }} {{ number_format($liveItem->min_next_bid, 2) }} MAD</div>
            </div>
            @endif
            @endauth

            {{-- Awaiting next item --}}
            <div class="js-awaiting-row" style="{{ $liveItem->auction_status === 'active' ? 'display:none;' : '' }}">
                <div class="awaiting-btn">{{ __('Awaiting Next Item') }}</div>
            </div>
        </div>

        {{-- Floating hearts --}}
        <div class="heart-float js-heart-float"></div>

        {{-- Unmute --}}
        <div class="unmute-btn js-unmute" style="display:none;">
            <div class="unmute-inner">
                <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                </svg>
                {{ __('Tap to unmute') }}
            </div>
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
</div>{{-- #live-feed --}}
</div>{{-- #live-feed-outer --}}

@auth
{{-- Top-up modal --}}
<div id="topup-backdrop" style="display:none;">
    <div id="topup-modal">
        <div class="topup-title">{{ __('Top Up Wallet') }}</div>
        <p class="topup-sub">{{ __('Funds go directly to your wallet balance') }}</p>
        <div class="topup-current">
            <span class="topup-current-label">{{ __('Current balance') }}</span>
            <span class="topup-current-value" id="topup-current-display">{{ number_format(app(\Modules\Wallet\Services\WalletService::class)->getBalance(Auth::user()), 2) }} MAD</span>
        </div>
        <div class="topup-presets">
            <div class="topup-preset" data-amount="300">300 MAD</div>
            <div class="topup-preset" data-amount="500">500 MAD</div>
            <div class="topup-preset" data-amount="1000">1000 MAD</div>
        </div>
        <div class="topup-custom-wrap">
            <input type="number" id="topup-custom-input" min="1" max="10000" placeholder="{{ __('Custom amount') }}">
            <span class="topup-custom-unit">MAD</span>
        </div>
        <div class="fake-card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div class="fake-card-label">{{ __('Saved card') }}</div>
                    <div class="fake-card-number">•••• •••• •••• 4242</div>
                </div>
                <div style="display:flex;">
                    <div style="width:26px;height:26px;border-radius:50%;background:#eb001b;opacity:.9;"></div>
                    <div style="width:26px;height:26px;border-radius:50%;background:#f79e1b;opacity:.9;margin-left:-8px;"></div>
                </div>
            </div>
            <div class="fake-card-row">
                <div>
                    <div class="fake-card-label">{{ __('Cardholder') }}</div>
                    <div class="fake-card-value">{{ Auth::user()->full_name }}</div>
                </div>
                <div>
                    <div class="fake-card-label">{{ __('Expires') }}</div>
                    <div class="fake-card-value">12/28</div>
                </div>
            </div>
        </div>
        <button class="topup-confirm-btn" id="topup-confirm-btn">{{ __('Confirm & Top Up') }}</button>
        <button class="topup-cancel-btn" id="topup-cancel-btn">{{ __('Cancel') }}</button>
    </div>
</div>
@endauth
@endsection

@section('after_body')
<script>
document.addEventListener('DOMContentLoaded', function () {
    (function () {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const IS_AUTH = {!! Auth::check() ? 'true' : 'false' !!};
        const TOP_UP_URL = {!! json_encode(route('balance.top-up')) !!};

        // ── Balance ───────────────────────────────────────────────────
        let currentBalance = {!! Auth::check() ? (float) app(\Modules\Wallet\Services\WalletService::class)->getBalance(Auth::user()) : 0 !!};

        function updateBalanceChip(amount) {
            currentBalance = amount;
            document.querySelectorAll('.js-balance-badge').forEach(el => {
                el.textContent = Math.floor(amount) + ' MAD';
            });
            const cur = document.getElementById('topup-current-display');
            if (cur) cur.textContent = parseFloat(amount).toFixed(2) + ' MAD';
        }

        // ── Top-up modal ──────────────────────────────────────────────
        let topupResolve = null, selectedTopupAmount = 0;
        const topupBackdrop   = document.getElementById('topup-backdrop');
        const topupCustomInput = document.getElementById('topup-custom-input');
        const topupConfirmBtn = document.getElementById('topup-confirm-btn');
        const topupCancelBtn  = document.getElementById('topup-cancel-btn');

        function showTopupModal(suggestedAmount) {
            return new Promise(resolve => {
                topupResolve = resolve;
                selectedTopupAmount = suggestedAmount || 0;
                document.querySelectorAll('.topup-preset').forEach(b => b.classList.toggle('active', parseInt(b.dataset.amount) === suggestedAmount));
                if (topupCustomInput) topupCustomInput.value = '';
                if (topupBackdrop) topupBackdrop.style.display = 'flex';
            });
        }
        function hideTopupModal() {
            if (topupBackdrop) topupBackdrop.style.display = 'none';
            topupResolve = null; selectedTopupAmount = 0;
        }
        document.querySelectorAll('.topup-preset').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedTopupAmount = parseInt(btn.dataset.amount);
                document.querySelectorAll('.topup-preset').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                if (topupCustomInput) topupCustomInput.value = '';
            });
        });
        if (topupCustomInput) {
            topupCustomInput.addEventListener('input', () => {
                selectedTopupAmount = parseFloat(topupCustomInput.value) || 0;
                document.querySelectorAll('.topup-preset').forEach(b => b.classList.remove('active'));
            });
        }
        if (topupConfirmBtn) {
            topupConfirmBtn.addEventListener('click', async () => {
                const amount = selectedTopupAmount || parseFloat(topupCustomInput?.value) || 0;
                if (!amount || amount < 1) { alert({!! json_encode(__('Select or enter an amount.')) !!}); return; }
                topupConfirmBtn.disabled = true;
                topupConfirmBtn.innerHTML = '<div class="spinner"></div>';
                try {
                    const res = await apiFetch(TOP_UP_URL, { amount });
                    if (res.ok) {
                        updateBalanceChip(res.balance);
                        const resolve = topupResolve;
                        hideTopupModal();
                        if (resolve) resolve(true);
                    }
                } catch (e) {}
                topupConfirmBtn.disabled = false;
                topupConfirmBtn.innerHTML = {!! json_encode(__('Confirm & Top Up')) !!};
            });
        }
        if (topupCancelBtn) {
            topupCancelBtn.addEventListener('click', () => { const r = topupResolve; hideTopupModal(); if (r) r(false); });
        }

        async function apiFetch(url, body) {
            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };
            try { const sid = window.Echo?.socketId(); if (sid) headers['X-Socket-ID'] = sid; } catch (e) {}
            const r = await fetch(url, { method: 'POST', headers, body: JSON.stringify(body) });
            return r.json();
        }

        const screenState = new Map();
        function getState(id) {
            if (!screenState.has(id)) screenState.set(id, { client: null, localTracks: [], countdownTimer: null, auctionEnding: false });
            return screenState.get(id);
        }

        function escHtml(s) {
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function getUserColor(username) {
            const colors = ['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#06b6d4'];
            let h = 0;
            for (let i = 0; i < (username||'').length; i++) h = (username.charCodeAt(i) + ((h << 5) - h)) | 0;
            return colors[Math.abs(h) % colors.length];
        }

        // ── Countdown ────────────────────────────────────────────────
        function startCountdown(screen, endsAtIso) {
            const id = screen.dataset.liveId;
            const st = getState(id);
            const bar = screen.querySelector('.js-countdown-bar');
            const sliderEl = screen.querySelector('.js-bid-bar');
            const sliderLabel = screen.querySelector('.js-slider-label');
            const isSeller = screen.dataset.isSeller === '1';
            clearInterval(st.countdownTimer);
            if (!endsAtIso) {
                if (bar) bar.style.display = 'none';
                if (sliderLabel && sliderEl) sliderLabel.textContent = {!! json_encode(__('Slide to bid')) !!} + ' ' + parseFloat(sliderEl.dataset.minBid || 0).toFixed(2) + ' MAD';
                return;
            }
            if (isSeller) {
                if (bar) bar.style.display = '';
            } else {
                if (bar) bar.style.display = 'none';
            }
            st.countdownTimer = setInterval(() => {
                const remaining = Math.round((new Date(endsAtIso) - Date.now()) / 1000);
                if (remaining <= 0) {
                    clearInterval(st.countdownTimer);
                    if (bar) bar.style.display = 'none';
                    if (sliderLabel && sliderEl) sliderLabel.textContent = {!! json_encode(__('Slide to bid')) !!} + ' ' + parseFloat(sliderEl.dataset.minBid || 0).toFixed(2) + ' MAD';
                    if (isSeller && !st.auctionEnding) { st.auctionEnding = true; triggerCloseAuction(screen); }
                    return;
                }
                const txt = screen.querySelector('.js-countdown-text');
                if (isSeller) {
                    if (txt) txt.textContent = {!! json_encode(__('Closing in')) !!} + ' ' + remaining + 's';
                } else if (sliderLabel && sliderEl) {
                    sliderLabel.textContent = '⏱ ' + remaining + 's  ·  ' + {!! json_encode(__('Slide to bid')) !!} + ' ' + parseFloat(sliderEl.dataset.minBid || 0).toFixed(2) + ' MAD';
                }
            }, 500);
        }

        // ── Comments / events ─────────────────────────────────────────
        function appendComment(screen, c) {
            const box = screen.querySelector('.js-comments');
            const color = getUserColor(c.username || '');
            const initial = (c.username || '?')[0].toUpperCase();
            const div = document.createElement('div');
            div.className = 'comment-item';
            div.innerHTML = '<div class="comment-avatar-circle" style="background:' + color + ';">' + initial + '</div>'
                + '<div class="comment-body"><span class="comment-username">' + escHtml(c.username) + '</span>'
                + '<span class="comment-text">' + escHtml(c.content) + '</span></div>';
            box.appendChild(div);
            box.scrollTop = box.scrollHeight;
            while (box.children.length > 80) box.removeChild(box.firstChild);
        }

        function appendJoinedEvent(screen, username) {
            const box = screen.querySelector('.js-comments');
            const color = getUserColor(username || '');
            const initial = (username || '?')[0].toUpperCase();
            const div = document.createElement('div');
            div.className = 'comment-item';
            div.innerHTML = '<div class="comment-avatar-circle" style="background:' + color + ';">' + initial + '</div>'
                + '<div class="comment-body"><span class="comment-username">' + escHtml(username) + '</span>'
                + '<span class="comment-joined">joined 👋</span></div>';
            box.appendChild(div);
            box.scrollTop = box.scrollHeight;
            while (box.children.length > 80) box.removeChild(box.firstChild);
        }

        function appendBidEvent(screen, username, amount) {
            const box = screen.querySelector('.js-comments');
            const div = document.createElement('div');
            div.className = 'bid-event-item';
            div.innerHTML = '<div class="bid-event-bubble">🔨 <strong>' + escHtml(username) + '</strong> {!! json_encode(__('bid')) !!} <strong>' + escHtml(amount) + ' MAD</strong></div>';
            box.appendChild(div);
            box.scrollTop = box.scrollHeight;
            while (box.children.length > 80) box.removeChild(box.firstChild);
        }

        // ── Winner / bottom panel state ───────────────────────────────
        function showWinner(screen, username, amount) {
            const winnerRow  = screen.querySelector('.js-winner-row');
            const winnerName = screen.querySelector('.js-winner-name');
            const awaitingRow = screen.querySelector('.js-awaiting-row');
            if (winnerRow && winnerName) {
                winnerName.textContent = username || '';
                winnerRow.style.display = username ? 'flex' : 'none';
            }
            if (awaitingRow) awaitingRow.style.display = '';
        }

        function setBottomActive(screen) {
            const awaitingRow = screen.querySelector('.js-awaiting-row');
            const winnerRow   = screen.querySelector('.js-winner-row');
            if (awaitingRow) awaitingRow.style.display = 'none';
            if (winnerRow)   winnerRow.style.display   = 'none';
        }

        // ── Swipe-to-bid ─────────────────────────────────────────────
        function resetSlider(el) {
            const thumb = el.querySelector('.js-slider-thumb');
            const fill  = el.querySelector('.js-slider-fill');
            el.classList.remove('success', 'locked');
            if (thumb) { thumb.style.transition = 'transform .3s ease'; thumb.style.transform = 'translateX(0)'; }
            if (fill)  { fill.style.transition  = 'width .3s ease';     fill.style.width = '0'; }
            setTimeout(() => { if (thumb) thumb.style.transition = ''; if (fill) fill.style.transition = ''; }, 320);
        }

        function bindSlider(sliderEl, screen) {
            const thumb = sliderEl.querySelector('.js-slider-thumb');
            const fill  = sliderEl.querySelector('.js-slider-fill');
            const label = sliderEl.querySelector('.js-slider-label');
            let startX = 0, currentX = 0, dragging = false;
            const getTrack = () => sliderEl.offsetWidth - 52;

            const onStart = e => {
                if (sliderEl.classList.contains('locked')) return;
                dragging = true;
                startX = e.touches ? e.touches[0].clientX : e.clientX;
                thumb.style.transition = 'none'; fill.style.transition = 'none';
            };
            const onMove = e => {
                if (!dragging) return;
                const x = e.touches ? e.touches[0].clientX : e.clientX;
                currentX = Math.max(0, Math.min(x - startX, getTrack()));
                thumb.style.transform = 'translateX(' + currentX + 'px)';
                fill.style.width = (currentX + 52) + 'px';
            };
            const onEnd = async () => {
                if (!dragging) return;
                dragging = false;
                const track = getTrack();
                if (currentX >= track * 0.85) {
                    thumb.style.transition = 'transform .15s ease';
                    fill.style.transition  = 'width .15s ease';
                    thumb.style.transform = 'translateX(' + track + 'px)';
                    fill.style.width = '100%';
                    sliderEl.classList.add('locked');
                    await submitBid(parseFloat(sliderEl.dataset.minBid), sliderEl, screen, label);
                } else {
                    resetSlider(sliderEl);
                }
                currentX = 0;
            };
            thumb.addEventListener('mousedown', onStart);
            thumb.addEventListener('touchstart', onStart, { passive: true });
            document.addEventListener('mousemove', onMove);
            document.addEventListener('touchmove', onMove, { passive: true });
            document.addEventListener('mouseup', onEnd);
            document.addEventListener('touchend', onEnd);
        }

        async function submitBid(amount, sliderEl, screen, label) {
            try {
                const res = await apiFetch(screen.dataset.bidUrl, { amount });
                if (res.insufficient_balance) {
                    resetSlider(sliderEl);
                    const presets = [300, 500, 1000];
                    const suggested = presets.find(p => p >= res.shortfall) || Math.ceil(res.shortfall / 100) * 100;
                    const charged = await showTopupModal(suggested);
                    if (charged) await submitBid(amount, sliderEl, screen, label);
                } else if (!res.ok) {
                    resetSlider(sliderEl);
                    alert(res.message || 'Error');
                } else {
                    if (res.balance !== undefined) updateBalanceChip(res.balance);
                    appendBidEvent(screen, {!! json_encode(Auth::user()?->username ?? '') !!}, amount.toFixed(2));
                    sliderEl.classList.add('success');
                    const nextBid = amount + 10;
                    if (label) label.textContent = {!! json_encode(__('Slide to bid')) !!} + ' ' + nextBid.toFixed(2) + ' MAD';
                    sliderEl.dataset.minBid = nextBid;
                    setTimeout(() => resetSlider(sliderEl), 1200);
                }
            } catch (e) { resetSlider(sliderEl); }
        }

        // ── Hearts ───────────────────────────────────────────────────
        function spawnHeart(screen) {
            const c = screen.querySelector('.js-heart-float');
            const hearts = ['❤️','🧡','💛','💚','💙','💜'];
            const h = document.createElement('span');
            h.className = 'heart';
            h.textContent = hearts[Math.floor(Math.random() * hearts.length)];
            h.style.left = (Math.random() * 20) + 'px';
            c.appendChild(h);
            setTimeout(() => h.remove(), 1900);
        }

        // ── Viewer count ─────────────────────────────────────────────
        function updateViewerDisplay(screen, count) {
            const pill = screen.querySelector('.viewer-pill');
            const num  = screen.querySelector('.js-viewer-count-num');
            if (!pill) return;
            if (count > 0) { pill.classList.add('visible'); if (num) num.textContent = count; }
            else { pill.classList.remove('visible'); }
        }

        // ── Go Live ──────────────────────────────────────────────────
        async function triggerGoLive(screen) {
            const url = screen.dataset.goLiveUrl;
            if (!url) return;
            await apiFetch(url, {});
            screen.dataset.status = 'live';
            const goBtn = screen.querySelector('.js-go-live');
            const setBtn = screen.querySelector('.js-open-product-sheet');
            const endBtn = screen.querySelector('.js-end-live');
            if (goBtn) goBtn.style.display = 'none';
            if (setBtn) setBtn.style.display = '';
            if (endBtn) endBtn.style.display = '';
            const ltbSub = screen.querySelector('.ltb-sub');
            if (ltbSub) ltbSub.innerHTML = '<span class="ltb-live-badge"><span class="ltb-live-dot"></span>LIVE</span>';
            const st = getState(screen.dataset.liveId);
            if (st.client && st.localTracks.length) try { await st.client.publish(st.localTracks); } catch (e) {}
        }

        async function triggerCloseAuction(screen) {
            const url = screen.dataset.closeAuctionUrl;
            if (!url) return;
            try { await apiFetch(url, {}); } catch (e) {}
        }

        // ── Pusher + presence ─────────────────────────────────────────
        function bindPusherChannel(screen) {
            const id = screen.dataset.liveId;
            const ch = Echo.channel('live.' + id);

            if (IS_AUTH) {
                let viewerCount = 0;
                Echo.join('live.' + id)
                    .here(members => { viewerCount = members.length; updateViewerDisplay(screen, viewerCount); })
                    .joining(member => {
                        viewerCount++;
                        updateViewerDisplay(screen, viewerCount);
                        appendJoinedEvent(screen, member.username);
                    })
                    .leaving(() => { viewerCount = Math.max(0, viewerCount - 1); updateViewerDisplay(screen, viewerCount); });
            }

            ch.listen('BidPlaced', e => {
                const bd = screen.querySelector('.js-bid-display');
                const bn = screen.querySelector('.js-bidder-name');
                const sliderLabel = screen.querySelector('.js-slider-label');
                const sliderEl = screen.querySelector('.js-bid-bar');
                const prodStatus = screen.querySelector('.js-prod-status');
                if (bd) bd.textContent = parseFloat(e.current_bid).toFixed(2) + ' MAD';
                if (bn) bn.textContent = 'by ' + (e.bidder_username || '');
                if (prodStatus) { prodStatus.textContent = 'Top bid'; prodStatus.className = 'prod-status-tag active js-prod-status'; }
                if (sliderLabel) sliderLabel.textContent = {!! json_encode(__('Slide to bid')) !!} + ' ' + parseFloat(e.min_next_bid).toFixed(2) + ' MAD';
                if (sliderEl) sliderEl.dataset.minBid = e.min_next_bid;
                startCountdown(screen, e.countdown_ends_at);
                appendBidEvent(screen, e.bidder_username, parseFloat(e.current_bid).toFixed(2));
            });

            ch.listen('AuctionProductChanged', e => {
                const pc = screen.querySelector('.js-product-card');
                const pn = screen.querySelector('.js-prod-name');
                const pi = screen.querySelector('.js-prod-img');
                const bd = screen.querySelector('.js-bid-display');
                const bn = screen.querySelector('.js-bidder-name');
                const bidBar = screen.querySelector('.js-bid-bar');
                const prodStatus = screen.querySelector('.js-prod-status');
                const shopWrap = screen.querySelector('.js-shop-icon-wrap');
                const isSeller = screen.dataset.isSeller === '1';
                if (pn) pn.textContent = e.product_name || '';
                if (pi && e.product_image) { pi.src = e.product_image; pi.style.display = ''; }
                if (bd) bd.textContent = parseFloat(e.starting_bid).toFixed(2) + ' MAD';
                if (bn) bn.textContent = '';
                if (prodStatus) { prodStatus.textContent = 'Start'; prodStatus.className = 'prod-status-tag active js-prod-status'; }
                if (pc) pc.style.display = '';
                if (shopWrap && e.product_image) {
                    shopWrap.innerHTML = '<img src="' + escHtml(e.product_image) + '" alt="">';
                    shopWrap.classList.add('has-img');
                }
                setBottomActive(screen);
                if (bidBar && !isSeller) {
                    bidBar.style.display = '';
                    bidBar.dataset.minBid = e.starting_bid;
                    const lbl = bidBar.querySelector('.js-slider-label');
                    if (lbl) lbl.textContent = {!! json_encode(__('Slide to bid')) !!} + ' ' + parseFloat(e.starting_bid).toFixed(2) + ' MAD';
                    resetSlider(bidBar);
                }
                screen.dataset.auction = 'active';
                const bar = screen.querySelector('.js-countdown-bar');
                if (bar) bar.style.display = 'none';
                clearInterval(getState(id).countdownTimer);
            });

            ch.listen('AuctionClosed', e => {
                const bidBar     = screen.querySelector('.js-bid-bar');
                const pc         = screen.querySelector('.js-product-card');
                const prodStatus = screen.querySelector('.js-prod-status');
                const shopWrap   = screen.querySelector('.js-shop-icon-wrap');
                if (bidBar) bidBar.style.display = 'none';
                if (pc) pc.style.display = '';
                if (prodStatus) { prodStatus.textContent = 'Sold'; prodStatus.className = 'prod-status-tag sold js-prod-status'; }
                if (shopWrap) { shopWrap.innerHTML = '🛍️'; shopWrap.classList.remove('has-img'); shopWrap.style.fontSize = '22px'; }
                screen.dataset.auction = 'idle';
                const bar = screen.querySelector('.js-countdown-bar');
                if (bar) bar.style.display = 'none';
                clearInterval(getState(id).countdownTimer);
                getState(id).auctionEnding = false;
                showWinner(screen, e.winner_username, e.winning_bid ? parseFloat(e.winning_bid).toFixed(2) : null);
                if (e.product_id) {
                    const radio = screen.querySelector('.js-sheet-radio[value="' + e.product_id + '"]');
                    if (radio) radio.closest('label').remove();
                }
            });

            ch.listen('CommentPosted', e => appendComment(screen, e));

            ch.listen('LiveStatusChanged', e => {
                if (e.status === 'live') {
                    screen.dataset.status = 'live';
                    const ltbSub = screen.querySelector('.ltb-sub');
                    if (ltbSub) ltbSub.innerHTML = '<span class="ltb-live-badge"><span class="ltb-live-dot"></span>LIVE</span>';
                } else if (e.status === 'ended') {
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
            AgoraRTC.onAudioAutoplayFailed = () => { const b = screen.querySelector('.js-unmute'); if (b) b.style.display = 'flex'; };
            try {
                if (isSeller) {
                    await client.setClientRole('host');
                    await client.join(tokenData.app_id, tokenData.channel, tokenData.token, tokenData.uid);
                    const [audioTrack, videoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
                    st.localTracks = [audioTrack, videoTrack];
                    videoTrack.play(wrap);
                    if (screen.dataset.status === 'live') await client.publish([audioTrack, videoTrack]);
                } else {
                    await client.setClientRole('audience', { level: 2 });
                    await client.join(tokenData.app_id, tokenData.channel, tokenData.token, tokenData.uid);
                }
            } catch (e) { console.error('Agora join error', e); return; }
            client.on('user-published', async (user, mediaType) => {
                await client.subscribe(user, mediaType);
                if (mediaType === 'video') {
                    const div = document.createElement('div');
                    div.id = 'agora-remote-' + user.uid;
                    div.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;';
                    wrap.innerHTML = ''; wrap.appendChild(div);
                    user.videoTrack.play(div);
                }
                if (mediaType === 'audio') {
                    try { user.audioTrack.play(); }
                    catch (e) { const b = screen.querySelector('.js-unmute'); if (b) b.style.display = 'flex'; }
                }
            });
            client.on('user-unpublished', (user, mediaType) => {
                if (mediaType === 'video') { const d = document.getElementById('agora-remote-' + user.uid); if (d) d.remove(); }
            });
        }

        async function teardownAgora(screen) {
            const id = screen.dataset.liveId;
            const st = getState(id);
            if (!st.client) return;
            st.localTracks.forEach(t => { try { t.stop(); t.close(); } catch (e) {} });
            try { await st.client.leave(); } catch (e) {}
            st.client = null; st.localTracks = [];
        }

        // ── Bind screen ───────────────────────────────────────────────
        function bindScreen(screen) {
            bindPusherChannel(screen);
            const cdIso = screen.dataset.countdown;
            if (cdIso && Date.now() < new Date(cdIso)) startCountdown(screen, cdIso);

            // Go Live
            const goBtn = screen.querySelector('.js-go-live');
            if (goBtn) goBtn.addEventListener('click', async () => { goBtn.disabled = true; await triggerGoLive(screen); });

            // End Live
            const endBtn = screen.querySelector('.js-end-live');
            if (endBtn) endBtn.addEventListener('click', async () => {
                if (!confirm({!! json_encode(__('End this live auction now?')) !!})) return;
                endBtn.disabled = true;
                try { await apiFetch(screen.dataset.endLiveUrl, {}); await teardownAgora(screen); window.location.href = {!! json_encode(route('lives.index')) !!}; }
                catch (e) { endBtn.disabled = false; }
            });

            // Close auction
            const closeAuctionBtn = screen.querySelector('.js-close-auction');
            if (closeAuctionBtn) closeAuctionBtn.addEventListener('click', async () => {
                closeAuctionBtn.disabled = true;
                await triggerCloseAuction(screen);
                closeAuctionBtn.disabled = false;
            });

            // Like
            const likeBtn = screen.querySelector('.js-like-btn');
            if (likeBtn && IS_AUTH) likeBtn.addEventListener('click', () => {
                spawnHeart(screen);
                const countEl = likeBtn.querySelector('.js-likes-count');
                if (countEl) countEl.textContent = parseInt(countEl.textContent || '0', 10) + 1;
                apiFetch(screen.dataset.likeUrl, {}).catch(() => {});
            });

            // Share
            const shareBtn = screen.querySelector('.js-share-btn');
            if (shareBtn) shareBtn.addEventListener('click', () => {
                const url = window.location.origin + '/lives/' + screen.dataset.liveId;
                if (navigator.share) navigator.share({ url }).catch(() => {});
                else { navigator.clipboard?.writeText(url); alert({!! json_encode(__('Link copied!')) !!}); }
            });

            // Wallet
            const walletBtn = screen.querySelector('.js-wallet-btn');
            if (walletBtn && IS_AUTH) walletBtn.addEventListener('click', () => showTopupModal(0).then(() => {}));

            // Bid slider
            const sliderEl = screen.querySelector('.js-bid-bar');
            if (sliderEl && IS_AUTH) bindSlider(sliderEl, screen);

            // Comment
            const commentInput = screen.querySelector('.js-comment-input');
            const commentSend  = screen.querySelector('.js-comment-send');
            if (commentSend && IS_AUTH) {
                const send = async () => {
                    const content = commentInput.value.trim();
                    if (!content) return;
                    commentInput.value = '';
                    appendComment(screen, { avatar_url: '', username: {!! json_encode(Auth::user()?->username ?? '') !!}, content });
                    apiFetch(screen.dataset.commentUrl, { content }).catch(() => {});
                };
                commentSend.addEventListener('click', send);
                commentInput.addEventListener('keydown', e => { if (e.key === 'Enter') send(); });
            }

            // Unmute
            const unmuteBtn = screen.querySelector('.js-unmute');
            if (unmuteBtn) unmuteBtn.addEventListener('click', () => {
                const st = getState(screen.dataset.liveId);
                if (st.client) st.client.remoteUsers.forEach(u => { try { u.audioTrack?.play(); } catch (e) {} });
                unmuteBtn.style.display = 'none';
            });

            // Product sheet
            const sheetBtn     = screen.querySelector('.js-open-product-sheet');
            const sheet        = screen.querySelector('.js-product-sheet');
            if (sheetBtn && sheet) {
                sheetBtn.addEventListener('click', () => sheet.classList.add('open'));
                sheet.querySelector('.js-sheet-close').addEventListener('click', () => sheet.classList.remove('open'));
                sheet.querySelectorAll('.js-sheet-radio').forEach(r => {
                    r.addEventListener('change', () => {
                        sheet.querySelectorAll('.js-sheet-card').forEach(c => c.style.borderColor = 'rgba(255,255,255,.15)');
                        r.closest('label').querySelector('.js-sheet-card').style.borderColor = '#ef4444';
                    });
                });
                sheet.querySelector('.js-sheet-confirm').addEventListener('click', async () => {
                    const checked = sheet.querySelector('.js-sheet-radio:checked');
                    const bidVal  = parseFloat(sheet.querySelector('.js-sheet-bid').value);
                    if (!checked) { alert({!! json_encode(__('Select a product first.')) !!}); return; }
                    if (!bidVal || bidVal < 1) { alert({!! json_encode(__('Enter a starting bid.')) !!}); return; }
                    const btn = sheet.querySelector('.js-sheet-confirm');
                    btn.disabled = true;
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
                            const prodStatus = screen.querySelector('.js-prod-status');
                            const shopWrap = screen.querySelector('.js-shop-icon-wrap');
                            if (pn) pn.textContent = checked.dataset.name;
                            if (pi) { pi.src = checked.dataset.img; pi.style.display = ''; }
                            if (bd) bd.textContent = bidVal.toFixed(2) + ' MAD';
                            if (bn) bn.textContent = '';
                            if (prodStatus) { prodStatus.textContent = 'Start'; prodStatus.className = 'prod-status-tag active js-prod-status'; }
                            if (pc) pc.style.display = '';
                            if (shopWrap) { shopWrap.innerHTML = '<img src="' + escHtml(checked.dataset.img) + '" alt="">'; shopWrap.classList.add('has-img'); }
                            if (bidBar) bidBar.style.display = 'none';
                            setBottomActive(screen);
                        } else { alert(res.message || 'Error'); }
                    } catch (e) {}
                    btn.disabled = false;
                });
            }
        }

        // ── IntersectionObserver ──────────────────────────────────────
        const initedScreens = new Set();
        const feed = document.getElementById('live-feed');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(async entry => {
                const screen = entry.target;
                const id = screen.dataset.liveId;
                if (entry.isIntersecting) {
                    if (!initedScreens.has(id)) { initedScreens.add(id); bindScreen(screen); }
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
