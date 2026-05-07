<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Block non-superadmin authenticated users from all /admin/* paths
        if ($request->is('admin') || $request->is('admin/*')) {
            if (Auth::check() && ! Auth::user()->hasRole('Superadmin')) {
                abort(403, __('Unauthorized access'));
            }

            if (! Auth::check()) {
                return redirect()->route('home', ['login_required' => 1]);
            }
        }

        return $next($request);
    }
}
