<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class SocialAuthController extends Controller
{
    /** Providers the website can sign in with, when their keys are configured. */
    private const PROVIDERS = ['google', 'facebook', 'apple'];

    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirect(string $provider): SymfonyRedirect
    {
        $this->ensureProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider and sign in (or sign up).
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $this->ensureProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error('Socialite Error: '.$e->getMessage());

            return $this->failed(__('Connexion avec :provider impossible. Veuillez réessayer.', ['provider' => ucfirst($provider)]));
        }

        $email = strtolower((string) $socialUser->getEmail());
        if ($email === '') {
            return $this->failed(__('Votre compte :provider ne fournit pas d\'adresse e-mail.', ['provider' => ucfirst($provider)]));
        }

        // An existing account is matched by email, so the provider must have
        // verified that the address belongs to this person.
        if (! $this->emailIsVerified($provider, $socialUser)) {
            return $this->failed(__('Votre adresse e-mail n\'est pas vérifiée chez :provider.', ['provider' => ucfirst($provider)]));
        }

        $user = User::where('email', $email)->first();
        $isNew = $user === null;

        if ($isNew) {
            try {
                $user = $this->createUser($socialUser, $email);
            } catch (\Exception $e) {
                Log::error('Social Login Error: '.$e->getMessage());

                return $this->failed(__('Impossible de créer votre compte. Veuillez réessayer.'));
            }
        } elseif (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if ($user->isBanned()) {
            return $this->failed(__('Ce compte a été suspendu.'));
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        // Ask for the phone number when phone verification is enabled.
        if (config('services.twilio.phone_verification_enabled') && empty($user->phone_number)) {
            return redirect()->route('auth.secure_account');
        }

        return redirect()->intended(route('home'));
    }

    private function ensureProvider(string $provider): void
    {
        abort_unless(
            in_array($provider, self::PROVIDERS, true) && config("services.{$provider}.client_id"),
            404
        );
    }

    private function emailIsVerified(string $provider, SocialUser $socialUser): bool
    {
        // Google says so explicitly; the other providers only return verified emails.
        if ($provider !== 'google') {
            return true;
        }

        $raw = method_exists($socialUser, 'getRaw') ? $socialUser->getRaw() : [];

        return in_array($raw['email_verified'] ?? null, [true, 'true', 1, '1'], true);
    }

    private function createUser(SocialUser $socialUser, string $email): User
    {
        $fullName = $socialUser->getName() ?: ($socialUser->getNickname() ?: explode('@', $email)[0]);
        [$firstName, $lastName] = array_pad(explode(' ', trim($fullName), 2), 2, '');

        $baseUsername = Str::slug($fullName) ?: 'user';
        $username = $baseUsername;
        $count = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$count++;
        }

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'username' => $username,
            'password' => Hash::make(Str::random(32)),
        ]);

        // Not mass-assignable: set it explicitly (the provider verified the email).
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function failed(string $message): RedirectResponse
    {
        return redirect()->route('home')->with('error', $message);
    }
}
