<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

pest()->use(RefreshDatabase::class);

test('follow endpoint toggles following state', function () {
    $me = User::factory()->create();
    $seller = User::factory()->create();
    Sanctum::actingAs($me);

    $this->postJson("/api/mobile/users/{$seller->id}/follow")
        ->assertOk()
        ->assertJsonPath('following', true);
    expect($me->fresh()->isFollowing($seller))->toBeTrue();

    $this->postJson("/api/mobile/users/{$seller->id}/follow")
        ->assertOk()
        ->assertJsonPath('following', false);
    expect($me->fresh()->isFollowing($seller))->toBeFalse();
});

test('cannot follow yourself', function () {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $this->postJson("/api/mobile/users/{$me->id}/follow")->assertStatus(422);
});
