<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. If user is NOT logged in, let them proceed (it's likely a login page or public page)
        // OR the 'auth' middleware will catch them later if needed.
        if (!$user) {
            return $next($request);
        }

        // 2. Identify excluded routes (to prevent infinite loops)
        // We must allow:
        // - Logout
        // - The "Secure your account" page itself
        // - The "Verify Phone" page
        // - The "Verify Code" page
        // - Any API/Ajax calls needed for verification (like resending code)
        if ($request->is('auth/*') || $request->is('logout') || $request->is('email/*') || $request->is('livewire/*')) {
            return $next($request);
        }

        // 3. Check if phone needs verification
        // Either phone_number is missing, OR phone_verified_at is null
        // Note: Our logic sets phone_number but keeps phone_verified_at null until confirmed.
        // If we strictly want to block browsing until verified:
        if (empty($user->phone_number) || is_null($user->phone_verified_at)) {
            // Redirect to the Secure Account prompt
            return redirect()->route('auth.secure_account');
        }

        return $next($request);
    }
}
