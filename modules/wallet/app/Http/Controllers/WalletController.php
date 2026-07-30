<?php

namespace Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Wallet\Services\WalletService;
use Modules\Wallet\Models\WithdrawalRequest;
use Modules\Wallet\Models\PayoutAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Wallet\Exceptions\InsufficientFundsException;

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

        $storeValue = \App\Models\Product::where('vendor_id', $user->id)
            ->where('status', 'approved')
            ->sum('price');

        return view('wallet::index', compact('wallet', 'transactions', 'withdrawalRequests', 'payoutAccounts', 'storeValue'));
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

        // The request row and the debit that backs it must be created together, otherwise a
        // failed debit would leave a payable request the balance never covered.
        try {
            DB::transaction(function () use ($request, $wallet, $payoutAccount, $user) {
                WithdrawalRequest::create([
                    'wallet_id' => $wallet->id,
                    'payout_account_id' => $payoutAccount->id,
                    'amount' => $request->amount,
                    'status' => 'pending',
                    'bank_details' => "Bank: {$payoutAccount->bank_name}\nHolder: {$payoutAccount->account_holder}\nRIB: {$payoutAccount->rib}",
                ]);

                // Debit immediately to prevent double spending while the request is pending.
                $this->walletService->debit($user, (float) $request->amount, 'withdrawal', 'Withdrawal Request');
            });
        } catch (InsufficientFundsException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Withdrawal request failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'exception' => $e,
            ]);

            return back()->with('error', 'We could not submit your withdrawal request. Please try again.');
        }

        return back()->with('success', 'Withdrawal request submitted.');
    }
}
