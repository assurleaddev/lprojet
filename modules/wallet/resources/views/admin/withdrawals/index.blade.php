@extends('backend.layouts.app')

@section('title', 'Withdrawal Management')

@section('admin-content')
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Withdrawal Management</h1>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <form action="{{ route('admin.withdrawals.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Search User</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Username or Email..." 
                        class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm px-4 py-2.5 focus:ring-brand focus:border-brand transition-all">
                </div>
                
                <div class="w-48">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm px-4 py-2.5 focus:ring-brand focus:border-brand transition-all">
                        <option value="all">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="w-48">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Sort By</label>
                    <select name="sort" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm px-4 py-2.5 focus:ring-brand focus:border-brand transition-all">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="amount_desc" {{ request('sort') == 'amount_desc' ? 'selected' : '' }}>Amount (High to Low)</option>
                        <option value="amount_asc" {{ request('sort') == 'amount_asc' ? 'selected' : '' }}>Amount (Low to High)</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-brand hover:bg-brand-dark text-white px-6 py-2.5 rounded-lg font-bold text-sm transition-all shadow-lg shadow-brand/20">
                        Apply Filters
                    </button>
                    @if(request()->hasAny(['search', 'status', 'sort']))
                        <a href="{{ route('admin.withdrawals.index') }}" class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-4 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-all text-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bank Details</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($withdrawals as $withdrawal)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 font-bold overflow-hidden">
                                            @if($withdrawal->wallet->user->avatar_url)
                                                <img src="{{ $withdrawal->wallet->user->avatar_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($withdrawal->wallet->user->username, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $withdrawal->wallet->user->username }}</p>
                                            <p class="text-xs text-gray-500">{{ $withdrawal->wallet->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                    {{ number_format($withdrawal->amount, 2) }} MAD
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-start gap-2 group/copy" x-data="{ 
                                        copied: false,
                                        copyToClipboard() {
                                            const text = `{{ str_replace(["\r", "\n"], ' ', $withdrawal->bank_details) }}`;
                                            navigator.clipboard.writeText(text).then(() => {
                                                this.copied = true;
                                                setTimeout(() => this.copied = false, 2000);
                                            });
                                        }
                                    }">
                                        <div class="max-w-[200px] whitespace-pre-line text-xs text-gray-600 dark:text-gray-400 line-clamp-3">
                                            {{ $withdrawal->bank_details }}
                                        </div>
                                        <button @click="copyToClipboard" class="mt-0.5 text-gray-400 hover:text-brand transition-colors relative" title="Copy to clipboard">
                                            <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                            </svg>
                                            <svg x-show="copied" x-cloak class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span x-show="copied" x-cloak class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] py-1 px-2 rounded shadow-lg whitespace-nowrap">Copied!</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$withdrawal->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $withdrawal->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($withdrawal->status === 'pending')
                                        <div class="flex items-center gap-2" x-data="{ showReject: false }">
                                            <form action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this withdrawal?')">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-700 font-bold text-xs uppercase transition-colors">Approve</button>
                                            </form>
                                            
                                            <button @click="showReject = true" class="text-red-600 hover:text-red-700 font-bold text-xs uppercase transition-colors">Reject</button>

                                            <!-- Reject Modal -->
                                            <div x-show="showReject" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                                                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md shadow-xl" @click.away="showReject = false">
                                                    <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Reject Withdrawal Request</h3>
                                                    <form action="{{ route('admin.withdrawals.reject', $withdrawal->id) }}" method="POST">
                                                        @csrf
                                                        <div class="mb-4">
                                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for rejection (Optional)</label>
                                                            <textarea name="admin_note" rows="3" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg p-3 text-sm focus:ring-brand focus:border-brand" placeholder="Explain why the request was rejected..."></textarea>
                                                        </div>
                                                        <div class="flex justify-end gap-3">
                                                            <button type="button" @click="showReject = false" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
                                                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm">Reject & Refund</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        @if($withdrawal->admin_note)
                                            <p class="text-xs italic text-gray-500 dark:text-gray-400 truncate max-w-[150px]" title="{{ $withdrawal->admin_note }}">
                                                Note: {{ $withdrawal->admin_note }}
                                            </p>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">--</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-200 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p>No withdrawal requests found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($withdrawals->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $withdrawals->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
