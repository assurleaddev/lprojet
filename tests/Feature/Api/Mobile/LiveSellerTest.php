<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Live;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    // Keep broadcasts inert in tests (no Reverb/Pusher connection).
    config(['broadcasting.default' => 'null']);
    Storage::fake('public');

    $this->seller = User::factory()->create();
});

function approvedProduct(User $seller, float $price = 100): Product
{
    $category = Category::create([
        'name' => 'Cat '.uniqid(),
        'slug' => 'cat-'.uniqid(),
    ]);

    return Product::create([
        'name' => 'Item '.uniqid(),
        'description' => 'desc',
        'price' => $price,
        'vendor_id' => $seller->id,
        'category_id' => $category->id,
        'status' => 'approved',
    ]);
}

function scheduledLive(User $seller, array $attrs = []): Live
{
    return Live::create(array_merge([
        'seller_id' => $seller->id,
        'title' => 'My live',
        'thumbnail' => 'lives/thumbnails/x.jpg',
        'agora_channel' => 'live-'.uniqid(),
        'status' => 'scheduled',
        'auction_status' => 'idle',
        'starting_bid' => 0,
    ], $attrs));
}

test('seller can create a scheduled live with curated products', function () {
    Sanctum::actingAs($this->seller);
    $p1 = approvedProduct($this->seller);
    $p2 = approvedProduct($this->seller);

    $response = $this->postJson('/api/mobile/lives', [
        'title' => 'Friday drop',
        'thumbnail' => UploadedFile::fake()->image('cover.jpg', 600, 600),
        'product_ids' => [$p1->id, $p2->id],
        'pre_bid_min' => [$p1->id => 20, $p2->id => 35],
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'scheduled')
        ->assertJsonPath('title', 'Friday drop');

    $live = Live::first();
    expect($live->liveProducts()->count())->toBe(2);
    expect((float) $live->liveProducts()->where('product_id', $p2->id)->first()->pivot->pre_bid_min)->toBe(35.0);
});

test('create live validates required fields', function () {
    Sanctum::actingAs($this->seller);

    $this->postJson('/api/mobile/lives', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'thumbnail', 'product_ids', 'pre_bid_min']);
});

test('create live rejects products the seller does not own', function () {
    Sanctum::actingAs($this->seller);
    $foreign = approvedProduct(User::factory()->create());

    $this->postJson('/api/mobile/lives', [
        'title' => 'Sneaky',
        'thumbnail' => UploadedFile::fake()->image('c.jpg'),
        'product_ids' => [$foreign->id],
        'pre_bid_min' => [$foreign->id => 10],
    ])->assertStatus(422);
});

test('seller can go live; non-owner is forbidden', function () {
    $live = scheduledLive($this->seller);

    Sanctum::actingAs(User::factory()->create());
    $this->postJson("/api/mobile/lives/{$live->id}/go-live")->assertStatus(403);

    Sanctum::actingAs($this->seller);
    $this->postJson("/api/mobile/lives/{$live->id}/go-live")
        ->assertOk()
        ->assertJsonPath('ok', true);

    expect($live->fresh()->status)->toBe('live');
});

test('go live is rejected when the live is not scheduled', function () {
    $live = scheduledLive($this->seller, ['status' => 'live']);
    Sanctum::actingAs($this->seller);

    $this->postJson("/api/mobile/lives/{$live->id}/go-live")->assertStatus(422);
});

test('seller can put a session product up for auction', function () {
    $product = approvedProduct($this->seller);
    $live = scheduledLive($this->seller, ['status' => 'live']);
    $live->liveProducts()->attach([$product->id => ['pre_bid_min' => 10]]);

    Sanctum::actingAs($this->seller);
    $this->postJson("/api/mobile/lives/{$live->id}/set-product", [
        'product_id' => $product->id,
        'starting_bid' => 50,
    ])->assertOk();

    $live->refresh();
    expect($live->auction_status)->toBe('active');
    expect((int) $live->product_id)->toBe($product->id);
    expect((float) $live->starting_bid)->toBe(50.0);
});

test('set-product rejects a product not in the live session', function () {
    $product = approvedProduct($this->seller); // never attached
    $live = scheduledLive($this->seller, ['status' => 'live']);

    Sanctum::actingAs($this->seller);
    $this->postJson("/api/mobile/lives/{$live->id}/set-product", [
        'product_id' => $product->id,
        'starting_bid' => 50,
    ])->assertStatus(422);
});

test('closing an auction with no bids settles nothing and returns to idle', function () {
    $product = approvedProduct($this->seller);
    $live = scheduledLive($this->seller, [
        'status' => 'live',
        'auction_status' => 'active',
        'product_id' => $product->id,
        'starting_bid' => 50,
    ]);

    Sanctum::actingAs($this->seller);
    $this->postJson("/api/mobile/lives/{$live->id}/close-auction")
        ->assertOk()
        ->assertJsonPath('winner_username', null);

    expect($live->fresh()->auction_status)->toBe('idle');
    expect(Order::count())->toBe(0);
    expect($product->fresh()->status)->toBe('approved'); // not sold — no winner
});

test('ending a live marks it ended', function () {
    $live = scheduledLive($this->seller, ['status' => 'live']);

    Sanctum::actingAs($this->seller);
    $this->postJson("/api/mobile/lives/{$live->id}/end")->assertOk();

    expect($live->fresh()->status)->toBe('ended');
});

test('seller-products returns only the sellers own approved products', function () {
    approvedProduct($this->seller);
    approvedProduct($this->seller, 200);
    $draft = approvedProduct($this->seller);
    $draft->update(['status' => 'pending']);
    approvedProduct(User::factory()->create()); // someone else's

    Sanctum::actingAs($this->seller);
    $this->getJson('/api/mobile/live-products')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});
