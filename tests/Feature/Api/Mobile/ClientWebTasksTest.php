<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use App\Notifications\OfferNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Modules\Chat\Enums\OfferStatus;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Message;
use Modules\Chat\Models\Offer;

pest()->use(RefreshDatabase::class);

function cwtProduct(User $vendor, string $status = 'approved', float $price = 300): Product
{
    $cat = Category::firstOrCreate(['slug' => 'cwt-cat'], ['name' => 'CwtCat']);

    return Product::create([
        'name' => 'Item '.uniqid(),
        'description' => 'd',
        'price' => $price,
        'vendor_id' => $vendor->id,
        'category_id' => $cat->id,
        'status' => $status,
    ]);
}

function cwtOffer(User $buyer, User $seller, Product $product): Offer
{
    $conv = Conversation::create([
        'user_one_id' => min($seller->id, $buyer->id),
        'user_two_id' => max($seller->id, $buyer->id),
        'product_id' => $product->id,
    ]);

    return Offer::create([
        'conversation_id' => $conv->id,
        'product_id' => $product->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'offer_price' => 144,
        'status' => OfferStatus::Pending,
    ]);
}

function cwtReview(User $seller, int $rating, ?int $parentId = null): Review
{
    $author = User::factory()->create();

    return Review::create([
        'review' => 'ok',
        'rating' => $rating,
        'model_id' => $seller->id,
        'model_type' => $seller->getMorphClass(),
        'author_id' => $author->id,
        'author_type' => $author->getMorphClass(),
        'parent_id' => $parentId,
    ]);
}

function cwtGoogleClaims(array $overrides = []): array
{
    return array_merge([
        'aud' => 'web-client.apps.googleusercontent.com',
        'iss' => 'https://accounts.google.com',
        'exp' => (string) (time() + 3600),
        'sub' => '1234567890',
        'email' => 'Sara.Deals@gmail.com',
        'email_verified' => 'true',
        'name' => 'Sara Deals',
        'given_name' => 'Sara',
        'family_name' => 'Deals',
    ], $overrides);
}

// ---------- W1: prices in MAD ----------

test('offer notifications show the amount in MAD and carry product and conversation ids', function () {
    $seller = User::factory()->create();
    $offer = cwtOffer(User::factory()->create(), $seller, cwtProduct($seller));

    $data = (new OfferNotification($offer, 'received'))->toDatabase($seller);

    expect($data['message'])->toContain('144.00 MAD')->not->toContain('$')
        ->and($data['product_id'])->toBe($offer->product_id)
        ->and($data['conversation_id'])->toBe($offer->conversation_id);

    $mail = (new OfferNotification($offer, 'received'))->toMail($seller);
    expect(implode(' ', $mail->introLines))->toContain('144.00 MAD')->not->toContain('$');
});

test('migration rewrites dollar amounts in stored offer notifications', function () {
    $user = User::factory()->create();
    $id = (string) Str::uuid();
    DB::table('notifications')->insert([
        'id' => $id,
        'type' => OfferNotification::class,
        'notifiable_type' => $user->getMorphClass(),
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'offer_received', 'offer_id' => 1, 'message' => 'New offer of $1,290.00 on sneaker nike']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    (require database_path('migrations/2026_11_01_165524_rewrite_dollar_amounts_in_offer_notifications.php'))->up();

    $message = json_decode(DB::table('notifications')->where('id', $id)->value('data'), true)['message'];
    expect($message)->toBe('New offer of 1,290.00 MAD on sneaker nike');
});

// ---------- W2: login with username or email ----------

test('api login accepts a username, an email, or the legacy email field', function () {
    $user = User::factory()->create(['username' => 'houdastore', 'email' => 'houda@example.com', 'password' => Hash::make('secret123')]);

    $this->postJson('/api/auth/login', ['login' => 'houdastore', 'password' => 'secret123'])
        ->assertOk()->assertJsonPath('user.id', $user->id)->assertJsonStructure(['token']);
    $this->postJson('/api/auth/login', ['login' => 'houda@example.com', 'password' => 'secret123'])
        ->assertOk()->assertJsonPath('user.id', $user->id);
    $this->postJson('/api/auth/login', ['email' => 'houda@example.com', 'password' => 'secret123'])
        ->assertOk()->assertJsonPath('user.id', $user->id);

    $this->postJson('/api/auth/login', ['login' => 'houdastore', 'password' => 'wrong'])->assertStatus(401);
    $this->postJson('/api/auth/login', ['password' => 'secret123'])->assertStatus(422);
});

// ---------- W3: Google sign-in ----------

test('google login creates a verified account from a valid id token', function () {
    config(['services.google.client_id' => 'web-client.apps.googleusercontent.com']);
    Http::fake(['oauth2.googleapis.com/*' => Http::response(cwtGoogleClaims())]);

    $this->postJson('/api/auth/google', ['id_token' => 'tok'])
        ->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'email']])
        ->assertJsonPath('user.email', 'sara.deals@gmail.com')
        ->assertJsonPath('user.email_verified', true);

    $user = User::where('email', 'sara.deals@gmail.com')->firstOrFail();
    expect($user->first_name)->toBe('Sara')
        ->and($user->last_name)->toBe('Deals')
        ->and($user->username)->toBe('sara-deals');
});

test('google login signs into the existing account with that email', function () {
    config(['services.google.client_id' => 'web-client.apps.googleusercontent.com']);
    $existing = User::factory()->create(['email' => 'sara.deals@gmail.com']);
    Http::fake(['oauth2.googleapis.com/*' => Http::response(cwtGoogleClaims())]);

    $this->postJson('/api/auth/google', ['id_token' => 'tok'])->assertOk()->assertJsonPath('user.id', $existing->id);
    expect(User::where('email', 'sara.deals@gmail.com')->count())->toBe(1);
});

test('google login rejects tokens for another app, unverified emails and invalid tokens', function () {
    config(['services.google.client_id' => 'web-client.apps.googleusercontent.com']);

    Http::fake(['oauth2.googleapis.com/*' => Http::response(cwtGoogleClaims(['aud' => 'someone-else']))]);
    $this->postJson('/api/auth/google', ['id_token' => 'tok'])->assertStatus(401);

    Http::fake(['oauth2.googleapis.com/*' => Http::response(cwtGoogleClaims(['email_verified' => 'false']))]);
    $this->postJson('/api/auth/google', ['id_token' => 'tok'])->assertStatus(401);

    Http::fake(['oauth2.googleapis.com/*' => Http::response(['error' => 'invalid_token'], 400)]);
    $this->postJson('/api/auth/google', ['id_token' => 'tok'])->assertStatus(401);

    expect(User::where('email', 'sara.deals@gmail.com')->exists())->toBeFalse();
});

test('google login accepts the extra mobile client ids', function () {
    config([
        'services.google.client_id' => 'web-client.apps.googleusercontent.com',
        'services.google.mobile_client_ids' => ['ios-client.apps.googleusercontent.com'],
    ]);
    Http::fake(['oauth2.googleapis.com/*' => Http::response(cwtGoogleClaims(['aud' => 'ios-client.apps.googleusercontent.com']))]);

    $this->postJson('/api/auth/google', ['id_token' => 'tok'])->assertOk();
});

// ---------- W4: favorites ----------

test('favorite toggles, keeps the counter in sync and lists my favorites', function () {
    $seller = User::factory()->create();
    $me = User::factory()->create();
    $liked = cwtProduct($seller);
    $hidden = cwtProduct($seller, 'hidden');
    cwtProduct($seller); // not liked
    Sanctum::actingAs($me);

    $this->postJson("/api/mobile/products/{$liked->id}/favorite")
        ->assertOk()->assertJsonPath('favorited', true)->assertJsonPath('favorites_count', 1);
    $this->postJson("/api/mobile/products/{$hidden->id}/favorite")->assertOk();
    expect((int) DB::table('products')->where('id', $liked->id)->value('favorites_count'))->toBe(1);

    $this->getJson('/api/mobile/favorites')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $liked->id);

    $this->postJson("/api/mobile/products/{$liked->id}/favorite")
        ->assertOk()->assertJsonPath('favorited', false)->assertJsonPath('favorites_count', 0);
    $this->getJson('/api/mobile/favorites')->assertOk()->assertJsonCount(0, 'data');
});

test('a seller cannot like their own product', function () {
    $seller = User::factory()->create();
    $product = cwtProduct($seller);
    Sanctum::actingAs($seller);

    $this->postJson("/api/mobile/products/{$product->id}/favorite")->assertStatus(422);
    expect($product->favorites()->count())->toBe(0);
});

// ---------- W5: member search ----------

test('member search finds users by username or name and skips banned users', function () {
    $sara = User::factory()->create(['username' => 'saradeals', 'first_name' => 'Sara']);
    User::factory()->create(['username' => 'sarabanned', 'banned_at' => now()]);
    User::factory()->create(['username' => 'reda']);
    cwtReview($sara, 4);

    $this->getJson('/api/mobile/users/search?q=sara')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.username', 'saradeals')
        ->assertJsonPath('data.0.rating_count', 1);

    $this->getJson('/api/mobile/users/search?q=s')->assertStatus(422);
});

// ---------- W6: rating and follower counts ----------

test('product page includes the seller rating (top-level reviews only)', function () {
    $seller = User::factory()->create();
    $product = cwtProduct($seller);
    $first = cwtReview($seller, 5);
    cwtReview($seller, 3);
    cwtReview($seller, 1, $first->id); // a reply — not counted

    $this->getJson("/api/mobile/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('vendor.rating_avg', 4)
        ->assertJsonPath('vendor.rating_count', 2);
});

test('public member profile shows rating, follower counts and follow state', function () {
    $seller = User::factory()->create();
    $viewer = User::factory()->create();
    $viewer->follow($seller);
    $seller->follow(User::factory()->create());
    cwtProduct($seller);
    cwtProduct($seller, 'hidden');
    cwtReview($seller, 4);

    Sanctum::actingAs($viewer);
    $this->getJson("/api/mobile/users/{$seller->id}")
        ->assertOk()
        ->assertJsonPath('followers_count', 1)
        ->assertJsonPath('following_count', 1)
        ->assertJsonPath('products_count', 1)
        ->assertJsonPath('rating_avg', 4)
        ->assertJsonPath('rating_count', 1)
        ->assertJsonPath('is_following', true)
        ->assertJsonPath('is_me', false);
});

test('my profile stats include followers, following and rating', function () {
    $me = User::factory()->create();
    User::factory()->create()->follow($me);
    cwtReview($me, 5);
    Sanctum::actingAs($me);

    $this->getJson('/api/mobile/profile')
        ->assertOk()
        ->assertJsonPath('stats.followers_count', 1)
        ->assertJsonPath('stats.following_count', 0)
        ->assertJsonPath('stats.rating_avg', 5)
        ->assertJsonPath('stats.rating_count', 1);
});

// ---------- W7 / W8 / W9: notifications and badges ----------

test('notifications leave out chat messages and say where to go', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $offer = cwtOffer($buyer, $seller, cwtProduct($seller));

    $insert = fn (string $type, array $data) => DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => $type,
        'notifiable_type' => $seller->getMorphClass(),
        'notifiable_id' => $seller->id,
        'data' => json_encode($data),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $insert(NewMessageNotification::class, ['type' => 'new_message', 'message' => 'x sent you a message.']);
    // Stored before offer notifications carried a product id.
    $insert(OfferNotification::class, ['type' => 'offer_received', 'offer_id' => $offer->id, 'message' => 'New offer']);

    Sanctum::actingAs($seller);
    $this->getJson('/api/mobile/notifications')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'offer_received')
        ->assertJsonPath('data.0.product_id', $offer->product_id)
        ->assertJsonPath('data.0.conversation_id', $offer->conversation_id)
        ->assertJsonPath('data.0.target.type', 'product')
        ->assertJsonPath('data.0.target.id', $offer->product_id);

    $this->getJson('/api/mobile/unread-counts')
        ->assertOk()
        ->assertJsonPath('notifications', 1);
});

test('unread counts count unread messages from the other person only', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $offer = cwtOffer($buyer, $seller, cwtProduct($seller));

    Message::create(['conversation_id' => $offer->conversation_id, 'user_id' => $buyer->id, 'body' => 'hi']);
    Message::create(['conversation_id' => $offer->conversation_id, 'user_id' => $buyer->id, 'body' => 'there']);
    Message::create(['conversation_id' => $offer->conversation_id, 'user_id' => $buyer->id, 'body' => 'read', 'read_at' => now()]);
    Message::create(['conversation_id' => $offer->conversation_id, 'user_id' => $seller->id, 'body' => 'mine']);

    Sanctum::actingAs($seller);
    $this->getJson('/api/mobile/unread-counts')
        ->assertOk()
        ->assertJsonPath('messages', 2)
        ->assertJsonPath('total', 2);
});

// ---------- Mobile app support ----------

test('public product endpoints report is_favorited for the app user', function () {
    $seller = User::factory()->create();
    $me = User::factory()->create();
    $product = cwtProduct($seller);
    $product->addFavorite($me->id);
    Sanctum::actingAs($me);

    $this->getJson("/api/mobile/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('is_favorited', true);

    $this->getJson('/api/mobile/products')
        ->assertOk()
        ->assertJsonPath('data.0.is_favorited', true);
});

test('addresses endpoint lists only my delivery addresses', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $address = ['country' => 'Maroc', 'full_name' => 'Houda', 'address_line_1' => '1 rue X', 'city' => 'Rabat', 'postcode' => '10000'];
    $me->addresses()->create($address);
    $other->addresses()->create($address);
    Sanctum::actingAs($me);

    $this->getJson('/api/mobile/addresses')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.city', 'Rabat');
});
