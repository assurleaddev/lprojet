<?php

namespace App\Http\Controllers;

use App\Events\AuctionClosed;
use App\Events\AuctionProductChanged;
use App\Events\BidPlaced;
use App\Events\CommentPosted;
use App\Events\LiveStatusChanged;
use App\Models\Live;
use App\Models\LiveLike;
use App\Models\LivePreBid;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Wallet\Services\WalletService;
use Peterujah\Agora\Agora as AgoraClient;
use Peterujah\Agora\Builders\RtcToken;
use Peterujah\Agora\Roles;
use Peterujah\Agora\User as AgoraUser;

class LiveController extends Controller
{
    private const COUNTDOWN_SECONDS = 10;

    public function index()
    {
        $lives = Live::with(['seller', 'product'])
            ->whereIn('status', ['live', 'scheduled'])
            ->latest()
            ->get();

        return view('frontend.lives.index', compact('lives'));
    }

    public function show(Live $live)
    {
        // Load all active/scheduled lives for the scroll feed, starting from the requested one
        $allLives = Live::with(['seller', 'product'])
            ->whereIn('status', ['live', 'scheduled'])
            ->latest()
            ->get();

        // Put the requested live first
        $orderedLives = $allLives->sortBy(fn ($l) => $l->id === $live->id ? 0 : 1)->values();

        $recentComments = $live->comments()->with('user')->latest()->limit(50)->get()->reverse()->values();

        $hasLiked = Auth::check()
            ? LiveLike::where('live_id', $live->id)->where('user_id', Auth::id())->exists()
            : false;

        // Seller's approved products (used by seller sheet AND viewer shop)
        $sellerProducts = Product::where('vendor_id', $live->seller_id)
            ->where('status', 'approved')
            ->with('images')
            ->get();

        // Pre-bid counts per product for this live
        $preBidCounts = LivePreBid::where('live_id', $live->id)
            ->selectRaw('product_id, count(*) as cnt')
            ->groupBy('product_id')
            ->pluck('cnt', 'product_id');

        // Current user's existing pre-bids for this live
        $userPreBids = Auth::check()
            ? LivePreBid::where('live_id', $live->id)->where('user_id', Auth::id())->pluck('max_amount', 'product_id')
            : collect();

        return view('frontend.lives.show', compact('live', 'orderedLives', 'recentComments', 'hasLiked', 'sellerProducts', 'preBidCounts', 'userPreBids'));
    }

    public function create()
    {
        return view('frontend.lives.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
        ]);

        $live = Live::create([
            'seller_id' => Auth::id(),
            'title' => $request->title,
            'agora_channel' => 'live-' . Str::uuid(),
            'status' => 'scheduled',
            'auction_status' => 'idle',
            'starting_bid' => 0,
        ]);

        return redirect()->route('lives.show', $live);
    }

    public function goLive(Live $live)
    {
        abort_if($live->seller_id !== Auth::id(), 403);
        abort_if($live->status !== 'scheduled', 422);

        $live->update(['status' => 'live', 'started_at' => now()]);

        broadcast(new LiveStatusChanged($live))->toOthers();

        return response()->json(['ok' => true]);
    }

    public function endLive(Live $live)
    {
        abort_if($live->seller_id !== Auth::id(), 403);
        abort_if($live->status !== 'live', 422);

        // Close any active auction first
        if ($live->auction_status === 'active' && $live->current_bidder_id) {
            $this->closeAuctionInternal($live);
        }

        $live->update(['status' => 'ended', 'ended_at' => now()]);

        broadcast(new LiveStatusChanged($live));

        return response()->json(['ok' => true]);
    }

    public function placeBid(Request $request, Live $live)
    {
        abort_if(! Auth::check(), 401);
        abort_if($live->status !== 'live', 422, 'Auction is not active.');
        abort_if($live->auction_status !== 'active', 422, 'No active auction.');
        abort_if($live->seller_id === Auth::id(), 422, 'Seller cannot bid.');

        $minBid = $live->current_bid
            ? (float) $live->current_bid + 10
            : (float) $live->starting_bid;

        $request->validate(['amount' => "required|numeric|min:{$minBid}"]);

        $amount = (float) $request->amount;
        $user = Auth::user();
        $wallet = app(WalletService::class);
        $balance = $wallet->getBalance($user);

        if ($balance < $amount) {
            return response()->json([
                'ok' => false,
                'insufficient_balance' => true,
                'balance' => $balance,
                'required' => $amount,
                'shortfall' => round($amount - $balance, 2),
            ], 422);
        }

        $countdownEndsAt = now()->addSeconds(self::COUNTDOWN_SECONDS);

        $wallet->debit($user, $amount, 'bid', "Bid on live #{$live->id}", (string) $live->id);

        // Refund previous highest bidder if different
        if ($live->current_bidder_id && $live->current_bidder_id !== $user->id) {
            $previousBidder = \App\Models\User::find($live->current_bidder_id);
            if ($previousBidder) {
                $wallet->credit($previousBidder, (float) $live->current_bid, 'refund', "Outbid refund on live #{$live->id}", (string) $live->id);
            }
        }

        $live->update([
            'current_bid' => $amount,
            'current_bidder_id' => $user->id,
            'countdown_ends_at' => $countdownEndsAt,
        ]);

        $live->bids()->create(['user_id' => $user->id, 'amount' => $amount]);

        broadcast(new BidPlaced($live, $user, $amount, $countdownEndsAt->toISOString()))->toOthers();

        return response()->json([
            'ok' => true,
            'current_bid' => $amount,
            'countdown_ends_at' => $countdownEndsAt->toISOString(),
            'balance' => $wallet->getBalance($user),
        ]);
    }

    public function topUpBalance(Request $request)
    {
        abort_if(! Auth::check(), 401);

        $request->validate(['amount' => 'required|numeric|min:1|max:10000']);

        $amount = (float) $request->amount;
        $wallet = app(WalletService::class);

        // Simulate card processing delay
        sleep(1);

        $wallet->credit(Auth::user(), $amount, 'deposit', 'Top-up via saved card');

        return response()->json([
            'ok' => true,
            'balance' => $wallet->getBalance(Auth::user()),
        ]);
    }

    public function getBalance()
    {
        abort_if(! Auth::check(), 401);

        return response()->json([
            'balance' => app(WalletService::class)->getBalance(Auth::user()),
        ]);
    }

    public function preBid(Request $request, Live $live)
    {
        abort_if(! Auth::check(), 401);
        abort_if($live->seller_id === Auth::id(), 422, 'Seller cannot pre-bid.');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'max_amount' => 'required|numeric|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        abort_if($product->vendor_id !== $live->seller_id, 422);

        LivePreBid::updateOrCreate(
            ['live_id' => $live->id, 'product_id' => $request->product_id, 'user_id' => Auth::id()],
            ['max_amount' => $request->max_amount]
        );

        $count = LivePreBid::where('live_id', $live->id)->where('product_id', $request->product_id)->count();

        return response()->json(['ok' => true, 'pre_bid_count' => $count]);
    }

    public function closeAuction(Live $live)
    {
        abort_if($live->seller_id !== Auth::id(), 403);
        abort_if($live->auction_status !== 'active', 422);

        $winner = $this->closeAuctionInternal($live);

        return response()->json([
            'ok' => true,
            'winner_username' => $winner?->username,
            'winning_bid' => (float) $live->current_bid,
        ]);
    }

    public function setProduct(Request $request, Live $live)
    {
        abort_if($live->seller_id !== Auth::id(), 403);
        abort_if($live->status !== 'live', 422);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'starting_bid' => 'required|numeric|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        abort_if($product->vendor_id !== Auth::id(), 403);

        // Refund current bidder if switching products mid-auction
        if ($live->auction_status === 'active' && $live->current_bidder_id && $live->current_bid) {
            $previousBidder = \App\Models\User::find($live->current_bidder_id);
            if ($previousBidder) {
                app(WalletService::class)->credit($previousBidder, (float) $live->current_bid, 'refund', "Product changed on live #{$live->id}", (string) $live->id);
            }
        }

        $live->update([
            'product_id' => $request->product_id,
            'starting_bid' => $request->starting_bid,
            'current_bid' => null,
            'current_bidder_id' => null,
            'countdown_ends_at' => null,
            'auction_status' => 'active',
        ]);

        $live->load('product');

        broadcast(new AuctionProductChanged($live));

        return response()->json(['ok' => true]);
    }

    public function postComment(Request $request, Live $live)
    {
        abort_if(! Auth::check(), 401);
        abort_if($live->status !== 'live', 422);

        $request->validate(['content' => 'required|string|max:200']);

        $comment = $live->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        $comment->load('user');

        broadcast(new CommentPosted($comment))->toOthers();

        return response()->json([
            'ok' => true,
            'id' => $comment->id,
            'content' => $comment->content,
            'username' => $comment->user->username,
            'avatar_url' => $comment->user->avatar_url,
        ]);
    }

    public function toggleLike(Live $live)
    {
        abort_if(! Auth::check(), 401);

        $live->increment('likes_count');

        return response()->json(['ok' => true, 'likes_count' => $live->fresh()->likes_count]);
    }

    public function agoraToken(Live $live)
    {
        $uid = Auth::id() ?? 0;
        $expire = 3600;

        $client = new AgoraClient(config('agora.app_id'), config('agora.app_certificate'));
        $client->setExpiration($expire);

        $role = ($live->seller_id === Auth::id())
            ? Roles::RTC_PUBLISHER
            : Roles::RTC_SUBSCRIBER;

        $user = (new AgoraUser($uid))
            ->setPrivilegeExpire($expire)
            ->setChannel($live->agora_channel)
            ->setRole($role);

        $token = RtcToken::buildTokenWithUid($client, $user);

        return response()->json([
            'token' => $token,
            'channel' => $live->agora_channel,
            'uid' => $uid,
            'app_id' => config('agora.app_id'),
        ]);
    }

    private function closeAuctionInternal(Live $live): ?object
    {
        $winner = $live->currentBidder;

        if ($live->product && $winner) {
            $live->product->update([
                'status' => 'reserved',
                'reserved_by_user_id' => $winner->id,
            ]);
        }

        $live->update(['auction_status' => 'idle']);

        broadcast(new AuctionClosed($live));

        return $winner;
    }
}
