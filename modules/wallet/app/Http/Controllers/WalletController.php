<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Models\WithdrawalRequest;
use Modules\Wallet\Models\PayoutAccount;
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
        $transactions = $wallet->transactions()->latest()->paginate(6, ['*'], 'page');
        $withdrawalRequests = $wallet->withdrawalRequests()->latest()->paginate(6, ['*'], 'withdraw_page');
        $payoutAccounts = PayoutAccount::where('user_id', $user->id)->get();

        return view('wallet::index', compact('wallet', 'transactions', 'withdrawalRequests', 'payoutAccounts'));
    }

    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payout_account_id' => 'required|exists:payout_accounts,id',
        ]);

        $user = Auth::user();
        $payoutAccount = PayoutAccount::where('user_id', $user->id)
            ->where('id', $request->payout_account_id)
            ->firstOrFail();

        $wallet = $this->walletService->getWallet($user);

        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance.');
        }

        // Create withdrawal request
        WithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'payout_account_id' => $payoutAccount->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'bank_details' => "Bank: {$payoutAccount->bank_name}\nHolder: {$payoutAccount->account_holder}\nRIB: {$payoutAccount->rib}",
        ]);

        // Optionally hold the funds? 
        // For now, we just create the request. 
        // Real implementation might debit the wallet immediately or hold it.
        // Let's debit it to prevent double spending.
        $this->walletService->debit($user, $request->amount, 'withdrawal', 'Withdrawal Request');

        return back()->with('success', 'Withdrawal request submitted.');
    }
}
