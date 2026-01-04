<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     *
     * @param  string  $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Unable to login using ' . $provider . '. Please try again.');
        }

        // Check if user already exists
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Update provider ID if not set (or create a linked_social_accounts table logic if robust)
            // For simplicity, we just log them in if email matches. 
            // In a real app, strict security checks (like verifying email ownership) are recommended.
            Auth::login($user);
            return redirect('/');
        } else {
            // Create a new user
            // Note: You might want to force them to set a password later or just handle social-only users
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // Random password
                'email_verified_at' => now(), // Assuming social login verifies email
                // 'provider_id' => $socialUser->getId(), // if you have this column
                // 'provider_name' => $provider,        // if you have this column
            ]);

            Auth::login($user);
            return redirect('/');
        }
    }
}
