<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Events\AuctionProductChanged;
use App\Events\BidPlaced;
use App\Events\CommentPosted;
use App\Events\LiveLiked;
use App\Events\LiveStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\LiveResource;
use App\Models\Live;
use App\Models\LivePreBid;
use App\Models\Product;
use App\Notifications\SellerWentLiveNotification;
use App\Services\LiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Modules\Wallet\Services\WalletService;
use Peterujah\Agora\Agora as AgoraClient;
use Peterujah\Agora\Builders\RtcToken;
use Peterujah\Agora\Roles;
use Peterujah\Agora\User as AgoraUser;

/**
 * Mobile API for the live-auction feature. Mirrors the web LiveController but
 * authenticates via Sanctum ($request->user()) and returns JSON.
 * Realtime updates ride the same public Reverb channel `live.{id}`.
 */
class LiveController extends Controller
{
    private const COUNTDOWN_SECONDS = 10;

    /** Live + scheduled sessions for the "Live" tab. */
    public function index(): AnonymousResourceCollection
    {
        $lives = Live::with(['seller', 'product'])
            ->whereIn('status', ['live', 'scheduled'])
            ->latest()
            ->get();

        return LiveResource::collection($lives);
    }

    /** Full detail for a single live (seller, current product, session products). */
    public function show(int $id): JsonResponse
    {
        $live = Live::with(['seller', 'product', 'currentBidder', 'liveProducts'])->findOrFail($id);

        return response()->json(new LiveResource($live));
    }

    // ---------------------------------------------------------------------
    // Seller ("go live") — mirrors the web App\Http\Controllers\LiveController
    // ---------------------------------------------------------------------

    /** The seller's approved products, for the create-live product picker. */
    public function sellerProducts(Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('vendor_id', $request->user()->id)
            ->where('status', 'approved')
            ->latest()
            ->get();

        return response()->json([
            'data' => $products->map(fn ($p) => [
                'id' => (int) $p->id,
                'title' => $p->name,
                'price' => (float) $p->price,
                'image' => $p->getFirstMediaUrl('featured', 'preview')
                    ?: $p->getFirstMediaUrl('products', 'preview')
                    ?: $p->getFirstMediaUrl('featured')
                    ?: $p->getFirstMediaUrl('products')
                    ?: null,
            ])->values(),
        ]);
    }

    /** Create a scheduled live with a cover image + curated products. */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'thumbnail' => 'required|image|max:8192',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'pre_bid_min' => 'required|array',
            'pre_bid_min.*' => 'numeric|min:1',
        ]);

        $userId = $request->user()->id;

        // Only the seller's own approved products may be listed in a live.
        $ownedApproved = Product::whereIn('id', $request->product_ids)
            ->where('vendor_id', $userId)
            ->where('status', 'approved')
            ->pluck('id')
            ->all();

        abort_if(count($ownedApproved) !== count($request->product_ids), 422, 'One or more products are not yours or not approved.');

        $path = $request->file('thumbnail')->store('lives/thumbnails', 'public');

        $live = Live::create([
            'seller_id' => $userId,
            'title' => $request->title,
            'thumbnail' => $path,
            'agora_channel' => 'live-'.Str::uuid(),
            'status' => 'scheduled',
            'auction_status' => 'idle',
            'starting_bid' => 0,
        ]);

        $pivotData = [];
        foreach ($request->product_ids as $productId) {
            $pivotData[$productId] = [
                'pre_bid_min' => (float) ($request->input("pre_bid_min.{$productId}") ?? 1),
            ];
        }
        $live->liveProducts()->attach($pivotData);

        $live->load(['seller', 'product', 'liveProducts']);

        return response()->json(new LiveResource($live), 201);
    }

    /** Go on air. */
    public function goLive(Request $request, int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        abort_if($live->seller_id !== $request->user()->id, 403);
        abort_if($live->status !== 'scheduled', 422, 'Live is not schedulable.');

        $live->update(['status' => 'live', 'started_at' => now()]);

        broadcast(new LiveStatusChanged($live))->toOthers();

        $live->load('seller');
        $live->seller->followers()->each(
            fn ($follower) => $follower->notify(new SellerWentLiveNotification($live))
        );

        return response()->json(['ok' => true]);
    }

    /** End the live (closing any active auction first). */
    public function endLive(Request $request, int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        abort_if($live->seller_id !== $request->user()->id, 403);
        abort_if($live->status !== 'live', 422, 'Live is not active.');

        if ($live->auction_status === 'active' && $live->current_bidder_id) {
            app(LiveService::class)->closeAuction($live);
        }

        $live->update(['status' => 'ended', 'ended_at' => now()]);

        broadcast(new LiveStatusChanged($live));

        return response()->json(['ok' => true]);
    }

    /** Put a session product up for auction (starts a fresh countdown-less auction). */
    public function setProduct(Request $request, int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        abort_if($live->seller_id !== $request->user()->id, 403);
        abort_if($live->status !== 'live', 422, 'Live is not active.');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'starting_bid' => 'required|numeric|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        abort_if($product->vendor_id !== $request->user()->id, 403);
        abort_if(! $live->liveProducts()->where('product_id', $product->id)->exists(), 422, 'Product not in live session.');

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

    /** Close the active auction (settle winner) — also fired by the seller's device at 0s. */
    public function closeAuction(Request $request, int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        abort_if($live->seller_id !== $request->user()->id, 403);
        abort_if($live->auction_status !== 'active', 422, 'No active auction.');

        $winner = app(LiveService::class)->closeAuction($live);

        return response()->json([
            'ok' => true,
            'winner_username' => $winner?->username,
            'winning_bid' => (float) $live->current_bid,
        ]);
    }

    /** Recent comments (oldest → newest) for initial render before realtime kicks in. */
    public function comments(int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        $comments = $live->comments()->with('user')->latest()->limit(50)->get()->reverse()->values();

        return response()->json([
            'data' => $comments->map(fn ($c) => [
                'id' => $c->id,
                'content' => $c->content,
                'username' => $c->user->username,
                'avatar_url' => $c->user->avatar_url ?? null,
            ]),
        ]);
    }

    /** Agora RTC token: publisher for the seller, subscriber for viewers. */
    public function agoraToken(Request $request, int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        $uid = $request->user()->id ?? 0;
        $expire = 3600;

        $client = new AgoraClient(config('agora.app_id'), config('agora.app_certificate'));
        $client->setExpiration($expire);

        $role = ($live->seller_id === $request->user()->id)
            ? Roles::RTC_PUBLISHER
            : Roles::RTC_SUBSCRIBER;

        $user = (new AgoraUser($uid))
            ->setPrivilegeExpire($expire)
            ->setChannel($live->agora_channel)
            ->setRole($role);

        return response()->json([
            'token' => RtcToken::buildTokenWithUid($client, $user),
            'channel' => $live->agora_channel,
            'uid' => $uid,
            'app_id' => config('agora.app_id'),
        ]);
    }

    /** Place a bid (wallet balance held, not debited). Resets the 10s countdown. */
    public function placeBid(Request $request, int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        $user = $request->user();

        abort_if($live->status !== 'live', 422, 'Auction is not active.');
        abort_if($live->auction_status !== 'active', 422, 'No active auction.');
        abort_if($live->seller_id === $user->id, 422, 'Seller cannot bid.');

        $minBid = $live->current_bid ? (float) $live->current_bid + 10 : (float) $live->starting_bid;
        $request->validate(['amount' => "required|numeric|min:{$minBid}"]);

        $amount = (float) $request->amount;
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
            'balance' => $balance,
        ]);
    }

    /** Post a live comment. */
    public function postComment(Request $request, int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        abort_if($live->status !== 'live', 422);

        $request->validate(['content' => 'required|string|max:200']);

        $comment = $live->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);
        $comment->load('user');

        broadcast(new CommentPosted($comment))->toOthers();

        return response()->json([
            'ok' => true,
            'id' => $comment->id,
            'content' => $comment->content,
            'username' => $comment->user->username,
            'avatar_url' => $comment->user->avatar_url ?? null,
        ]);
    }

    /** Like the live. */
    public function toggleLike(int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        $live->increment('likes_count');
        $live->refresh();

        broadcast(new LiveLiked($live))->toOthers();

        return response()->json(['ok' => true, 'likes_count' => $live->likes_count]);
    }

    /** Pre-bid a max amount on a session product before its auction starts. */
    public function preBid(Request $request, int $id): JsonResponse
    {
        $live = Live::findOrFail($id);
        $user = $request->user();
        abort_if($live->seller_id === $user->id, 422, 'Seller cannot pre-bid.');

        $request->validate(['product_id' => 'required|exists:products,id']);

        $liveProduct = $live->liveProducts()->where('product_id', $request->product_id)->first();
        abort_if(! $liveProduct, 422, 'Product not in live session.');

        $minBid = (float) $liveProduct->pivot->pre_bid_min;
        $request->validate(['max_amount' => "required|numeric|min:{$minBid}"]);

        LivePreBid::updateOrCreate(
            ['live_id' => $live->id, 'product_id' => $request->product_id, 'user_id' => $user->id],
            ['max_amount' => $request->max_amount]
        );

        $count = LivePreBid::where('live_id', $live->id)->where('product_id', $request->product_id)->count();

        return response()->json(['ok' => true, 'pre_bid_count' => $count]);
    }

    /** Wallet balance (for the bid UI). */
    public function balance(Request $request): JsonResponse
    {
        return response()->json([
            'balance' => app(WalletService::class)->getBalance($request->user()),
        ]);
    }

    /** Public realtime + video config for the app (Reverb client params, agora flag). */
    public function config(): JsonResponse
    {
        return response()->json([
            'reverb' => config('services.reverb_client'),
            'agora_enabled' => (bool) config('agora.app_id'),
        ]);
    }
}
