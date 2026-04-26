<x-layouts.backend-layout>
    <x-slot name="title">Claims</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Claims</h2>
        <div class="flex gap-2">
            @foreach(['', 'pending', 'under_review', 'resolved', 'rejected'] as $s)
                <a href="{{ route('admin.claims.index', $s ? ['status' => $s] : []) }}"
                    class="px-3 py-1.5 text-sm rounded-md font-medium transition-colors
                        {{ ($status ?? '') === $s
                            ? 'bg-gray-900 text-white'
                            : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                    {{ $s ? ucfirst(str_replace('_', ' ', $s)) : 'All' }}
                </a>
            @endforeach
        </div>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Buyer</th>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Photos</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($claims as $claim)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $claim->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $claim->user->full_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $claim->user->email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $claim->order_id) }}" class="text-gray-900 dark:text-white hover:underline font-medium">
                                    #{{ $claim->order_id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <p class="truncate text-gray-600 dark:text-gray-300">{{ $claim->description }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $claim->photos ? count($claim->photos) : 0 }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $colors = [
                                        'pending'      => 'bg-yellow-100 text-yellow-800',
                                        'under_review' => 'bg-blue-100 text-blue-800',
                                        'resolved'     => 'bg-green-100 text-green-800',
                                        'rejected'     => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$claim->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst(str_replace('_', ' ', $claim->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $claim->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.claims.show', $claim) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:underline">
                                    Review →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-400">No claims found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($claims->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $claims->appends(request()->query())->links() }}</div>
        @endif
    </x-card>
</x-layouts.backend-layout>
