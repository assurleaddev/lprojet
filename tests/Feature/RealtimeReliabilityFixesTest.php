<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Modules\Chat\Enums\OfferStatus;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Offer;

pest()->use(RefreshDatabase::class);

function makeConversation(User $a, User $b): Conversation
{
    return Conversation::create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
    ]);
}

function makeOffer(Conversation $conv, User $buyer, User $seller, array $attrs = []): Offer
{
    return Offer::create(array_merge([
        'conversation_id' => $conv->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'offer_price' => 50,
        'status' => OfferStatus::Pending,
    ], $attrs));
}

// ---------- T2a: mobile chat parity ----------

test('mobile sendMessage broadcasts MessageSent and notifies the recipient', function () {
    Event::fake([MessageSent::class]);
    Notification::fake();

    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $conv = makeConversation($buyer, $seller);

    Sanctum::actingAs($buyer);
    $this->postJson("/api/mobile/conversations/{$conv->id}/messages", ['body' => 'hello'])
        ->assertStatus(201);

    Event::assertDispatched(MessageSent::class);
    Notification::assertSentTo($seller, \App\Notifications\NewMessageNotification::class);
    expect($conv->fresh()->last_message_at)->not->toBeNull();
});

// ---------- T2b: offer expiry ----------

test('offers:expire marks stale pending offers as expired', function () {
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $conv = makeConversation($buyer, $seller);

    $stale = makeOffer($conv, $buyer, $seller, ['expires_at' => now()->subHour()]);
    $fresh = makeOffer($conv, $buyer, $seller, ['expires_at' => now()->addHours(5)]);
    $accepted = makeOffer($conv, $buyer, $seller, ['status' => OfferStatus::Accepted, 'expires_at' => now()->subHour()]);

    $this->artisan('offers:expire')->assertSuccessful();

    expect($stale->fresh()->status)->toBe(OfferStatus::Expired);
    expect($fresh->fresh()->status)->toBe(OfferStatus::Pending);
    expect($accepted->fresh()->status)->toBe(OfferStatus::Accepted); // final states untouched
});

test('an expired offer cannot be accepted from mobile', function () {
    Notification::fake();
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $conv = makeConversation($buyer, $seller);
    $offer = makeOffer($conv, $buyer, $seller, ['expires_at' => now()->subMinute()]);

    Sanctum::actingAs($seller);
    $this->postJson("/api/mobile/offers/{$offer->id}/accept")->assertStatus(422);

    expect($offer->fresh()->status)->toBe(OfferStatus::Expired);
});

// ---------- T2c: preference defaults ----------

test('mobile profile reports notification prefs ON by default (matching enforcement)', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);
    $res = $this->getJson('/api/mobile/profile')->assertOk()->json();

    expect($res['notifications']['enable_email_notifications'])->toBeTrue();
    expect($res['notifications']['notify_favourited_items'])->toBeTrue();
    expect($res['notifications']['notify_marketing'])->toBeFalse(); // unenforced opt-in stays off
});

// ---------- T2d: realtime events broadcast immediately ----------

test('live and chat events implement ShouldBroadcastNow', function () {
    foreach ([
        \App\Events\BidPlaced::class,
        \App\Events\CommentPosted::class,
        \App\Events\AuctionClosed::class,
        \App\Events\LiveStatusChanged::class,
        \Modules\Chat\Events\MessageSent::class,
        \Modules\Chat\Events\MessageRead::class,
    ] as $event) {
        expect(is_subclass_of($event, \Illuminate\Contracts\Broadcasting\ShouldBroadcastNow::class))
            ->toBeTrue($event.' must broadcast immediately');
    }
});
