<?php

namespace App\Http\Controllers;

use App\Events\AuctionClosed;
use App\Events\AuctionProductChanged;
use App\Events\BidPlaced;
use App\Events\CommentPosted;
use App\Events\LiveStatusChanged;
use App\Models\Live;
use App\Models\LiveLike;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

        $sellerProducts = Auth::id() === $live->seller_id
            ? Product::where('vendor_id', Auth::id())
                ->where('status', 'approved')
                ->with('images')
                ->get()
            : collect();

        return view('frontend.lives.show', compact('live', 'orderedLives', 'recentComments', 'hasLiked', 'sellerProducts'));
    }

    public function create()
    {
        $products = Product::where('vendor_id', Auth::id())
            ->where('status', 'approved')
            ->with('images')
            ->get();

        return view('frontend.lives.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'product_id' => 'nullable|exists:products,id',
            'starting_bid' => 'required|numeric|min:1',
        ]);

        if ($request->product_id) {
            $product = Product::findOrFail($request->product_id);
            abort_if($product->vendor_id !== Auth::id(), 403);
        }

        $live = Live::create([
            'seller_id' => Auth::id(),
            'product_id' => $request->product_id,
            'title' => $request->title,
            'agora_channel' => 'live-' . Str::uuid(),
            'status' => 'scheduled',
            'auction_status' => 'idle',
            'starting_bid' => $request->starting_bid,
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
        $countdownEndsAt = now()->addSeconds(self::COUNTDOWN_SECONDS);

        $live->update([
            'current_bid' => $amount,
            'current_bidder_id' => Auth::id(),
            'countdown_ends_at' => $countdownEndsAt,
        ]);

        $live->bids()->create(['user_id' => Auth::id(), 'amount' => $amount]);

        broadcast(new BidPlaced($live, Auth::user(), $amount, $countdownEndsAt->toISOString()));

        return response()->json([
            'ok' => true,
            'current_bid' => $amount,
            'countdown_ends_at' => $countdownEndsAt->toISOString(),
        ]);
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

        $existing = LiveLike::where('live_id', $live->id)->where('user_id', Auth::id())->first();

        if ($existing) {
            $existing->delete();
            $live->decrement('likes_count');
            $liked = false;
        } else {
            LiveLike::create(['live_id' => $live->id, 'user_id' => Auth::id()]);
            $live->increment('likes_count');
            $liked = true;
        }

        return response()->json(['ok' => true, 'liked' => $liked, 'likes_count' => $live->fresh()->likes_count]);
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
