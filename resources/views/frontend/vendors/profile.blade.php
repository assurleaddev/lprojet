@extends('layouts.app')

@section('before_head')
    <style>
        /* micro-tuning to match screenshot rhythm */
        .tight {
            letter-spacing: -.01em
        }

        .shadow-card {
            box-shadow: 0 1px 0 0 rgba(17, 24, 28, .08)
        }

        .ring-muted {
            box-shadow: inset 0 0 0 1px rgba(17, 24, 28, .08)
        }
    </style>
@endsection

@section('content')
    <div class="mx-auto max-w-[1200px] px-6 md:px-10">

        <!-- Top spacing like screenshot -->
        <div class="h-6"></div>

        <!-- Header row -->
        <div class="grid grid-cols-12 items-start gap-x-8">
            <!-- “Logo” block (left) -->
            <div class="col-span-2 flex items-center justify-center">
                <img class="h-40 w-40 rounded-full object-cover" src="{{ $user->avatar_url }}" alt="avatar">
            </div>

            <!-- Profile summary -->
            <div class="col-span-8">
                <div class="flex items-center gap-4">
                    <h1 class="tight text-[28px] font-semibold">{{ $user->username }}</h1>

                    <!-- Stars + reviews -->
                    <div class="flex items-center gap-1 text-amber-400">
                        <!-- five stars -->
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="m9.05 2.927 1.3 2.638c.18.365.527.619.93.678l2.91.423c1.014.147 1.419 1.394.685 2.11l-2.104 2.051c-.292.285-.425.695-.356 1.096l.497 2.897c.173 1.006-.883 1.772-1.787 1.298l-2.6-1.366a1.25 1.25 0 0 0-1.164 0l-2.6 1.366c-.904.474-1.96-.292-1.788-1.298l.498-2.897a1.25 1.25 0 0 0-.357-1.096L1.17 8.776c-.733-.716-.327-1.963.686-2.11l2.91-.423a1.25 1.25 0 0 0 .93-.678l1.3-2.638a1.25 1.25 0 0 1 2.254 0Z" />
                        </svg>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="m9.05 2.927 1.3 2.638c.18.365.527.619.93.678l2.91.423c1.014.147 1.419 1.394.685 2.11l-2.104 2.051c-.292.285-.425.695-.356 1.096l.497 2.897c.173 1.006-.883 1.772-1.787 1.298l-2.6-1.366a1.25 1.25 0 0 0-1.164 0l-2.6 1.366c-.904.474-1.96-.292-1.788-1.298l.498-2.897a1.25 1.25 0 0 0-.357-1.096L1.17 8.776c-.733-.716-.327-1.963.686-2.11l2.91-.423a1.25 1.25 0 0 0 .93-.678l1.3-2.638a1.25 1.25 0 0 1 2.254 0Z" />
                        </svg>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="m9.05 2.927 1.3 2.638c.18.365.527.619.93.678l2.91.423c1.014.147 1.419 1.394.685 2.11l-2.104 2.051c-.292.285-.425.695-.356 1.096l.497 2.897c.173 1.006-.883 1.772-1.787 1.298l-2.6-1.366a1.25 1.25 0 0 0-1.164 0l-2.6 1.366c-.904.474-1.96-.292-1.788-1.298l.498-2.897a1.25 1.25 0 0 0-.357-1.096L1.17 8.776c-.733-.716-.327-1.963.686-2.11l2.91-.423a1.25 1.25 0 0 0 .93-.678l1.3-2.638a1.25 1.25 0 0 1 2.254 0Z" />
                        </svg>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="m9.05 2.927 1.3 2.638c.18.365.527.619.93.678l2.91.423c1.014.147 1.419 1.394.685 2.11l-2.104 2.051c-.292.285-.425.695-.356 1.096l.497 2.897c.173 1.006-.883 1.772-1.787 1.298l-2.6-1.366a1.25 1.25 0 0 0-1.164 0l-2.6 1.366c-.904.474-1.96-.292-1.788-1.298l.498-2.897a1.25 1.25 0 0 0-.357-1.096L1.17 8.776c-.733-.716-.327-1.963.686-2.11l2.91-.423a1.25 1.25 0 0 0 .93-.678l1.3-2.638a1.25 1.25 0 0 1 2.254 0Z" />
                        </svg>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="m9.05 2.927 1.3 2.638c.18.365.527.619.93.678l2.91.423c1.014.147 1.419 1.394.685 2.11l-2.104 2.051c-.292.285-.425.695-.356 1.096l.497 2.897c.173 1.006-.883 1.772-1.787 1.298l-2.6-1.366a1.25 1.25 0 0 0-1.164 0l-2.6 1.366c-.904.474-1.96-.292-1.788-1.298l.498-2.897a1.25 1.25 0 0 0-.357-1.096L1.17 8.776c-.733-.716-.327-1.963.686-2.11l2.91-.423a1.25 1.25 0 0 0 .93-.678l1.3-2.638a1.25 1.25 0 0 1 2.254 0Z" />
                        </svg>
                    </div>

                    <a href="#reviews" class="text-teal-700 hover:underline text-[15px]">{{ $stats['total'] }} reviews</a>
                </div>

                <!-- badges row -->
                <div class="mt-5 grid grid-cols-2 gap-6 max-w-3xl">
                    <!-- Frequent Uploads -->
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 h-9 w-9 shrink-0 rounded-full bg-cyan-100 grid place-items-center text-cyan-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h10v2H4v-2Z" />
                            </svg>
                        </div>
                        <div class="-mt-0.5">
                            <div class="font-semibold">Frequent Uploads</div>
                            <div class="text-[13px] text-zinc-600">Regularly lists 5 or more items.</div>
                        </div>
                    </div>

                    <!-- Speedy Shipping -->
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 h-9 w-9 shrink-0 rounded-full bg-cyan-100 grid place-items-center text-cyan-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 13h11a4 4 0 0 1 4 4v1H9a4 4 0 0 1-4-4v-1Zm0-6h12v2H3V7Zm16 2h2l2 3v6h-4V9Z" />
                            </svg>
                        </div>
                        <div class="-mt-0.5">
                            <div class="font-semibold">Speedy Shipping</div>
                            <div class="text-[13px] text-zinc-600">Sends items promptly — usually within the next 24 hours.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About / Verified info -->
                <div class="mt-6 grid grid-cols-2 gap-10 max-w-3xl">
                    <div>
                        <div class="text-sm font-semibold text-zinc-700 mb-2">About:</div>
                        <div class="flex items-center gap-2 text-[15px]">
                            <svg class="h-4 w-4 text-zinc-600" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z" />
                            </svg>
                            Bradford, United Kingdom
                        </div>
                        <div class="mt-1 flex items-center gap-2 text-[15px]">
                            <svg class="h-4 w-4 text-zinc-600" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm-1-6h2v4h-2V2Zm0 16h2v4h-2v-4ZM2 11h4v2H2v-2Zm16 0h4v2h-4v-2Zm-9.78-6.36 1.41-1.41 2.83 2.83-1.41 1.41-2.83-2.83Zm8.49 11.32 2.83 2.83-1.41 1.41-2.83-2.83 1.41-1.41Z" />
                            </svg>
                            Last seen 3 hours ago
                        </div>
                        <livewire:follower-stats :user="$user" />
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-zinc-700 mb-2">Verified info:</div>
                        <div class="flex items-center gap-2 text-[15px]">
                            <svg class="h-4 w-4 text-zinc-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 12 7 10l-2 2 4 4 8-8-2-2-6 6Z" />
                            </svg>
                            Email
                        </div>
                    </div>
                </div>
            </div>

            <!-- Follow button (right) -->
            <div class="col-span-2 flex justify-end">
                <div class="relative flex items-center gap-3">
                    <livewire:follow-button :user="$user" />

                    <!-- Report dropdown trigger -->
                    <button id="reportBtn"
                        class="h-10 w-10 grid place-items-center rounded-md ring-muted text-zinc-700 hover:bg-zinc-50"
                        aria-haspopup="true" aria-expanded="false">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" />
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div id="reportMenu"
                        class="invisible opacity-0 pointer-events-none absolute right-0 top-12 w-44 rounded-lg border border-zinc-200 bg-white py-2 text-sm shadow-lg transition">
                        <div class="px-4 pb-2 text-zinc-400 text-[12px]">Report</div>
                        <a href="#" class="block px-4 py-2 hover:bg-zinc-50">Report this user</a>
                        <a href="#" class="block px-4 py-2 hover:bg-zinc-50">Block user</a>
                    </div>

                </div>
            </div>
        </div>

        <!-- ============ TABS (nav) ============ -->
        <div class="mt-10 border-b border-zinc-200" role="tablist" aria-label="Profile sections">
            <nav class="-mb-px flex gap-10">
                <button id="tab-listings" role="tab" aria-controls="panel-listings" aria-selected="true"
                    class="pb-3 font-medium tab-active focus:outline-none">
                    Listings
                </button>
                <button id="tab-reviews" role="tab" aria-controls="panel-reviews" aria-selected="false"
                    class="pb-3 font-medium tab-inactive focus:outline-none hover:text-zinc-900">
                    Reviews
                </button>
            </nav>
        </div>

        <!-- ============ PANELS (content) ============ -->

        <!-- Listings panel -->
        <section id="panel-listings" role="tabpanel" aria-labelledby="tab-listings" class="pt-6">
            <div class="text-sm text-zinc-700 mb-3">3 items</div>
            <div class="grid grid-cols-5 gap-6">
                @forelse ($user->products as $item)
                    <a href="{{ route('products.show', $item) }}"
                        class="flex-shrink-0 w-full block hover:opacity-80 transition">
                        <img src="{{ $item->getFeaturedImageUrl() }}" alt="Product" class="w-full h-56 object-cover">
                        @if($item->status === 'sold')
                            <div class="text-white text-[11px] font-bold px-3 py-1.5 mb-2" style="background-color: #4fb286 !important;">
                                Vendus
                            </div>
                        @elseif($item->status === 'reserved')
                            <div class="text-white text-[11px] font-bold px-3 py-1.5 mb-2" style="background-color: #f59e0b !important;">
                                Réservé
                            </div>
                        @else
                            <div class="mb-2"></div>
                        @endif
                        <p class="font-bold text-sm">{{ $item->price }} MAD</p>
                        <p class="text-xs text-vinted-gray-500">
                            {{ $item->options->groupBy('attribute_id')->map(fn($grp) => $grp->pluck('value')->implode(' / '))->implode(' · ') }}
                        </p>
                        <p class="text-xs text-vinted-gray-500">{{ $user->username }}</p>
                    </a>
                @empty
                    <div class="col-span-5 text-center text-gray-500">no products yet</div>
                @endforelse
            </div>
            <div class="h-16"></div>
        </section>

        <!-- Reviews panel -->
        <section id="panel-reviews" role="tabpanel" aria-labelledby="tab-reviews" class="pt-6 hidden">

            @if(isset($pendingReviewOrder) && $pendingReviewOrder)
                <div class="mb-8 bg-teal-50 border border-teal-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-teal-900 mb-2">Leave a review for your recent purchase</h3>
                    <p class="text-sm text-teal-700 mb-4">
                        You recently received an item from {{ $user->username }}. Please let us know how it went!
                    </p>

                    <form action="{{ route('reviews.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $pendingReviewOrder->id }}">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                            <div class="flex items-center space-x-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" required {{ $i === 5 ? 'checked' : '' }}>
                                        <svg class="w-8 h-8 text-gray-300 peer-checked:text-amber-400 hover:text-amber-300 transition-colors"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.733-.716-.327-1.963.686-2.11l2.91-.423a1.25 1.25 0 0 0 .93-.678l1.3-2.638a1.25 1.25 0 0 1 2.254 0Z" />
                                        </svg>
                                    </label>
                                @endfor
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="review" class="block text-sm font-medium text-gray-700 mb-1">Review (Required)</label>
                            <textarea name="review" id="review" rows="3" required minlength="5"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                placeholder="Write your review here..."></textarea>
                        </div>

                        <button type="submit"
                            class="px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-md hover:bg-teal-700 shadow-sm">
                            Submit Review
                        </button>
                    </form>
                </div>
            @endif

            <div class="grid grid-cols-12">
                <div class="col-span-8">
                    <div class="flex items-end gap-10">
                        <div class="flex items-center gap-4">
                            <div class="text-[72px] leading-none font-light">{{ number_format($stats['avg'], 1) }}</div>
                            <div>
                                <div class="flex gap-1 text-amber-400">
                                    {{-- Main Stars --}}
                                    @for ($i = 0; $i < 5; $i++)
                                        <svg class="h-5 w-5 {{ $i < round($stats['avg']) ? 'text-amber-400' : 'text-gray-300' }}"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="m9.05 2.927 1.3 2.638c.18.365.527.619.93.678l2.91.423c1.014.147 1.419 1.394.685 2.11l-2.104 2.051c-.292.285-.425.695-.356 1.096l.497 2.897c.173 1.006-.883 1.772-1.787 1.298l-2.6-1.366a1.25 1.25 0 0 0-1.164 0l-2.6 1.366c-.904.474-1.96-.292-1.788-1.298l.498-2.897a1.25 1.25 0 0 0-.357-1.096L1.17 8.776c-.733-.716-.327-1.963.686-2.11l2.91-.423a1.25 1.25 0 0 0 .93-.678l1.3-2.638a1.25 1.25 0 0 1 2.254 0Z" />
                                        </svg>
                                    @endfor
                                </div>
                                <div class="text-sm text-zinc-600">({{ $stats['total'] }})</div>
                            </div>
                            <div class="hidden md:block h-12 w-px bg-zinc-200"></div>
                            <div>
                                <div class="text-[15px] font-semibold">Member reviews ({{ $stats['member_count'] }})</div>
                                <div class="mt-1 flex items-center gap-2 text-[15px]">
                                    {{ number_format($stats['member_avg'], 1) }} <span class="text-amber-400">★</span>
                                </div>
                                <div class="mt-4 text-[15px] font-semibold">Automatic reviews ({{ $stats['auto_count'] }})
                                </div>
                                <div class="mt-1 flex items-center gap-2 text-[15px]">
                                    {{ number_format($stats['auto_avg'], 1) }} <span class="text-amber-400">★</span>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <a href="#" class="text-teal-700 hover:underline">How reviews work</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="h-16"></div>

            <!-- Reviews Filter and List with Alpine.js -->
            <div x-data="{ filter: 'all' }">
                <div class="flex gap-4 mb-8">
                    <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-zinc-100' : ''"
                        class="rounded-full border border-zinc-300 px-5 py-2 text-[15px] hover:bg-zinc-50 transition">
                        All
                    </button>
                    <button @click="filter = 'member'" :class="filter === 'member' ? 'bg-zinc-100' : ''"
                        class="rounded-full border border-zinc-300 px-5 py-2 text-[15px] hover:bg-zinc-50 transition">
                        From members
                    </button>
                    <button @click="filter = 'auto'" :class="filter === 'auto' ? 'bg-zinc-100' : ''"
                        class="rounded-full border border-zinc-300 px-5 py-2 text-[15px] hover:bg-zinc-50 transition">
                        Automatic
                    </button>
                </div>

                <!-- Reviews list -->
                <ul class="divide-y divide-zinc-200">
                    @forelse($reviews as $review)
                        @php
                            $isAuto = $review->is_auto || \Str::contains($review->review, 'Auto-feedback');
                        @endphp
                        <li class="py-6"
                            x-show="filter === 'all' || (filter === 'auto' && {{ $isAuto ? 'true' : 'false' }}) || (filter === 'member' && {{ $isAuto ? 'false' : 'true' }})"
                            x-transition>
                            <div class="flex items-start gap-4">
                                @if($isAuto)
                                    <!-- System Icon for Auto Review -->
                                    <div
                                        class="h-12 w-12 rounded-full bg-white border border-gray-200 grid place-items-center overflow-hidden">
                                        <img src="{{ asset('images/logo/reviews_logo_mini.png') }}" alt="{{ config('app.name') }}"
                                            class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <!-- Member Avatar -->
                                    <div
                                        class="h-12 w-12 rounded-full bg-zinc-200 grid place-items-center text-zinc-500 overflow-hidden">
                                        <img src="{{ $review->author->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($review->author->full_name ?? 'User') }}"
                                            alt="{{ $review->author->full_name ?? 'User' }}" class="w-full h-full object-cover">
                                    </div>
                                @endif

                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="font-semibold text-[16px]">
                                            {{ $isAuto ? config('app.name') : ($review->author->username ?? 'Deleted User') }}
                                        </div>
                                        <div class="text-sm text-zinc-500">{{ $review->created_at->diffForHumans() }}</div>
                                    </div>

                                    <!-- Stars -->
                                    <div class="mt-1 flex items-center gap-1 text-amber-400">
                                        @for ($i = 0; $i < 5; $i++)
                                            <svg class="h-4 w-4 {{ $i < $review->rating ? 'text-amber-400' : 'text-gray-300' }}"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    d="m9.05 2.927 1.3 2.638c.18.365.527.619.93.678l2.91.423c1.014.147 1.419 1.394.685 2.11l-2.104 2.051c-.292.285-.425.695-.356 1.096l.497 2.897c.173 1.006-.883 1.772-1.787 1.298l-2.6-1.366a1.25 1.25 0 0 0-1.164 0l-2.6 1.366c-.904.474-1.96-.292-1.788-1.298l.498-2.897a1.25 1.25 0 0 0-.357-1.096L1.17 8.776c-.733-.716-.327-1.963.686-2.11l2.91-.423a1.25 1.25 0 0 0 .93-.678l1.3-2.638a1.25 1.25 0 0 1 2.254 0Z" />
                                            </svg>
                                        @endfor
                                    </div>

                                    <p class="mt-2 text-[15px]">
                                        {{ $review->review }}
                                    </p>

                                    <button
                                        class="mt-3 inline-flex items-center gap-2 text-teal-700 hover:underline text-[14px]">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2a9 9 0 1 0 9 9h-9V2Z" />
                                        </svg>
                                        Translate this
                                    </button>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="py-6 text-center text-gray-500">
                            No reviews yet.
                        </li>
                    @endforelse
                </ul>
            </div>

        </section>
    </div>

    <!-- Overlay (unchanged) -->
    <div id="authOverlay" class="fixed inset-0 bg-black/50 hidden" aria-hidden="true"></div>

    <!-- Modal (centered) -->
    <div id="authModal" role="dialog" aria-modal="true" aria-labelledby="authTitle"
        class="fixed inset-0 z-[60] hidden grid place-items-center p-4">
        <!-- Panel -->
        <div class="w-[520px] max-w-full rounded-2xl bg-white shadow-xl max-h-[90vh] overflow-auto">
            <div class="flex justify-end p-4">
                <button id="authClose" class="h-8 w-8 grid place-items-center rounded-full hover:bg-zinc-100"
                    aria-label="Close">✕</button>
            </div>
            <div class="px-8 pb-8 -mt-4">
                <h2 id="authTitle" class="text-center text-[26px] font-semibold leading-tight">
                    Join and sell pre-loved clothes<br />with no fees
                </h2>

                <div class="mt-6 space-y-3">
                    <a href="#"
                        class="flex items-center justify-center gap-3 rounded-lg border border-zinc-300 px-4 py-3 hover:bg-zinc-50">
                        <img src="https://www.gstatic.com/images/branding/product/1x/gsa_64dp.png" class="h-5 w-5" alt="">
                        <span class="font-medium">Continue with Google</span>
                    </a>

                    <a href="#"
                        class="flex items-center justify-center gap-3 rounded-lg border border-zinc-300 px-4 py-3 hover:bg-zinc-50">
                        <span class="text-2xl"></span>
                        <span class="font-medium">Continue with Apple</span>
                    </a>

                    <a href="#"
                        class="flex items-center justify-center gap-3 rounded-lg border border-zinc-300 px-4 py-3 hover:bg-zinc-50">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 fill-[#1877F2]">
                            <path
                                d="M22 12a10 10 0 1 0-11.6 9.9v-7h-2.3V12h2.3V9.7c0-2.3 1.4-3.6 3.5-3.6 1 0 2 .2 2 .2v2.2h-1.1c-1.1 0-1.5.7-1.5 1.4V12h2.6l-.4 2.9h-2.2v7A10 10 0 0 0 22 12Z" />
                        </svg>
                        <span class="font-medium">Continue with Facebook</span>
                    </a>
                </div>

                <div class="mt-6 text-center text-[15px] text-zinc-700">
                    Or register with <a href="#" class="text-teal-700 underline">email</a>
                </div>
                <div class="mt-2 text-center text-[15px] text-zinc-700">
                    Already have an account? <a href="#" class="text-teal-700 underline">Log in</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after_body')
    <script>
        // ------- Report dropdown -------
        const reportBtn = document.getElementById('reportBtn');
        const reportMenu = document.getElementById('reportMenu');
        const closeMenu = () => reportMenu.classList.add('invisible', 'opacity-0', 'pointer-events-none');
        const openMenu = () => reportMenu.classList.remove('invisible', 'opacity-0', 'pointer-events-none');
        reportBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = !reportMenu.classList.contains('invisible');
            open ? closeMenu() : openMenu();
        });
        window.addEventListener('click', closeMenu);

        // ------- Tabs (Listings / Reviews) -------
        const tabs = {
            listings: {
                tab: document.getElementById('tab-listings'),
                panel: document.getElementById('panel-listings'),
                hash: '#listings'
            },
            reviews: {
                tab: document.getElementById('tab-reviews'),
                panel: document.getElementById('panel-reviews'),
                hash: '#reviews'
            }
        };

        function setActive(name, push = true) {
            for (const key in tabs) {
                const {
                    tab,
                    panel
                } = tabs[key];
                const active = key === name;
                tab.classList.toggle('tab-active', active);
                tab.classList.toggle('tab-inactive', !active);
                tab.setAttribute('aria-selected', String(active));
                panel.classList.toggle('hidden', !active);
            }
            if (push) {
                history.pushState({
                    tab: name
                }, '', tabs[name].hash);
            }
        }

        // Click to switch
        tabs.listings.tab.addEventListener('click', () => setActive('listings'));
        tabs.reviews.tab.addEventListener('click', () => setActive('reviews'));

        // Keyboard arrows (ARIA-ish)
        document.querySelector('[role="tablist"]').addEventListener('keydown', (e) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return;
            e.preventDefault();
            const order = ['listings', 'reviews'];
            const current = order.findIndex(k => tabs[k].tab.getAttribute('aria-selected') === 'true');
            let next = current;
            if (e.key === 'ArrowRight') next = (current + 1) % order.length;
            if (e.key === 'ArrowLeft') next = (current - 1 + order.length) % order.length;
            if (e.key === 'Home') next = 0;
            if (e.key === 'End') next = order.length - 1;
            tabs[order[next]].tab.focus();
            setActive(order[next]);
        });

        // Back/forward support
        window.addEventListener('popstate', (e) => {
            const name = (location.hash === '#reviews') ? 'reviews' : 'listings';
            setActive(name, /*push*/ false);
        });

        // Initial tab from hash
        setActive(location.hash === '#reviews' ? 'reviews' : 'listings', /*push*/ false);
    </script>

    <script>
        (function () {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const modal = document.getElementById('authModal');
            const overlay = document.getElementById('authOverlay');
            const close = document.getElementById('authClose');

            function openAuth() {
                modal.classList.remove('hidden');
                overlay.classList.remove('hidden');
            }

            function closeAuth() {
                modal.classList.add('hidden');
                overlay.classList.add('hidden');
            }
            close?.addEventListener('click', closeAuth);
            overlay?.addEventListener('click', closeAuth);
            window.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeAuth();
            });

            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('.follow-btn');
                if (!btn) return;

                const isAuth = btn.dataset.auth === '1';
                const isSelf = btn.dataset.self === '1';
                const url = btn.dataset.url || `/users/${btn.dataset.userId}/follow`;
                const label = btn.querySelector('.label');

                // Not logged in → open modal and stop
                if (!isAuth) {
                    openAuth();
                    return;
                }

                // Prevent self follow (optional: toast)
                if (isSelf) {
                    return;
                }

                // Toggle follow via AJAX
                btn.disabled = true;
                btn.classList.add('opacity-70');
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        }
                    });

                    // If session expired, show modal instead of redirect
                    if (res.status === 401) {
                        openAuth();
                        return;
                    }

                    // If server returned 422 (self follow), just stop
                    if (res.status === 422) {
                        return;
                    }

                    const data = await res.json(); // { following: bool, followers_count: int }
                    btn.setAttribute('aria-pressed', data.following ? 'true' : 'false');
                    label.textContent = data.following ? 'Following' : 'Follow';
                } catch (err) {
                    console.error(err);
                } finally {
                    btn.disabled = false;
                    btn.classList.remove('opacity-70');
                }
            });
        })();
    </script>
@endsection