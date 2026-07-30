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
    background: #fbbf24; color: #111; border: 1px solid transparent; border-radius: 8px;
    font-size: 12px; font-weight: 800; padding: 5px 14px; cursor: pointer; flex-shrink: 0; white-space: nowrap;
    transition: background .15s, color .15s, border-color .15s;
}
.ltb-follow-btn.following {
    background: transparent; color: rgba(255,255,255,.8); border-color: rgba(255,255,255,.4);
}
.ltb-follow-btn:disabled { opacity: .6; cursor: not-allowed; }
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
    transition: bottom .2s ease;
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
    transition: bottom .2s ease;
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
    transition: bottom .2s ease;
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

/* ── Product sheet (seller) ── */
.product-sheet { position: absolute; bottom: 0; left: 0; right: 0; z-index: 40; background: #111; border-radius: 20px 20px 0 0; padding: 20px 16px 24px; transform: translateY(100%); transition: transform .3s ease; max-height: 68dvh; overflow-y: auto; }
.product-sheet.open { transform: translateY(0); }
.sheet-handle { width: 36px; height: 4px; background: rgba(255,255,255,.22); border-radius: 2px; margin: 0 auto 14px; }
.sheet-title { color: #fff; font-size: 15px; font-weight: 700; margin-bottom: 14px; }
.sheet-product-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
.sheet-product-label { cursor: pointer; display: block; }
.sheet-product-card { border: 2px solid rgba(255,255,255,.15); border-radius: 12px; overflow: hidden; transition: border-color .15s, background .15s; }
.sheet-product-label:has(.js-sheet-radio:checked) .sheet-product-card { border-color: #ef4444; background: rgba(239,68,68,.08); }
.sheet-card-img-wrap { position: relative; width: 100%; aspect-ratio: 1/1; background: #222; overflow: hidden; }
.sheet-card-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
.sheet-card-no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.2); }
.sheet-card-no-img svg { width: 32px; height: 32px; }
.sheet-card-check { position: absolute; top: 6px; right: 6px; width: 22px; height: 22px; border-radius: 50%; background: #ef4444; display: none; align-items: center; justify-content: center; }
.sheet-card-check svg { width: 12px; height: 12px; color: #fff; }
.sheet-product-label:has(.js-sheet-radio:checked) .sheet-card-check { display: flex; }
.sheet-card-info { padding: 8px 10px 10px; }
.sheet-card-name { color: #fff; font-size: 12px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 2px; }
.sheet-card-price { color: rgba(255,255,255,.5); font-size: 11px; }
.sheet-card-minbid { color: rgba(239,68,68,.8); font-size: 10px; margin-top: 2px; }
.sheet-bid-row { display: flex; gap: 8px; align-items: center; }
.sheet-bid-input { flex: 1; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.25); border-radius: 20px; padding: 10px 16px; color: #fff; font-size: 13px; outline: none; }
.sheet-bid-input::placeholder { color: rgba(255,255,255,.4); }
.sheet-bid-input:focus { border-color: rgba(255,255,255,.5); }
.sheet-confirm-btn { background: #ef4444; color: #fff; border: none; border-radius: 20px; padding: 10px 20px; font-weight: 700; font-size: 13px; cursor: pointer; white-space: nowrap; transition: opacity .15s; }
.sheet-confirm-btn:disabled { opacity: .6; }

/* ── Viewer shop sheet ── */
.viewer-shop-sheet { position: absolute; bottom: 0; left: 0; right: 0; z-index: 40; background: #1a1a1a; border-radius: 20px 20px 0 0; transform: translateY(100%); transition: transform .3s ease; max-height: 86dvh; display: flex; flex-direction: column; }
.viewer-shop-sheet.open { transform: translateY(0); }
.shop-header { padding: 16px 16px 0; flex-shrink: 0; }
.shop-search-row { display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,.07); border-radius: 30px; padding: 10px 16px; margin-bottom: 12px; }
.shop-search-row svg { flex-shrink: 0; color: rgba(255,255,255,.4); }
.shop-search-row input { flex: 1; background: transparent; border: none; outline: none; color: #fff; font-size: 14px; }
.shop-search-row input::placeholder { color: rgba(255,255,255,.35); }
.shop-close-btn { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,.12); border: none; color: rgba(255,255,255,.7); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.shop-filters { display: flex; gap: 8px; align-items: center; margin-bottom: 14px; }
.shop-filter-btn { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15); border-radius: 20px; color: rgba(255,255,255,.7); font-size: 12px; font-weight: 600; padding: 5px 14px; cursor: pointer; white-space: nowrap; }
.shop-filter-btn.active { background: #fff; color: #111; border-color: #fff; }
.shop-count { color: #fff; font-size: 16px; font-weight: 800; margin-bottom: 6px; }
.shop-disclaimer { display: flex; align-items: center; gap: 6px; background: rgba(234,179,8,.12); border: 1px solid rgba(234,179,8,.35); border-radius: 8px; padding: 7px 10px; color: rgba(234,179,8,.9); font-size: 11px; font-weight: 600; margin-bottom: 10px; line-height: 1.35; }
.bid-disclaimer { display: flex; align-items: center; justify-content: center; gap: 5px; background: rgba(234,179,8,.1); border: 1px solid rgba(234,179,8,.3); border-radius: 8px; padding: 6px 12px; color: rgba(234,179,8,.85); font-size: 11px; font-weight: 600; margin-top: 6px; }
.shop-body { overflow-y: auto; padding: 0 16px 24px; flex: 1; }
.shop-product-row { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,.07); }
.shop-product-row:last-child { border-bottom: none; }
.shop-img-wrap { position: relative; flex-shrink: 0; width: 110px; height: 110px; border-radius: 10px; overflow: hidden; background: #222; }
.shop-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
.shop-bell-badge { position: absolute; bottom: 6px; right: 6px; background: rgba(0,0,0,.7); border-radius: 20px; padding: 3px 8px; color: #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; gap: 3px; }
.shop-product-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.shop-product-name { color: #fff; font-size: 15px; font-weight: 700; }
.shop-product-price { color: #fff; font-size: 18px; font-weight: 800; }
.shop-bid-count { color: rgba(255,255,255,.45); font-size: 12px; }
.shop-pre-bid-btn { margin-top: auto; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); border-radius: 20px; color: #fff; font-size: 13px; font-weight: 700; padding: 9px 20px; cursor: pointer; text-align: center; }
.shop-pre-bid-btn.placed { background: rgba(251,191,36,.2); border-color: #fbbf24; color: #fbbf24; }

/* ── Pre-bid modal ── */
.pre-bid-backdrop { position: fixed; inset: 0; z-index: 400; background: rgba(0,0,0,.65); backdrop-filter: blur(4px); display: flex; align-items: flex-end; justify-content: center; }
.pre-bid-modal { width: 100%; max-width: 480px; background: #1c1c1e; border-radius: 24px 24px 0 0; padding: 28px 20px 36px; color: #fff; animation: sheetUp .3s ease; }
.pre-bid-title { font-size: 22px; font-weight: 900; text-align: center; margin-bottom: 10px; }
.pre-bid-desc { font-size: 13px; color: rgba(255,255,255,.55); text-align: center; line-height: 1.5; margin-bottom: 22px; }
.pre-bid-label { font-size: 14px; font-weight: 800; margin-bottom: 2px; }
.pre-bid-min { font-size: 12px; color: rgba(255,255,255,.45); margin-bottom: 10px; }
.pre-bid-input-wrap { background: rgba(255,255,255,.07); border: 1.5px solid rgba(255,255,255,.15); border-radius: 14px; padding: 14px 18px; font-size: 20px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 4px; margin-bottom: 8px; }
.pre-bid-input-wrap input { background: transparent; border: none; outline: none; color: #fff; font-size: 20px; font-weight: 700; flex: 1; width: 100%; }
.pre-bid-input-wrap span { color: rgba(255,255,255,.4); font-size: 16px; }
.pre-bid-shipping { text-align: center; color: rgba(255,255,255,.4); font-size: 12px; margin-bottom: 18px; }
.pre-bid-submit { width: 100%; padding: 15px; background: #fff; color: #111; border: none; border-radius: 16px; font-size: 16px; font-weight: 900; cursor: pointer; margin-bottom: 14px; }
.pre-bid-submit:disabled { opacity: .5; cursor: not-allowed; }
.pre-bid-note { font-size: 11px; color: rgba(255,255,255,.35); text-align: center; line-height: 1.5; }

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

/* ── Live Ended Overlay ── */
.live-ended-overlay {
    position: absolute; inset: 0; z-index: 30;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(180deg, #1c1c1c, #050505);
}
.ended-blur-bg {
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(circle at 30% 20%, rgba(255, 0, 80, .35), transparent 30%),
        radial-gradient(circle at 70% 60%, rgba(0, 180, 255, .25), transparent 35%);
    filter: blur(20px);
}
.ended-content {
    position: relative; z-index: 2;
    display: flex; flex-direction: column; align-items: center;
    padding: 24px; text-align: center; width: 100%; max-width: 340px;
}
.ended-avatar-ring {
    width: 96px; height: 96px; border-radius: 50%;
    background: linear-gradient(135deg, #ff0050, #00f2ea);
    padding: 4px; margin-bottom: 18px;
}
.ended-avatar-img {
    width: 100%; height: 100%; border-radius: 50%;
    object-fit: cover; background: #222;
}
.ended-heading {
    font-size: 26px; font-weight: 900; color: #fff; margin-bottom: 10px;
}
.ended-subtitle {
    color: #ccc; font-size: 15px; margin-bottom: 28px; line-height: 1.4;
}
.ended-stats {
    display: flex; gap: 14px; margin-bottom: 34px;
}
.ended-stat {
    background: rgba(255,255,255,.1); padding: 14px 18px;
    border-radius: 16px; min-width: 110px; text-align: center;
}
.ended-stat strong {
    display: block; font-size: 20px; font-weight: 800; color: #fff;
}
.ended-stat span {
    font-size: 12px; color: #bbb;
}
.ended-btn {
    width: 100%; border: none; border-radius: 999px;
    padding: 15px; font-size: 16px; font-weight: 800;
    cursor: pointer; margin-bottom: 12px;
    text-align: center; text-decoration: none; display: block;
    transition: opacity .2s;
}
.ended-btn:hover { opacity: .85; }
.ended-btn-primary {
    background: #ff0050; color: #fff;
}
.ended-btn-primary.following {
    background: rgba(255,255,255,.12); color: rgba(255,255,255,.8);
}
.ended-btn-secondary {
    background: rgba(255,255,255,.12); color: #fff;
}
.ended-thanks {
    font-size: 13px; color: #aaa; margin-top: 16px;
}
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
    @include('frontend.lives._screen', [
        'liveItem'       => $liveItem,
        'isSeller'       => $isSeller,
        'isFirst'        => $isFirst,
        'sellerProducts' => $isFirst ? $sellerProducts : collect(),
        'preBidCounts'   => $isFirst ? $preBidCounts : collect(),
        'userPreBids'    => $isFirst ? $userPreBids : collect(),
        'recentComments' => $isFirst ? $recentComments : collect(),
    ])
    @endforeach
</div>{{-- #live-feed --}}
</div>{{-- #live-feed-outer --}}

@auth
{{-- Pre-bid modal --}}
<div class="pre-bid-backdrop" id="pre-bid-backdrop" style="display:none;">
    <div class="pre-bid-modal">
        <div class="pre-bid-title">{{ __('Place your Bid') }}</div>
        <p class="pre-bid-desc" id="pre-bid-desc"></p>
        <div class="pre-bid-label">{{ __('Max Bid') }}</div>
        <div class="pre-bid-min" id="pre-bid-min"></div>
        <div class="pre-bid-input-wrap">
            <span>MAD</span>
            <input type="number" id="pre-bid-amount" min="1" step="1" placeholder="0">
        </div>
        <div class="pre-bid-shipping">{{ __('+ Shipping & Tax may apply') }}</div>
        <button class="pre-bid-submit" id="pre-bid-submit">{{ __('Submit') }}</button>
        <p class="pre-bid-note">{{ __("Bids are final. You can increase at any time, but cannot reduce or cancel your max bid.") }}</p>
        <button style="width:100%;background:transparent;border:none;color:rgba(255,255,255,.35);font-size:13px;cursor:pointer;padding:8px;" id="pre-bid-cancel">{{ __('Cancel') }}</button>
    </div>
</div>

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
                    } else {
                        alert(res.message || {!! json_encode(__('Top up failed. Please try again.')) !!});
                    }
                } catch (e) {
                    console.error('Top up failed', e);
                    alert({!! json_encode(__('Top up failed. Please try again.')) !!});
                }
                topupConfirmBtn.disabled = false;
                topupConfirmBtn.innerHTML = {!! json_encode(__('Confirm & Top Up')) !!};
            });
        }
        if (topupCancelBtn) {
            topupCancelBtn.addEventListener('click', () => { const r = topupResolve; hideTopupModal(); if (r) r(false); });
        }

        async function apiFetch(url, body) {
            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };
            try { const sid = window.Echo?.socketId(); if (sid) headers['X-Socket-ID'] = sid; } catch (e) { console.debug('Echo socket id unavailable', e); }
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
            if (st.client && st.localTracks.length) try { await st.client.publish(st.localTracks); } catch (e) { console.error('Failed to publish local tracks', e); }
        }

        async function triggerCloseAuction(screen) {
            const url = screen.dataset.closeAuctionUrl;
            if (!url) return;
            try {
                const res = await apiFetch(url, {});
                if (res && res.ok === false) console.error('Close auction rejected', res.message);
            } catch (e) {
                console.error('Failed to close auction', e);
            }
        }

        // ── Pusher + presence ─────────────────────────────────────────
        function bindPusherChannel(screen) {
            const id = screen.dataset.liveId;
            const ch = Echo.channel('live.' + id);

            if (IS_AUTH) {
                let viewerCount = 0;
                Echo.join('live.' + id)
                    .here(members => {
                        viewerCount = members.filter(m => !m.is_seller).length;
                        updateViewerDisplay(screen, viewerCount);
                    })
                    .joining(member => {
                        if (!member.is_seller) {
                            viewerCount++;
                            updateViewerDisplay(screen, viewerCount);
                            appendJoinedEvent(screen, member.username);
                        }
                    })
                    .leaving(member => {
                        if (!member.is_seller) {
                            viewerCount = Math.max(0, viewerCount - 1);
                            updateViewerDisplay(screen, viewerCount);
                        }
                    });
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
                    const disc = screen.querySelector('.js-bid-disclaimer');
                    if (disc) disc.style.display = '';
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
                const disc = screen.querySelector('.js-bid-disclaimer');
                if (disc) disc.style.display = 'none';
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

            ch.listen('LiveLiked', e => {
                const countEl = screen.querySelector('.js-likes-count');
                if (countEl) countEl.textContent = e.likes_count;
            });

            ch.listen('LiveStatusChanged', e => {
                if (e.status === 'live') {
                    screen.dataset.status = 'live';
                    const ltbSub = screen.querySelector('.ltb-sub');
                    if (ltbSub) ltbSub.innerHTML = '<span class="ltb-live-badge"><span class="ltb-live-dot"></span>LIVE</span>';
                } else if (e.status === 'ended') {
                    screen.dataset.status = 'ended';
                    // Show the ended overlay
                    const overlay = screen.querySelector('.js-ended-overlay');
                    if (overlay) {
                        overlay.style.display = '';
                        // Update likes count in the overlay
                        const likesEl = overlay.querySelector('.js-ended-likes');
                        const sidebarLikes = screen.querySelector('.js-likes-count');
                        if (likesEl && sidebarLikes) likesEl.textContent = sidebarLikes.textContent;
                    }
                    // Teardown Agora
                    teardownAgora(screen);
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
            st.localTracks.forEach(t => { try { t.stop(); t.close(); } catch (e) { console.warn('Failed to stop local track', e); } });
            try { await st.client.leave(); } catch (e) { console.warn('Failed to leave Agora channel', e); }
            st.client = null; st.localTracks = [];
        }

        // ── Pre-bid modal ─────────────────────────────────────────────
        const preBidBackdrop = document.getElementById('pre-bid-backdrop');
        const preBidAmount   = document.getElementById('pre-bid-amount');
        const preBidSubmit   = document.getElementById('pre-bid-submit');
        const preBidCancel   = document.getElementById('pre-bid-cancel');
        let preBidCallback   = null;

        function openPreBidModal(productId, productName, minBid, url, triggerBtn) {
            const desc = document.getElementById('pre-bid-desc');
            const min  = document.getElementById('pre-bid-min');
            if (desc) desc.textContent = 'You are entering a max bid for ' + productName + '. We\'ll automatically place bids for you up to that amount when this auction runs.';
            if (min)  min.textContent  = 'Enter a bid of at least ' + minBid.toFixed(2) + ' MAD';
            if (preBidAmount) { preBidAmount.value = ''; preBidAmount.min = minBid; }
            if (preBidBackdrop) preBidBackdrop.style.display = 'flex';
            preBidCallback = async () => {
                const amount = parseFloat(preBidAmount?.value);
                if (!amount || amount < minBid) { alert('Enter at least ' + minBid.toFixed(2) + ' MAD'); return; }
                preBidSubmit.disabled = true;
                try {
                    const res = await apiFetch(url, { product_id: productId, max_amount: amount });
                    if (res.ok) {
                        if (preBidBackdrop) preBidBackdrop.style.display = 'none';
                        if (triggerBtn) {
                            triggerBtn.textContent = '✓ ' + amount.toFixed(2) + ' MAD';
                            triggerBtn.classList.add('placed');
                        }
                        // Update bell badge count
                        const row = triggerBtn?.closest('.shop-product-row');
                        if (row) {
                            const badge = row.querySelector('.shop-bell-badge');
                            const bidCount = row.querySelector('.shop-bid-count');
                            if (badge) badge.textContent = '🔔 ' + res.pre_bid_count;
                            if (bidCount) bidCount.textContent = res.pre_bid_count + ' ' + (res.pre_bid_count === 1 ? 'bid' : 'bids');
                        }
                    } else {
                        alert(res.message || {!! json_encode(__('We could not place your bid. Please try again.')) !!});
                    }
                } catch (e) {
                    console.error('Pre-bid failed', e);
                    alert({!! json_encode(__('We could not place your bid. Please try again.')) !!});
                }
                preBidSubmit.disabled = false;
            };
        }

        if (preBidSubmit)  preBidSubmit.addEventListener('click', () => preBidCallback?.());
        if (preBidCancel)  preBidCancel.addEventListener('click', () => { if (preBidBackdrop) preBidBackdrop.style.display = 'none'; });
        if (preBidBackdrop) preBidBackdrop.addEventListener('click', e => { if (e.target === preBidBackdrop) preBidBackdrop.style.display = 'none'; });

        // ── Dynamic bottom stack ──────────────────────────────────────
        function bindBottomStack(screen) {
            const panel    = screen.querySelector('.bottom-auction-panel');
            const inputRow = screen.querySelector('.comment-input-row');
            const comments = screen.querySelector('.live-comments');
            const sidebar  = screen.querySelector('.live-sidebar-right');
            if (!panel) return;
            function update() {
                const panelH  = panel.offsetHeight;
                const inputH  = inputRow ? inputRow.offsetHeight : 50;
                const gap = 8;
                if (inputRow) inputRow.style.bottom = (panelH + gap) + 'px';
                if (comments) comments.style.bottom = (panelH + gap + inputH + 4) + 'px';
                if (sidebar)  sidebar.style.bottom  = (panelH + gap + inputH + 10) + 'px';
            }
            update();
            new ResizeObserver(update).observe(panel);
        }

        // ── Bind screen ───────────────────────────────────────────────
        function bindScreen(screen) {
            bindPusherChannel(screen);
            bindBottomStack(screen);
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

            // Ended overlay follow button
            const endedFollowBtn = screen.querySelector('.js-ended-follow');
            if (endedFollowBtn) {
                endedFollowBtn.addEventListener('click', async () => {
                    endedFollowBtn.disabled = true;
                    try {
                        const res = await apiFetch(endedFollowBtn.dataset.followUrl, {});
                        if (res.following) {
                            endedFollowBtn.textContent = {!! json_encode(__('Following')) !!};
                            endedFollowBtn.classList.add('following');
                        } else {
                            endedFollowBtn.textContent = {!! json_encode(__('Follow')) !!};
                            endedFollowBtn.classList.remove('following');
                        }
                    } catch (e) {
                        console.error('Follow request failed', e);
                    }
                    endedFollowBtn.disabled = false;
                });
            }

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
                if (st.client) st.client.remoteUsers.forEach(u => { try { u.audioTrack?.play(); } catch (e) { console.warn('Unable to play remote audio track', e); } });
                unmuteBtn.style.display = 'none';
            });

            // Follow / unfollow
            const followBtn = screen.querySelector('.js-follow-btn');
            if (followBtn && IS_AUTH) {
                followBtn.addEventListener('click', async () => {
                    const url = followBtn.dataset.followUrl;
                    if (!url) return;
                    followBtn.disabled = true;
                    try {
                        const res = await apiFetch(url, {});
                        const nowFollowing = res.following;
                        followBtn.dataset.isFollowing = nowFollowing ? '1' : '0';
                        followBtn.textContent = nowFollowing ? {!! json_encode(__('Following')) !!} : {!! json_encode(__('Follow')) !!};
                        followBtn.classList.toggle('following', nowFollowing);
                    } catch (e) {
                        console.error('Follow request failed', e);
                    }
                    followBtn.disabled = false;
                });
            }

            // Viewer shop sheet
            const viewerShopBtn  = screen.querySelector('.js-open-viewer-shop');
            const viewerSheet    = screen.querySelector('.js-viewer-shop-sheet');
            if (viewerShopBtn && viewerSheet) {
                viewerShopBtn.addEventListener('click', () => viewerSheet.classList.add('open'));
                viewerSheet.querySelector('.js-viewer-shop-close').addEventListener('click', () => viewerSheet.classList.remove('open'));
                viewerSheet.querySelector('.js-shop-search-input').addEventListener('input', e => {
                    const q = e.target.value.toLowerCase();
                    viewerSheet.querySelectorAll('.shop-product-row').forEach(row => {
                        row.style.display = row.querySelector('.shop-product-name').textContent.toLowerCase().includes(q) ? '' : 'none';
                    });
                });
                viewerSheet.querySelectorAll('.js-pre-bid-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const row = btn.closest('.shop-product-row');
                        openPreBidModal(row.dataset.productId, row.dataset.productName, parseFloat(row.dataset.minBid), viewerSheet.dataset.preBidUrl, btn);
                    });
                });
            }

            // Product sheet
            const sheetBtn     = screen.querySelector('.js-open-product-sheet');
            const sheet        = screen.querySelector('.js-product-sheet');
            if (sheetBtn && sheet) {
                sheetBtn.addEventListener('click', () => sheet.classList.add('open'));
                sheet.querySelector('.js-sheet-close').addEventListener('click', () => sheet.classList.remove('open'));
                sheet.querySelectorAll('.js-sheet-radio').forEach(r => {
                    r.addEventListener('change', () => {
                        const bidInput = sheet.querySelector('.js-sheet-bid');
                        if (bidInput && r.dataset.preBidMin) {
                            bidInput.value = parseFloat(r.dataset.preBidMin).toFixed(2);
                            bidInput.min = r.dataset.preBidMin;
                        }
                    });
                });
                sheet.querySelector('.js-sheet-confirm').addEventListener('click', async () => {
                    const checked = sheet.querySelector('.js-sheet-radio:checked');
                    const bidVal  = parseFloat(sheet.querySelector('.js-sheet-bid').value);
                    if (!checked) { alert({!! json_encode(__('Select a product first.')) !!}); return; }
                    const minBid = parseFloat(checked.dataset.preBidMin || 1);
                    if (!bidVal || bidVal < minBid) { alert({!! json_encode(__('Starting bid must be at least')) !!} + ' ' + minBid.toFixed(2) + ' MAD'); return; }
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
                    } catch (e) {
                        console.error('Failed to set live product', e);
                        alert({!! json_encode(__('Something went wrong. Please try again.')) !!});
                    }
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

        // ── Global lives-feed channel — inject new lives in real time ─
        const knownLiveIds = new Set(
            Array.from(document.querySelectorAll('.live-screen')).map(s => s.dataset.liveId)
        );
        Echo.channel('lives-feed').listen('LiveStatusChanged', async e => {
            if (!e.live_id || knownLiveIds.has(String(e.live_id))) return;
            knownLiveIds.add(String(e.live_id));
            try {
                const r = await fetch({!! json_encode(url('/lives')) !!} + '/' + e.live_id + '/fragment', {
                    headers: { 'Accept': 'text/html', 'X-CSRF-TOKEN': CSRF },
                    credentials: 'same-origin',
                });
                if (!r.ok) return;
                const html = await r.text();
                const tmp = document.createElement('div');
                tmp.innerHTML = html;
                const newScreen = tmp.querySelector('.live-screen');
                if (!newScreen) return;
                feed.appendChild(newScreen);
                observer.observe(newScreen);
                bindScreen(newScreen);
                bindBottomStack(newScreen);
            } catch (e) {
                console.error('Failed to load more lives', e);
            }
        });
    })();
});
</script>
@endsection
