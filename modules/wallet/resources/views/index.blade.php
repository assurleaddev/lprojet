@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Balance Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">My Wallet</h2>
                    <div class="flex items-baseline gap-2 mb-6">
                        <span class="text-3xl font-bold text-gray-900">{{ number_format($wallet->balance, 2) }} MAD</span>
                        <span class="text-sm text-gray-500">Available Balance</span>
                    </div>

                    <button onclick="document.getElementById('withdrawModal').classList.remove('hidden')"
                        class="w-full bg-vinted-teal hover:bg-vinted-teal-dark text-white py-2 px-4 rounded-lg font-medium transition-colors">
                        Withdraw Funds
                    </button>
                </div>

                <!-- Withdrawal Requests -->
                @if($withdrawalRequests->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Withdrawal Requests</h3>
                        <div class="space-y-4">
                            @foreach($withdrawalRequests as $request)
                                        <div class="border-b border-gray-100 last:border-0 pb-3 last:pb-0">
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium">{{ number_format($request->amount, 2) }} MAD</span>
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full 
                                                                                            {{ $request->status === 'approved' ? 'bg-green-100 text-green-800' :
                                ($request->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">{{ $request->created_at->format('M d, Y') }}</div>
                                        </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Transactions -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">Transaction History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Description</th>
                                    <th class="px-6 py-3">Type</th>
                                    <th class="px-6 py-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 text-gray-500">
                                            {{ $transaction->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $transaction->description }}
                                            @if($transaction->reference_id)
                                                <span class="block text-xs text-gray-400">{{ $transaction->reference_id }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-right font-medium {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }}
                                            MAD
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            No transactions yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div id="withdrawModal" class="fixed inset-0 bg-black opacity-50 hidden z-50 flex items-center justify-center">
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
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (MAD)</label>
                        <input type="number" name="amount" step="0.01" min="1" max="{{ $wallet->balance }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-vinted-teal focus:ring-vinted-teal">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Details / PayPal Email</label>
                        <textarea name="bank_details" rows="3" required
                            class="w-full rounded-lg border-gray-300 focus:border-vinted-teal focus:ring-vinted-teal"
                            placeholder="Enter your bank account number or PayPal email..."></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-vinted-teal hover:bg-vinted-teal-dark text-white py-2 rounded-lg font-medium">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection