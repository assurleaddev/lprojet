<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ShippingLabelController extends Controller
{
    public function download(Order $order)
    {
        // Ensure the user is authorized to view this label (Vendor or Buyer or Admin)
        if (auth()->id() !== $order->vendor_id && auth()->id() !== $order->user_id && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $pdf = Pdf::loadView('wallet::shipping-label', compact('order'));

        return $pdf->download('shipping-label-' . $order->id . '.pdf');
    }
}
