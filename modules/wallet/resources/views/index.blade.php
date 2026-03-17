@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
        x-data="{ tab: '{{ request()->query('tab', 'transactions') }}' }">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Balance Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">My Wallet</h2>
                    <div class="mb-6">
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-bold text-gray-900">{{ number_format($wallet->balance, 2) }} MAD</span>
                        </div>
                        @if($wallet->pending_balance > 0)
                            <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-amber-600 bg-amber-50 w-fit px-2 py-1 rounded-lg">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ number_format($wallet->pending_balance, 2) }} MAD Pending
                            </div>
                        @endif
                    </div>

                    <button onclick="document.getElementById('withdrawModal').classList.remove('hidden')"
                        class="w-full bg-vinted-teal hover:bg-vinted-teal-dark text-white py-3 px-4 rounded-xl font-bold transition-all transform active:scale-95 shadow-lg shadow-vinted-teal/10">
                        Withdraw Funds
                    </button>

                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <a href="{{ route('settings.payments') }}"
                            class="flex items-center gap-3 text-sm text-gray-600 hover:text-vinted-teal transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                            Manage Payout Accounts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Tab Headers -->
                    <div class="flex border-b border-gray-100 bg-gray-50/30">
                        <button @click="tab = 'transactions'; window.history.replaceState(null, '', '?tab=transactions')"
                            :class="tab === 'transactions' ? 'border-vinted-teal text-vinted-teal bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                            class="flex-1 py-4 px-6 text-sm font-bold border-b-2 transition-all">
                            Transaction History
                        </button>
                        <button @click="tab = 'withdrawals'; window.history.replaceState(null, '', '?tab=withdrawals')"
                            :class="tab === 'withdrawals' ? 'border-vinted-teal text-vinted-teal bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                            class="flex-1 py-4 px-6 text-sm font-bold border-b-2 transition-all relative">
                            Withdrawal Requests
                            @if($withdrawalRequests->where('status', 'pending')->count() > 0)
                                <span class="absolute top-3 right-4 w-2 h-2 bg-red-500 rounded-full"></span>
                            @endif
                        </button>
                    </div>

                    <!-- Tab Contents -->
                    <div>
                        <!-- Transactions Tab -->
                        <div x-show="tab === 'transactions'" class="transition-all duration-300">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-500 uppercase bg-gray-50/50">
                                        <tr>
                                            <th class="px-6 py-4 font-bold">Date</th>
                                            <th class="px-6 py-4 font-bold">Description</th>
                                            <th class="px-6 py-4 font-bold">Type</th>
                                            <th class="px-6 py-4 font-bold text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($transactions as $transaction)
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-6 py-4 text-gray-400">
                                                    {{ $transaction->created_at->format('M d, H:i') }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="font-bold text-gray-900">{{ $transaction->description }}</span>
                                                    @if($transaction->reference_id)
                                                        <span
                                                            class="block text-[10px] text-gray-400 mt-0.5">{{ $transaction->reference_id }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="px-2 py-0.5 text-[10px] font-bold rounded bg-gray-100 text-gray-600 uppercase">
                                                        {{ str_replace('_', ' ', $transaction->type) }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-6 py-4 text-right font-bold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }}
                                                    MAD
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                                    <p>No transactions yet.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($transactions->hasPages())
                                <div class="p-4 border-t border-gray-100 bg-gray-50/20">
                                    {{ $transactions->appends(['tab' => 'transactions'])->links() }}
                                </div>
                            @endif
                        </div>

                        <!-- Withdrawals Tab -->
                        <div x-show="tab === 'withdrawals'" x-cloak class="transition-all duration-300">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-500 uppercase bg-gray-50/50">
                                        <tr>
                                            <th class="px-6 py-4 font-bold">Date</th>
                                            <th class="px-6 py-4 font-bold">Bank Info</th>
                                            <th class="px-6 py-4 font-bold">Status</th>
                                            <th class="px-6 py-4 font-bold text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($withdrawalRequests as $request)
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-6 py-4 text-gray-400">
                                                    {{ $request->created_at->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-xs text-gray-600 truncate max-w-[150px]"
                                                        title="{{ $request->bank_details }}">
                                                        {{ $request->bank_details }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @php
                                                        $statusClasses = [
                                                            'pending' => 'bg-amber-100 text-amber-700',
                                                            'approved' => 'bg-green-100 text-green-700',
                                                            'rejected' => 'bg-red-100 text-red-700',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="px-2 py-0.5 text-[10px] font-bold rounded uppercase {{ $statusClasses[$request->status] ?? 'bg-gray-100 text-gray-600' }}">
                                                        {{ $request->status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right font-bold text-gray-900">
                                                    {{ number_format($request->amount, 2) }} MAD
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                                    <p>No withdrawal requests yet.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($withdrawalRequests->hasPages())
                                <div class="p-4 border-t border-gray-100 bg-gray-50/20">
                                    {{ $withdrawalRequests->appends(['tab' => 'withdrawals'])->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div id="withdrawModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg w-full max-w-md mx-4 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Request Withdrawal</h3>
                <button onclick="document.getElementById('withdrawModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('wallet.withdraw') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Amount to Withdraw</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm font-semibold">MAD</span>
                            </div>
                            <input type="number" name="amount" id="withdrawalAmount" step="0.01" min="1"
                                max="{{ $wallet->balance }}" required
                                class="block w-full pl-14 pr-16 py-3.5 bg-gray-50 border-gray-200 rounded-xl text-gray-900 font-bold focus:ring-vinted-teal focus:border-vinted-teal transition-all"
                                placeholder="0.00">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button"
                                    onclick="document.getElementById('withdrawalAmount').value = '{{ $wallet->balance }}'"
                                    class="text-xs font-bold text-vinted-teal hover:text-vinted-teal-dark bg-white border border-gray-100 shadow-sm px-2.5 py-1.5 rounded-lg transition-all active:scale-95">
                                    MAX
                                </button>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Available balance: <span
                                class="font-bold text-gray-800">{{ number_format($wallet->balance, 2) }} MAD</span></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Payout Method</label>
                        @if($payoutAccounts->count() > 0)
                            <div class="relative">
                                <select name="payout_account_id" required
                                    class="block w-full p-4 bg-gray-50 border-gray-200 rounded-xl text-gray-900 focus:ring-vinted-teal focus:border-vinted-teal transition-all appearance-none cursor-pointer">
                                    <option value="" disabled selected>Select a saved account</option>
                                    @foreach($payoutAccounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->bank_name }} - {{ $account->rib }} ({{ $account->account_holder }})
                                        </option>
                                    @endforeach
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                <a href="{{ route('settings.payments') }}"
                                    class="text-vinted-teal hover:underline font-semibold">Manage payout accounts</a>
                            </p>
                        @else
                            <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl">
                                <p class="text-sm text-amber-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                    No payout accounts found.
                                </p>
                                <a href="{{ route('settings.payments') }}"
                                    class="mt-3 inline-block w-full text-center bg-white border border-amber-200 text-amber-800 py-2 rounded-lg font-bold text-sm hover:bg-amber-100 transition-colors">
                                    Add an account in Settings
                                </a>
                            </div>
                        @endif
                    </div>
                    <button type="submit" {{ $payoutAccounts->count() === 0 ? 'disabled' : '' }}
                        class="w-full bg-vinted-teal hover:bg-vinted-teal-dark text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-vinted-teal/20 transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                        Submit Withdrawal Request
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection