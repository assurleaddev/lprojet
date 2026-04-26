<?php

namespace App\Http\Controllers\Backend\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $query = Claim::with(['order', 'user'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $claims = $query->paginate(20);

        return view('backend.marketplace.claims.index', compact('claims', 'status'));
    }

    public function show(Claim $claim)
    {
        $claim->load(['order.vendor', 'order.product', 'user']);

        return view('backend.marketplace.claims.show', compact('claim'));
    }

    public function update(Request $request, Claim $claim)
    {
        $request->validate([
            'status' => 'required|in:pending,under_review,resolved,rejected',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $claim->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
        ]);

        return back()->with('success', 'Claim updated successfully.');
    }
}
