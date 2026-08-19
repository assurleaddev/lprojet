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
        if (! config('services.twilio.phone_verification_enabled')) {
            return $next($request);
        }

        $user = $request->user();

        // 1. If user is NOT logged in, let them proceed (it's likely a login page or public page)
        // OR the 'auth' middleware will catch them later if needed.
        if (! $user) {
            return $next($request);
        }

        // 2. Always allow the verification pages themselves, their actions, and
        // logout — otherwise we'd redirect-loop or block the very screens the
        // user needs to complete verification.
        // NOTE: '/verify-email' must be allowed too (it is NOT under 'email/*').
        if ($request->is('auth/*') || $request->is('logout') || $request->is('email/*')
            || $request->is('livewire/*') || $request->routeIs('verify-email')) {
            return $next($request);
        }

        // 3. Email must be verified BEFORE phone. Without this, the phone gate
        // hijacks the post-registration redirect and skips email verification.
        if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verify-email');
        }

        // 4. Then require a verified phone.
        // Either phone_number is missing, OR phone_verified_at is null
        // (our logic sets phone_number but keeps phone_verified_at null until confirmed).
        if (empty($user->phone_number) || is_null($user->phone_verified_at)) {
            return redirect()->route('auth.secure_account');
        }

        return $next($request);
    }
}
