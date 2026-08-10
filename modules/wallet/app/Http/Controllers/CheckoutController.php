<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Wallet\Services\CheckoutService;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout)
    {
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:wallet,card,cod',
            'product_id' => 'required_without:offer_id|exists:products,id',
            'offer_id' => 'nullable|exists:chat_offers,id',
        ]);

        try {
            $this->checkout->checkout(Auth::user(), $request->all());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('checkout.thank-you');
    }
}
