<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Wallet\Models\WithdrawalRequest;
use Modules\Wallet\Services\WalletService;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(Request $request)
    {
        $query = WithdrawalRequest::with(['wallet.user', 'payoutAccount']);

        // Search by username or email
        if ($request->search) {
            $query->whereHas('wallet.user', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'amount_desc':
                $query->orderBy('amount', 'desc');
                break;
            case 'amount_asc':
                $query->orderBy('amount', 'asc');
                break;
            default:
                $query->latest();
                break;
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        return view('wallet::admin.withdrawals.index', compact('withdrawals'));
    }

    public function approve(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $withdrawal->update(['status' => 'approved']);

        return back()->with('success', 'Withdrawal request approved successfully.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($withdrawal, $request) {
            $withdrawal->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note,
            ]);

            // Refund the user's wallet
            $user = $withdrawal->wallet->user;
            $this->walletService->credit($user, $withdrawal->amount, 'refund', 'Withdrawal request rejected: ' . $request->admin_note);
        });

        return back()->with('success', 'Withdrawal request rejected and refunded.');
    }
}
