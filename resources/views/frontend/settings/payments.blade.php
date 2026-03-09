@extends('layouts.app')

@section('title', 'Payout Accounts')

@section('content')
    <div class="shell px-4 md:px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">Settings</h1>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="w-full md:w-64 flex-shrink-0">
                <nav class="space-y-1">
                    <a href="{{ route('settings.profile') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Profile details</a>
                    <a href="{{ route('settings.account') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Account settings</a>
                    <a href="{{ route('settings.postage') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Postage</a>
                    <a href="{{ route('settings.payments') }}"
                        class="block px-3 py-2 text-sm font-medium rounded-md bg-gray-100 text-gray-900">Payments</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Bundle
                        discounts</a>
                    <a href="{{ route('settings.notifications') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Notifications</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Privacy
                        settings</a>
                    <a href="{{ route('settings.security') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Security</a>
                </nav>
            </aside>

            <!-- Content -->
            <div class="flex-1 bg-white border border-gray-200 rounded-lg p-6" x-data="payoutAccountManager()">
                @if (session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                    </div>
                @endif

                <div class="mb-8 pb-8 border-b border-gray-100">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Withdrawal Accounts</h3>
                    <p class="text-sm text-gray-500 mb-6">Manage your bank accounts for withdrawing funds from your wallet.
                    </p>

                    @if($payoutAccounts->isEmpty())
                        <button type="button" @click="showAddModal = true"
                            class="w-full border border-gray-300 border-dashed rounded-xl py-8 px-4 flex flex-col items-center justify-center hover:bg-gray-50 transition-colors group">
                            <div
                                class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-brand/10 transition-colors">
                                <svg class="w-6 h-6 text-gray-400 group-hover:text-brand" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                    </path>
                                </svg>
                            </div>
                            <span class="font-semibold text-gray-700">Add a withdrawal account</span>
                            <span class="text-xs text-gray-500 mt-1">RIB, Bank name, and Holder name required</span>
                        </button>
                    @else
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($payoutAccounts as $account)
                                <div
                                    class="border border-gray-200 rounded-xl p-5 flex items-start justify-between hover:border-brand/30 transition-all hover:shadow-sm">
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 14v20c0 4.418 7.163 8 16 8s16-3.582 16-8V14M8 14c0 4.418 7.163 8 16 8s16-3.582 16-8M8 14c0-4.418 7.163-8 16-8s16 3.582 16 8m0 0v20c0 4.418-7.163 8-16 8s-16-3.582-16-8V14">
                                                </path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h1m4 0h1"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900">{{ $account->bank_name }}</h4>
                                            <p class="text-sm text-gray-600 mt-0.5">{{ $account->account_holder }}</p>
                                            <p
                                                class="text-xs font-mono text-gray-500 mt-1 bg-gray-50 px-2 py-1 rounded border border-gray-100 inline-block">
                                                {{ $account->rib }}</p>
                                        </div>
                                    </div>
                                    <button type="button"
                                        @click="confirmDelete('{{ route('settings.payout-account.delete', $account->id) }}')"
                                        class="text-gray-400 hover:text-red-600 p-2 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" @click="showAddModal = true"
                            class="mt-6 text-brand font-bold text-sm hover:underline flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add another bank account
                        </button>
                    @endif
                </div>

                <!-- Add Account Modal -->
                <div x-cloak x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog"
                    aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showAddModal" class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true"
                            @click="showAddModal = false"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div x-show="showAddModal"
                            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-50">
                            <div class="bg-white px-6 pt-6 pb-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-xl font-bold text-gray-900">Add Bank Account</h3>
                                    <button @click="showAddModal = false"
                                        class="text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <form action="{{ route('settings.payout-account.store') }}" method="POST">
                                    @csrf
                                    <div class="space-y-5">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Bank
                                                Name</label>
                                            <input type="text" name="bank_name" required
                                                class="w-full border-gray-200 bg-gray-50 rounded-xl shadow-sm py-3 px-4 focus:border-brand focus:ring-brand transition-all"
                                                placeholder="e.g. Attijariwafa Bank, BCP, BMCE...">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Account Holder
                                                Name</label>
                                            <input type="text" name="account_holder" required
                                                class="w-full border-gray-200 bg-gray-50 rounded-xl shadow-sm py-3 px-4 focus:border-brand focus:ring-brand transition-all"
                                                placeholder="Full name as it appears on your RIB">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">RIB (24
                                                digits)</label>
                                            <input type="text" name="rib" required maxlength="24"
                                                class="w-full border-gray-200 bg-gray-50 rounded-xl shadow-sm py-3 px-4 focus:border-brand focus:ring-brand font-mono transition-all"
                                                placeholder="000 000 0000000000000000 00">
                                        </div>
                                    </div>
                                    <div class="mt-8 flex gap-3">
                                        <button type="submit"
                                            class="flex-1 bg-brand text-white py-3.5 rounded-xl font-bold hover:opacity-90 shadow-lg shadow-brand/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                            Save Account
                                        </button>
                                        <button type="button" @click="showAddModal = false"
                                            class="flex-1 border border-gray-300 py-3.5 rounded-xl text-gray-700 font-bold hover:bg-gray-50 transition-all">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Form -->
                <form x-ref="deleteForm" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <script>
                function payoutAccountManager() {
                    return {
                        showAddModal: false,
                        confirmDelete(url) {
                            if (confirm('Are you sure you want to delete this payout account?')) {
                                this.$refs.deleteForm.action = url;
                                this.$refs.deleteForm.submit();
                            }
                        }
                    }
                }
            </script>
        </div>
    </div>
@endsection
