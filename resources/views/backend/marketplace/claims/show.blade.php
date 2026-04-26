<x-layouts.backend-layout>
    <x-slot name="title">Claim #{{ $claim->id }}</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Claim #{{ $claim->id }}</h2>
        <a href="{{ route('admin.claims.index') }}" class="btn btn-secondary">← Back to Claims</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Claim Details --}}
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <div class="p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-3">Buyer's Description</h3>
                    <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $claim->description }}</p>
                </div>
            </x-card>

            @if($claim->photos && count($claim->photos) > 0)
                <x-card>
                    <div class="p-6">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-3">Photos ({{ count($claim->photos) }})</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($claim->photos as $photo)
                                <a href="{{ Storage::url($photo) }}" target="_blank">
                                    <img src="{{ Storage::url($photo) }}" alt="Claim photo"
                                        class="w-full h-40 object-cover rounded-lg border border-gray-200 hover:opacity-90 transition">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            @endif

            {{-- Order Summary --}}
            <x-card>
                <div class="p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-3">Order</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Order ID</span>
                            <a href="{{ route('admin.orders.show', $claim->order_id) }}" class="font-medium text-gray-900 dark:text-white hover:underline">
                                #{{ $claim->order_id }}
                            </a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Buyer</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $claim->user->full_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Seller</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $claim->order->vendor->full_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Product</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $claim->order->product->name ?? 'Bundle' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Order Total</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($claim->order->total_amount, 2) }} MAD</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Payment</span>
                            <span class="font-medium text-gray-900 dark:text-white uppercase text-xs">{{ $claim->order->payment_method }}</span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Right: Update Status --}}
        <div class="space-y-6">
            <x-card>
                <div class="p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-4">Update Claim</h3>
                    <form action="{{ route('admin.claims.update', $claim) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-gray-900 focus:border-gray-900 dark:bg-gray-700 dark:text-white">
                                @foreach(['pending', 'under_review', 'resolved', 'rejected'] as $s)
                                    <option value="{{ $s }}" @selected($claim->status === $s)>
                                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admin Note <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea name="admin_note" rows="4"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-gray-900 focus:border-gray-900 dark:bg-gray-700 dark:text-white"
                                placeholder="Internal note about this claim...">{{ old('admin_note', $claim->admin_note) }}</textarea>
                        </div>

                        <button type="submit"
                            class="w-full bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors">
                            Save
                        </button>
                    </form>

                    @if(session('success'))
                        <p class="mt-3 text-sm text-green-600">{{ session('success') }}</p>
                    @endif
                </div>
            </x-card>

            <x-card>
                <div class="p-4">
                    <p class="text-xs text-gray-400">Submitted {{ $claim->created_at->diffForHumans() }}</p>
                    @if($claim->updated_at != $claim->created_at)
                        <p class="text-xs text-gray-400 mt-1">Last updated {{ $claim->updated_at->diffForHumans() }}</p>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.backend-layout>
