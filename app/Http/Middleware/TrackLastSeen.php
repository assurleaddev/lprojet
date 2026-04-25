<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $userId = auth()->id();
            $cacheKey = "last_seen_{$userId}";

            if (! cache()->has($cacheKey)) {
                auth()->user()->updateQuietly(['last_seen_at' => now()]);
                cache()->put($cacheKey, true, now()->addMinutes(5));
            }
        }

        return $next($request);
    }
}
