<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.google.client_id' => 'web-client.apps.googleusercontent.com',
        'services.google.client_secret' => 'secret',
        'services.google.redirect' => 'http://localhost/auth/google/callback',
        'services.twilio.phone_verification_enabled' => false,
    ]);
});

function fakeGoogleUser(array $raw = []): void
{
    $raw = array_merge([
        'sub' => '123',
        'email' => 'Sara.Deals@gmail.com',
        'email_verified' => true,
        'name' => 'Sara Deals',
    ], $raw);

    $user = (new SocialiteUser())->setRaw($raw)->map([
        'id' => $raw['sub'],
        'name' => $raw['name'],
        'email' => $raw['email'],
    ]);

    $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
    $provider->shouldReceive('user')->andReturn($user);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
}

test('google sign-up creates a verified account and logs in', function () {
    fakeGoogleUser();

    $this->get('/auth/google/callback')->assertRedirect(route('home'));

    $user = User::where('email', 'sara.deals@gmail.com')->firstOrFail();
    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->first_name)->toBe('Sara')
        ->and($user->last_name)->toBe('Deals')
        ->and($user->username)->toBe('sara-deals');
    $this->assertAuthenticatedAs($user);
});

test('google sign-in logs into the existing account with that email', function () {
    $existing = User::factory()->create(['email' => 'sara.deals@gmail.com', 'email_verified_at' => null]);
    fakeGoogleUser();

    $this->get('/auth/google/callback')->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($existing);
    expect($existing->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and(User::where('email', 'sara.deals@gmail.com')->count())->toBe(1);
});

test('an unverified google email is refused', function () {
    User::factory()->create(['email' => 'sara.deals@gmail.com']);
    fakeGoogleUser(['email_verified' => false]);

    $this->get('/auth/google/callback')
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
    $this->assertGuest();
});

test('a banned account cannot sign in with google', function () {
    User::factory()->create(['email' => 'sara.deals@gmail.com', 'banned_at' => now()]);
    fakeGoogleUser();

    $this->get('/auth/google/callback')->assertSessionHas('error');
    $this->assertGuest();
});

test('a google error shows a message instead of an error page', function () {
    $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
    $provider->shouldReceive('user')->andThrow(new \Laravel\Socialite\Two\InvalidStateException());
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get('/auth/google/callback')
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
    $this->assertGuest();
});

test('unknown or unconfigured providers return 404', function () {
    $this->get('/auth/github')->assertNotFound();

    // Facebook login was removed: refused even when keys are configured.
    config(['services.facebook.client_id' => 'fb-id']);
    $this->get('/auth/facebook')->assertNotFound();
    $this->get('/auth/facebook/callback')->assertNotFound();
});
