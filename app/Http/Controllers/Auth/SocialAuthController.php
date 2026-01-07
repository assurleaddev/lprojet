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
            \Log::error('Socialite Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Unable to login using ' . $provider . '. Please try again.');
        }

        // Check if user already exists
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Update provider ID if not set (or create a linked_social_accounts table logic if robust)
            // For simplicity, we just log them in if email matches. 
            // In a real app, strict security checks (like verifying email ownership) are recommended.
            Auth::login($user);

            // Redirect to secure account if phone is missing
            if (empty($user->phone_number)) {
                return redirect()->route('auth.secure_account');
            }

            return redirect('/');
        } else {
            // Generate a unique username
            $baseUsername = Str::slug($socialUser->getName() ?? $socialUser->getNickname() ?? explode('@', $socialUser->getEmail())[0]);
            if (empty($baseUsername)) {
                $baseUsername = 'user';
            }

            $username = $baseUsername;
            $count = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $count;
                $count++;
            }

            try {
                // Split name
                $fullName = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';
                $nameParts = explode(' ', $fullName, 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '';

                // Create a new user
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $socialUser->getEmail(),
                    'username' => $username, // Added username
                    'password' => bcrypt(Str::random(16)), // Random password
                    'email_verified_at' => now(), // Assuming social login verifies email
                    // 'provider_id' => $socialUser->getId(), // if you have this column
                    // 'provider_name' => $provider,        // if you have this column
                ]);

                // Try to download avatar if possible (Optional, requires HasMedia or custom logic)
                // Leaving this purely as user creation for now to solve the crash.

                Auth::login($user);
                return redirect()->route('auth.secure_account');
            } catch (\Exception $e) {
                dd($e->getMessage());
                // \Log::error('Social Login Error: ' . $e->getMessage());
                // return redirect()->route('login')->with('error', 'Unable to create account. Please try again.');
            }
        }
    }
}
