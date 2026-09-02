<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Modules\Chat\Enums\OfferStatus;
use Modules\Chat\Livewire\BundleBuilder;
use Modules\Chat\Livewire\ChatWindow;
use Modules\Chat\Livewire\MakeOfferModal;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Offer;
use Modules\Chat\Services\ChatService;
use Modules\Wallet\Services\WalletService;

pest()->use(RefreshDatabase::class);

function makeProduct(User $vendor, string $status = 'approved', float $price = 100): Product
{
    $cat = Category::firstOrCreate(['slug' => 'fix-cat'], ['name' => 'FixCat']);

    return Product::create([
        'name' => 'Item '.uniqid(),
        'description' => 'd',
        'price' => $price,
        'vendor_id' => $vendor->id,
        'category_id' => $cat->id,
        'status' => $status,
    ]);
}

beforeEach(function () {
    config(['broadcasting.default' => 'null']);
    Notification::fake();
});

// ---------- T1a: BundleBuilder must not accept foreign / unsellable products ----------

test('bundle request rejects products that do not belong to the vendor', function () {
    $vendor = User::factory()->create();
    $otherVendor = User::factory()->create();
    $buyer = User::factory()->create();

    $own = makeProduct($vendor);
    $foreign = makeProduct($otherVendor);

    $this->actingAs($buyer);

    Livewire::test(BundleBuilder::class, ['vendor' => $vendor])
        ->call('toggleProduct', $own->id)
        ->call('toggleProduct', $foreign->id)
        ->call('sendRequest');

    expect(Offer::count())->toBe(0);
});

test('bundle request rejects sold products', function () {
    $vendor = User::factory()->create();
    $buyer = User::factory()->create();
    $sold = makeProduct($vendor, 'sold');

    $this->actingAs($buyer);

    Livewire::test(BundleBuilder::class, ['vendor' => $vendor])
        ->call('toggleProduct', $sold->id)
        ->call('sendRequest');

    expect(Offer::count())->toBe(0);
});

test('bundle request still works for the vendors own approved products', function () {
    $vendor = User::factory()->create();
    $buyer = User::factory()->create();
    $p1 = makeProduct($vendor);
    $p2 = makeProduct($vendor);

    $this->actingAs($buyer);

    Livewire::test(BundleBuilder::class, ['vendor' => $vendor])
        ->call('toggleProduct', $p1->id)
        ->call('toggleProduct', $p2->id)
        ->call('sendRequest');

    expect(Offer::count())->toBe(1);
    expect(Offer::first()->status)->toBe(OfferStatus::Accepted);
    expect(Offer::first()->items()->count())->toBe(2);
});

test('vendor cannot bundle-buy from themselves', function () {
    $vendor = User::factory()->create();
    $p = makeProduct($vendor);

    $this->actingAs($vendor);

    Livewire::test(BundleBuilder::class, ['vendor' => $vendor])
        ->call('toggleProduct', $p->id)
        ->call('sendRequest');

    expect(Offer::count())->toBe(0);
});

// ---------- T1b: reuploadItem ownership ----------

test('buyer cannot relist the sellers product via reuploadItem', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $product = makeProduct($seller, 'sold');

    $conversation = Conversation::create([
        'user_one_id' => min($seller->id, $buyer->id),
        'user_two_id' => max($seller->id, $buyer->id),
        'product_id' => $product->id,
    ]);

    $this->actingAs($buyer);
    Livewire::test(ChatWindow::class, ['conversationId' => $conversation->id])
        ->call('reuploadItem');

    expect($product->fresh()->status)->toBe('sold');
});

test('seller can relist their own product via reuploadItem', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $product = makeProduct($seller, 'sold');

    $conversation = Conversation::create([
        'user_one_id' => min($seller->id, $buyer->id),
        'user_two_id' => max($seller->id, $buyer->id),
        'product_id' => $product->id,
    ]);

    $this->actingAs($seller);
    Livewire::test(ChatWindow::class, ['conversationId' => $conversation->id])
        ->call('reuploadItem');

    expect($product->fresh()->status)->toBe('approved');
});

// ---------- T1c: self-offer guard ----------

test('owner cannot submit an offer on their own product', function () {
    $vendor = User::factory()->create();
    $product = makeProduct($vendor);

    $this->actingAs($vendor);

    Livewire::test(MakeOfferModal::class)
        ->set('product', $product)
        ->set('offerPrice', 50)
        ->call('submitOffer');

    expect(Offer::count())->toBe(0);
});

test('openModal refuses to open for the products owner', function () {
    $vendor = User::factory()->create();
    $product = makeProduct($vendor);

    $this->actingAs($vendor);

    Livewire::test(MakeOfferModal::class)
        ->call('openModal', $product->id)
        ->assertSet('showModal', false);
});

test('chat service refuses self-conversations', function () {
    $user = User::factory()->create();

    expect(fn () => app(ChatService::class)->getOrCreateConversation($user, $user))
        ->toThrow(\Exception::class, 'Cannot start a conversation with yourself.');
});

// ---------- T1d: single payout via OrderObserver ----------

function makeOrder(User $buyer, User $vendor, Product $product, string $paymentMethod = 'wallet', string $status = 'shipped'): Order
{
    return Order::create([
        'user_id' => $buyer->id,
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
        'amount' => 100,
        'shipping_cost' => 25,
        'buyer_protection_fee' => 5.7,
        'platform_commission' => 10,
        'total_amount' => 130.7,
        'status' => $status,
        'payment_method' => $paymentMethod,
    ]);
}

test('completing a wallet order releases escrow exactly once', function () {
    $vendor = User::factory()->create();
    $buyer = User::factory()->create();
    $product = makeProduct($vendor, 'sold');
    $order = makeOrder($buyer, $vendor, $product); // payout = 100 - 10 = 90

    $wallet = app(WalletService::class)->getWallet($vendor);
    $wallet->update(['pending_balance' => 200]); // 90 from this order + 110 other pending

    $order->update(['status' => 'completed']);

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(90.0);
    expect((float) $wallet->pending_balance)->toBe(110.0);
    expect($wallet->transactions()->where('type', 'sale_released')->count())->toBe(1);

    // Re-saving in a final state must not release again.
    $order->refresh()->update(['status' => 'completed', 'received_at' => now()]);
    expect((float) $wallet->fresh()->balance)->toBe(90.0);
});

test('completing a cod order credits the seller directly, no escrow', function () {
    $vendor = User::factory()->create();
    $buyer = User::factory()->create();
    $product = makeProduct($vendor, 'sold');
    $order = makeOrder($buyer, $vendor, $product, 'cod');

    $order->update(['status' => 'completed']);

    $wallet = app(WalletService::class)->getWallet($vendor);
    expect((float) $wallet->balance)->toBe(90.0);
    expect((float) $wallet->pending_balance)->toBe(0.0);
    expect($wallet->transactions()->where('type', 'sale')->count())->toBe(1);
});

test('mobile receiveOrder pays the seller exactly once and succeeds', function () {
    $vendor = User::factory()->create();
    $buyer = User::factory()->create();
    $product = makeProduct($vendor, 'sold');
    $order = makeOrder($buyer, $vendor, $product);

    $wallet = app(WalletService::class)->getWallet($vendor);
    $wallet->update(['pending_balance' => 90]);

    Sanctum::actingAs($buyer);
    $this->postJson("/api/mobile/orders/{$order->id}/receive")->assertOk();

    $wallet->refresh();
    expect($order->fresh()->status)->toBe('completed');
    expect((float) $wallet->balance)->toBe(90.0);
    expect((float) $wallet->pending_balance)->toBe(0.0);

    // A retry cannot pay again (order no longer 'shipped').
    $this->postJson("/api/mobile/orders/{$order->id}/receive")->assertStatus(422);
    expect((float) $wallet->fresh()->balance)->toBe(90.0);
});
