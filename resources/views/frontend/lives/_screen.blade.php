{{--
    Variables expected:
    $liveItem        - App\Models\Live (with seller, product loaded)
    $isSeller        - bool
    $isFirst         - bool  (back button + initial comments)
    $sellerProducts  - Collection
    $preBidCounts    - Collection  (product_id => count)
    $userPreBids     - Collection  (product_id => max_amount)
    $recentComments  - Collection
--}}
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
                @php $isFollowing = Auth::user()->isFollowing($liveItem->seller); @endphp
                <button class="ltb-follow-btn js-follow-btn{{ $isFollowing ? ' following' : '' }}"
                        data-follow-url="{{ route('users.follow.toggle', $liveItem->seller) }}"
                        data-is-following="{{ $isFollowing ? '1' : '0' }}">
                    {{ $isFollowing ? __('Following') : __('Follow') }}
                </button>
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
        {{-- Shop button: viewers only --}}
        @if(!$isSeller)
        <div class="side-btn js-open-viewer-shop">
            <div class="side-icon-wrap js-shop-icon-wrap" style="font-size:22px;position:relative;">🛍️
                <span class="side-badge js-shop-count-badge">{{ $sellerProducts->count() }}</span>
            </div>
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
        <div class="js-bid-disclaimer bid-disclaimer" style="{{ $liveItem->auction_status !== 'active' ? 'display:none;' : '' }}">
            ⚠️ {{ __('Bid price excludes shipping & buyer protection fees') }}
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

    {{-- Viewer shop sheet --}}
    @if(!$isSeller)
    <div class="viewer-shop-sheet js-viewer-shop-sheet"
         data-pre-bid-url="{{ route('lives.pre-bid', $liveItem) }}">
        <div class="shop-header">
            <div class="sheet-handle"></div>
            <div class="shop-search-row">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                <input type="text" placeholder="{{ __('Search shop…') }}" class="js-shop-search-input">
                <button class="shop-close-btn js-viewer-shop-close">×</button>
            </div>
            <div class="shop-filters">
                <button class="shop-filter-btn active">{{ __('All') }}</button>
                <button class="shop-filter-btn">{{ __('Auction') }}</button>
                <button class="shop-filter-btn">{{ __('Sold') }}</button>
            </div>
            <div class="shop-count">{{ __('Products') }} ({{ $sellerProducts->count() }})</div>
            <div class="shop-disclaimer">
                ⚠️ {{ __('Prices shown exclude shipping & buyer protection fees') }}
            </div>
        </div>
        <div class="shop-body">
            @foreach($sellerProducts as $sp)
            @php
                $prebidCount = $preBidCounts[$sp->id] ?? 0;
                $myPreBid    = $userPreBids[$sp->id] ?? null;
                $preBidMin   = (float) ($sp->pivot->pre_bid_min ?? 1);
            @endphp
            <div class="shop-product-row"
                 data-product-id="{{ $sp->id }}"
                 data-product-name="{{ $sp->name }}"
                 data-min-bid="{{ $preBidMin }}"
                 data-prebid-count="{{ $prebidCount }}">
                <div class="shop-img-wrap">
                    <img src="{{ $sp->getFeaturedImageUrl('preview') }}" alt="">
                    <div class="shop-bell-badge">🔔 {{ $prebidCount }}</div>
                </div>
                <div class="shop-product-info">
                    <span class="shop-product-name">{{ $sp->name }}</span>
                    <span class="shop-product-price">{{ number_format($sp->price, 2) }} MAD</span>
                    <span class="shop-bid-count">{{ $prebidCount }} {{ $prebidCount === 1 ? __('bid') : __('bids') }}</span>
                    <span class="text-xs" style="color:rgba(255,255,255,.45);">{{ __('Min bid') }}: {{ number_format($preBidMin, 2) }} MAD</span>
                    <button class="shop-pre-bid-btn js-pre-bid-btn {{ $myPreBid ? 'placed' : '' }}">
                        {{ $myPreBid ? '✓ ' . number_format($myPreBid, 2) . ' MAD' : __('Pre-Bid') }}
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Product sheet (seller only) --}}
    @if($isSeller)
    <div class="product-sheet js-product-sheet">
        <div class="sheet-handle"></div>
        <p class="sheet-title">{{ __('Select Product for Auction') }}</p>
        <div class="sheet-product-grid">
            @foreach($sellerProducts as $sp)
            @php
                $preBidMin = (float) ($sp->pivot->pre_bid_min ?? 1);
                $img = $sp->getFeaturedImageUrl('preview');
            @endphp
            <label class="sheet-product-label">
                <input type="radio" name="sheet-product-{{ $liveItem->id }}" value="{{ $sp->id }}"
                       data-name="{{ $sp->name }}" data-price="{{ $sp->price }}"
                       data-img="{{ $img }}"
                       data-pre-bid-min="{{ $preBidMin }}"
                       class="js-sheet-radio sr-only">
                <div class="js-sheet-card sheet-product-card">
                    <div class="sheet-card-img-wrap">
                        @if($img)
                            <img src="{{ $img }}" alt="{{ $sp->name }}">
                        @else
                            <div class="sheet-card-no-img">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="sheet-card-check">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="sheet-card-info">
                        <p class="sheet-card-name">{{ $sp->name }}</p>
                        <p class="sheet-card-price">{{ number_format($sp->price, 2) }} MAD</p>
                        <p class="sheet-card-minbid">{{ __('Min bid') }}: {{ number_format($preBidMin, 2) }} MAD</p>
                    </div>
                </div>
            </label>
            @endforeach
        </div>
        <div class="sheet-bid-row">
            <input type="number" class="js-sheet-bid sheet-bid-input" min="1" step="1" placeholder="{{ __('Starting bid (MAD)') }}">
            <button class="js-sheet-confirm sheet-confirm-btn">
                {{ __('Start Auction') }}
            </button>
        </div>
        <button class="js-sheet-close" style="margin-top:10px;width:100%;background:rgba(255,255,255,.08);border:none;border-radius:20px;padding:10px;color:#fff;font-size:13px;cursor:pointer;">
            {{ __('Cancel') }}
        </button>
    </div>
    @endif

</div>
