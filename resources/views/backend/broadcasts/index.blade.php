<x-layouts.backend-layout>
    <x-slot name="title">Broadcasts</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Broadcasts</h2>
        <a href="{{ route('admin.broadcasts.create') }}" class="btn btn-primary">+ New Broadcast</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-md text-sm">
            {{ session('success') }}
        </div>
    @endif

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Recipients</th>
                        <th class="px-4 py-3">Sent at</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($broadcasts as $broadcast)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3 text-gray-500">{{ $broadcast->id }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $broadcast->title }}</p>
                                <p class="text-xs text-gray-500 truncate max-w-xs">{{ $broadcast->body }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $colors = ['draft' => 'bg-gray-100 text-gray-700', 'sending' => 'bg-blue-100 text-blue-700', 'sent' => 'bg-green-100 text-green-700'];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$broadcast->status] ?? '' }}">
                                    {{ ucfirst($broadcast->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $broadcast->recipient_count > 0 ? number_format($broadcast->recipient_count) : '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $broadcast->sent_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $broadcast->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                @if($broadcast->status === 'draft')
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('admin.broadcasts.edit', $broadcast) }}"
                                            class="text-xs text-blue-600 hover:underline">Edit</a>

                                        <form action="{{ route('admin.broadcasts.send', $broadcast) }}" method="POST"
                                            onsubmit="return confirm('Send this broadcast to all users now?')">
                                            @csrf
                                            <button type="submit" class="text-xs text-green-600 hover:underline">Send</button>
                                        </form>

                                        <form action="{{ route('admin.broadcasts.destroy', $broadcast) }}" method="POST"
                                            onsubmit="return confirm('Delete this broadcast?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400">No broadcasts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($broadcasts->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $broadcasts->links() }}</div>
        @endif
    </x-card>
</x-layouts.backend-layout>
