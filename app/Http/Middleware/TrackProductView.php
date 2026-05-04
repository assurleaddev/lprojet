<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackProductView
{
    /**
     * Record a product detail-page view after the response is sent.
     * Runs after the response so it never adds latency to the page load.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests to the product show route
        if ($request->isMethod('GET') && ! $request->ajax()) {
            $product = $request->route('product');

            if ($product && is_object($product) && isset($product->id) && $request->user()?->id !== $product->vendor_id) {
                $source = $request->query('source', 'direct');

                DB::table('product_views')->insert([
                    'product_id' => $product->id,
                    'user_id' => $request->user()?->id,
                    'session_id' => substr(session()->getId(), 0, 64),
                    'source' => $source,
                    'created_at' => now(),
                ]);

                // Increment the denormalized counter directly — no need to wait for the hourly job
                DB::table('products')
                    ->where('id', $product->id)
                    ->increment('views_count');
            }
        }

        return $response;
    }
}
