<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getWallet($user);
        $transactions = $wallet->transactions()->latest()->paginate(10);
        $withdrawalRequests = $wallet->withdrawalRequests()->latest()->get();

        return view('wallet::index', compact('wallet', 'transactions', 'withdrawalRequests'));
    }

    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_details' => 'required|string',
        ]);

        $user = Auth::user();
        $wallet = $this->walletService->getWallet($user);

        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance.');
        }

        // Create withdrawal request
        WithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'bank_details' => $request->bank_details,
        ]);

        // Optionally hold the funds? 
        // For now, we just create the request. 
        // Real implementation might debit the wallet immediately or hold it.
        // Let's debit it to prevent double spending.
        $this->walletService->debit($user, $request->amount, 'withdrawal', 'Withdrawal Request');

        return back()->with('success', 'Withdrawal request submitted.');
    }
}
