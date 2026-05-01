<?php

namespace App\Http\Controllers;

use App\Events\BidPlaced;
use App\Events\LiveStatusChanged;
use App\Models\Live;
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
        $live->load(['seller', 'product', 'bids.user', 'currentBidder']);

        return view('frontend.lives.show', compact('live'));
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
            'product_id' => 'required|exists:products,id',
            'starting_bid' => 'required|numeric|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        abort_if($product->vendor_id !== Auth::id(), 403);

        $live = Live::create([
            'seller_id' => Auth::id(),
            'product_id' => $request->product_id,
            'title' => $request->title,
            'agora_channel' => 'live-' . Str::uuid(),
            'status' => 'scheduled',
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

        $live->update(['status' => 'ended', 'ended_at' => now()]);

        if ($live->product && $live->current_bidder_id) {
            $live->product->update([
                'status' => 'reserved',
                'reserved_by_user_id' => $live->current_bidder_id,
            ]);
        }

        broadcast(new LiveStatusChanged($live));

        return response()->json([
            'ok' => true,
            'winner' => $live->currentBidder?->username,
            'winning_bid' => $live->current_bid,
        ]);
    }

    public function placeBid(Request $request, Live $live)
    {
        abort_if(! Auth::check(), 401);
        abort_if($live->status !== 'live', 422, 'Auction is not active.');
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
}
