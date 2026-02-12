@extends('layouts.app')

@section('title', 'Account Settings')

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
                        class="block px-3 py-2 text-sm font-medium rounded-md bg-gray-100 text-gray-900">Account
                        settings</a>
                    <a href="{{ route('settings.postage') }}"
                        class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Postage</a>
                    <a href="#" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Payments</a>
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
            <div class="flex-1 bg-white border border-gray-200 rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('settings.account.update') }}" method="POST"
                    x-data="holidaySettings({{ $user->getMeta('holiday_mode') ? 'true' : 'false' }})">
                    @csrf

                    <!-- Email -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">{{ $user->email }}</h3>
                            <span class="text-xs text-gray-500 flex items-center gap-1">Verified <svg
                                    class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg></span>
                        </div>
                        <button type="button"
                            class="text-brand border border-brand px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">Change</button>
                    </div>

                    <!-- Phone number -->
                    <div class="mb-8 pb-8 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                @if($user->phone_number)
                                    <h3 class="text-base font-medium text-gray-900">{{ $user->phone_country_code }}
                                        {{ $user->phone_number }}
                                    </h3>
                                    @if($user->phone_verified_at)
                                        <span class="text-xs text-gray-500 flex items-center gap-1">Verified <svg
                                                class="w-3 h-3 text-green-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg></span>
                                    @else
                                        <span class="text-xs text-red-500">Not verified</span>
                                    @endif
                                @else
                                    <h3 class="text-base font-medium text-gray-900">Phone number</h3>
                                @endif
                            </div>

                            <a href="{{ route('auth.verify_phone') }}"
                                class="text-brand border border-brand px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">
                                {{ $user->phone_verified_at ? 'Change' : 'Verify' }}
                            </a>
                        </div>
                        <p class="text-xs text-gray-500">Your phone number will only be used to help you log in. It won't be
                            made public, or used for marketing purposes.</p>
                    </div>

                    <!-- Personal Information -->
                    <div class="mb-8 pb-8 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <label class="text-base font-medium text-gray-900 w-1/3">Full name</label>
                            <div class="w-2/3 text-gray-700 font-medium uppercase">
                                {{ $user->full_name }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-6">
                            <label class="text-base font-medium text-gray-900 w-1/3">Gender</label>
                            <div class="w-2/3">
                                <select name="gender"
                                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-brand focus:border-brand">
                                    <option value="" disabled {{ !$user->getMeta('gender') ? 'selected' : '' }}>Select
                                        gender</option>
                                    <option value="Male" {{ $user->getMeta('gender') == 'Male' ? 'selected' : '' }}>Male
                                    </option>
                                    <option value="Female" {{ $user->getMeta('gender') == 'Female' ? 'selected' : '' }}>Female
                                    </option>
                                    <option value="Other" {{ $user->getMeta('gender') == 'Other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="text-base font-medium text-gray-900 w-1/3">Birthday</label>
                            <div class="w-2/3">
                                <input type="date" name="birthday" value="{{ $user->getMeta('birthday') }}"
                                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-brand focus:border-brand">
                            </div>
                        </div>
                    </div>

                    <!-- Holiday mode -->
                    <!-- Holiday mode -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between relative">
                        <div>
                            <label class="text-base font-medium text-gray-900">Holiday mode</label>
                            <p class="text-sm text-gray-500 mt-1">Hide your items from search results and catalog.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="holiday_mode" value="1" class="sr-only peer" x-model="holidayMode"
                                @click.prevent="toggleHolidayMode()">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand">
                            </div>
                        </label>

                    </div>

                    <!-- Social Accounts -->
                    <div class="mb-8 pb-8 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-medium text-gray-900">Facebook</h3>
                            <button type="button"
                                class="text-[var(--brand)] border border-[var(--brand)] px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">Link</button>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-base font-medium text-gray-900">Google</h3>
                            <button type="button"
                                class="text-gray-400 border border-gray-300 px-4 py-2 rounded text-sm font-medium cursor-not-allowed"
                                disabled>Linked</button>
                        </div>
                        <p class="text-xs text-gray-500">Link to your other accounts to become a trusted, verified member.
                        </p>
                    </div>

                    <!-- Change password -->
                    <div class="mb-8 pb-8 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-base font-medium text-gray-900">Change password</h3>
                        <button type="button"
                            class="text-brand border border-brand px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">Change</button>
                    </div>

                    <!-- Delete account -->
                    <div class="mb-8 flex items-center justify-between cursor-pointer hover:bg-gray-50 p-2 -mx-2 rounded">
                        <h3 class="text-base font-medium text-gray-900">Delete my account</h3>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-brand text-white px-6 py-2 rounded font-medium hover:opacity-90">Save</button>
                    </div>

                    <!-- Warning Modal (Moved to end of form) -->
                    <div x-show="showWarning" style="display: none;"
                        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                        x-transition.opacity x-cloak>
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 transform transition-all"
                            @click.away="showWarning = false" x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

                            <div class="flex items-center gap-3 mb-4"
                                :class="pendingState ? 'text-amber-600' : 'text-brand'">
                                <template x-if="pendingState">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                </template>
                                <template x-if="!pendingState">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </template>
                                <h3 class="text-lg font-bold text-gray-900"
                                    x-text="pendingState ? 'Activate Holiday Mode?' : 'Disable Holiday Mode?'"></h3>
                            </div>

                            <p class="text-gray-600 mb-6 leading-relaxed">
                                <template x-if="pendingState">
                                    <span>Your <strong>{{ $approvedProductsCount }}</strong> approved products will be
                                        hidden from listings until you disable it.</span>
                                </template>
                                <template x-if="!pendingState">
                                    <span>Your <strong>{{ $holidayProductsCount }}</strong> products will be visible to
                                        buyers again.</span>
                                </template>
                            </p>

                            <div class="flex justify-end gap-3">
                                <button type="button" @click="showWarning = false"
                                    class="px-4 py-2 text-gray-700 font-medium hover:bg-gray-100 rounded-lg transition-colors">
                                    Cancel
                                </button>
                                <button type="button" @click="confirmHolidayMode()"
                                    class="px-4 py-2 text-white font-medium rounded-lg shadow-sm transition-colors"
                                    :class="pendingState ? 'bg-amber-600 hover:bg-amber-700' : 'bg-brand hover:opacity-90'">
                                    Confirm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('holidaySettings', (initialState) => ({
                    holidayMode: initialState,
                    showWarning: false,
                    pendingState: false,

                    init() {
                        console.log('Holiday Settings Initialized. State:', this.holidayMode);
                    },

                    toggleHolidayMode() {
                        // Determine what the new state WOULD be
                        this.pendingState = !this.holidayMode;
                        console.log('Toggle Clicked. Current:', this.holidayMode, 'Pending:', this.pendingState);

                        // Always show warning/confirmation
                        this.showWarning = true;
                    },

                    confirmHolidayMode() {
                        console.log('Confirmed. Setting mode to:', this.pendingState);

                        // Optimistic update
                        this.holidayMode = this.pendingState;
                        this.showWarning = false;

                        // Send Request
                        fetch('{{ route("settings.account.holiday-mode") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                holiday_mode: this.pendingState
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Server response:', data);
                                // Optional: Show a toast notification here
                                // If we had a toast system: showToast(data.message, 'success');
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                // Revert on error
                                this.holidayMode = !this.pendingState;
                                alert('Something went wrong. Please try again.');
                            });
                    }
                }));
            });
        </script>
    @endpush
@endsection