<?php

namespace App\Http\Controllers;

use App\Events\AuctionClosed;
use App\Notifications\SellerWentLiveNotification;
use App\Events\AuctionProductChanged;
use App\Events\BidPlaced;
use App\Events\CommentPosted;
use App\Events\LiveLiked;
use App\Events\LiveStatusChanged;
use App\Models\Live;
use App\Models\LivePreBid;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Chat\Services\ChatService;
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
        $user = Auth::user();

        // Require at least one shipping address before watching
        if ($user->addresses()->doesntExist()) {
            return redirect()->route('settings.postage', [
                'redirect_to' => route('lives.show', $live),
            ])->with('info', __('Please add a shipping address before watching a live auction.'));
        }

        $isSeller = $user->id === $live->seller_id;

        // Sellers only see their own live — no scroll to other lives
        if ($isSeller) {
            $orderedLives = collect([$live->load(['seller', 'product'])]);
        } else {
            $allLives = Live::with(['seller', 'product'])
                ->whereIn('status', ['live', 'scheduled'])
                ->latest()
                ->get();
            $orderedLives = $allLives->sortBy(fn ($l) => $l->id === $live->id ? 0 : 1)->values();
        }

        $recentComments = $live->comments()->with('user')->latest()->limit(50)->get()->reverse()->values();

        // Only products curated for this live session
        $sellerProducts = $live->liveProducts()->get();

        // Pre-bid counts per product for this live
        $preBidCounts = LivePreBid::where('live_id', $live->id)
            ->selectRaw('product_id, count(*) as cnt')
            ->groupBy('product_id')
            ->pluck('cnt', 'product_id');

        // Current user's existing pre-bids for this live
        $userPreBids = LivePreBid::where('live_id', $live->id)
            ->where('user_id', $user->id)
            ->pluck('max_amount', 'product_id');

        return view('frontend.lives.show', compact(
            'live',
            'orderedLives',
            'recentComments',
            'sellerProducts',
            'preBidCounts',
            'userPreBids',
        ));
    }

    public function fragment(Live $live)
    {
        $user = Auth::user();
        $isSeller = $user->id === $live->seller_id;

        $sellerProducts = $live->liveProducts()->get();

        $preBidCounts = LivePreBid::where('live_id', $live->id)
            ->selectRaw('product_id, count(*) as cnt')
            ->groupBy('product_id')
            ->pluck('cnt', 'product_id');

        $userPreBids = LivePreBid::where('live_id', $live->id)
            ->where('user_id', $user->id)
            ->pluck('max_amount', 'product_id');

        $liveItem = $live->load(['seller', 'product']);
        $isFirst = false;
        $recentComments = collect();

        return view('frontend.lives._screen', compact(
            'liveItem',
            'isSeller',
            'isFirst',
            'sellerProducts',
            'preBidCounts',
            'userPreBids',
            'recentComments',
        ));
    }

    public function create()
    {
        $products = Product::where('vendor_id', Auth::id())
            ->where('status', 'approved')
            ->get();

        return view('frontend.lives.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'thumbnail' => 'required|image|max:4096',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'pre_bid_min' => 'required|array',
            'pre_bid_min.*' => 'numeric|min:1',
        ]);

        $path = $request->file('thumbnail')->store('lives/thumbnails', 'public');

        $live = Live::create([
            'seller_id' => Auth::id(),
            'title' => $request->title,
            'thumbnail' => $path,
            'agora_channel' => 'live-' . Str::uuid(),
            'status' => 'scheduled',
            'auction_status' => 'idle',
            'starting_bid' => 0,
        ]);

        // Attach selected products with their pre-bid minimums
        $pivotData = [];
        foreach ($request->product_ids as $productId) {
            $pivotData[$productId] = [
                'pre_bid_min' => (float) ($request->input("pre_bid_min.{$productId}") ?? 1),
            ];
        }
        $live->liveProducts()->attach($pivotData);

        return redirect()->route('lives.show', $live);
    }

    public function goLive(Live $live)
    {
        abort_if($live->seller_id !== Auth::id(), 403);
        abort_if($live->status !== 'scheduled', 422);

        $live->update(['status' => 'live', 'started_at' => now()]);

        broadcast(new LiveStatusChanged($live))->toOthers();

        // Notify all followers of the seller
        $live->load('seller');
        $live->seller->followers()->each(
            fn ($follower) => $follower->notify(new SellerWentLiveNotification($live))
        );

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

        // Balance must cover the bid amount (funds are held, not debited yet)
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

        $request->validate(['product_id' => 'required|exists:products,id']);

        $liveProduct = $live->liveProducts()->where('product_id', $request->product_id)->first();
        abort_if(! $liveProduct, 422, 'Product not in live session.');

        $minBid = (float) $liveProduct->pivot->pre_bid_min;
        $request->validate(['max_amount' => "required|numeric|min:{$minBid}"]);

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
        $live->refresh();

        broadcast(new LiveLiked($live))->toOthers();

        return response()->json(['ok' => true, 'likes_count' => $live->likes_count]);
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
        $product = $live->product;

        if ($product && $winner) {
            DB::transaction(function () use ($live, $winner, $product) {
                $bidAmount = (float) $live->current_bid;

                // --- Fee calculation (mirrors CheckoutController) ---
                $shippingCost = (float) config('settings.delivery_fee_fixed', 25.00);
                $buyerProtectionPct = (float) config('settings.buyer_protection_fee_percentage', 5);
                $buyerProtectionFixed = (float) config('settings.buyer_protection_fee_fixed', 0.70);
                $platformCommissionPct = (float) config('settings.platform_commission_percentage', 0);

                $buyerProtectionFee = ($bidAmount * ($buyerProtectionPct / 100)) + $buyerProtectionFixed;
                $platformCommission = $bidAmount * ($platformCommissionPct / 100);
                $totalAmount = $bidAmount + $shippingCost + $buyerProtectionFee;
                $platformRevenue = $buyerProtectionFee + $platformCommission;
                $vendorPayout = $bidAmount - $platformCommission;

                // --- Wallet: move funds to escrow ---
                app(WalletService::class)->payToEscrow(
                    $winner,
                    $product->vendor,
                    $totalAmount,
                    $vendorPayout,
                    "Live auction #{$live->id}",
                    $platformRevenue
                );

                // --- Create Order ---
                $winnerAddress = $winner->addresses()->first();

                $order = Order::create([
                    'user_id' => $winner->id,
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'amount' => $bidAmount,
                    'shipping_cost' => $shippingCost,
                    'buyer_protection_fee' => $buyerProtectionFee,
                    'platform_commission' => $platformCommission,
                    'total_amount' => $totalAmount,
                    'status' => 'processing',
                    'payment_method' => 'wallet',
                    'address_id' => $winnerAddress?->id,
                    'wants_verification' => false,
                    'verification_fee' => 0,
                    'source' => 'live',
                ]);

                // --- Order item + mark product sold ---
                $order->items()->create([
                    'product_id' => $product->id,
                    'price' => $bidAmount,
                ]);

                $product->update(['status' => 'sold']);

                // --- Chat: notify both parties ---
                $chatService = app(ChatService::class);
                $conversation = $chatService->getOrCreateConversation($winner, $product->vendor, $product);

                $chatService->sendItemSoldMessage($conversation, $winner, $order);
                $chatService->sendOrderPlacedMessage($conversation, $winner, $order);
            });
        }

        $live->update(['auction_status' => 'idle']);

        broadcast(new AuctionClosed($live));

        return $winner;
    }
}
