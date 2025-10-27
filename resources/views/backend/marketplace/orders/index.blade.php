<x-layouts.backend-layout>
    <x-slot name="title">Orders</x-slot>
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Order Management</h2>
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Order ID</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">#{{ $order->id }}</td>
                            <td class="px-6 py-4">{{ $order->user->fullname }}</td>
                            {{-- Add Product column --}}
                            <td class="px-6 py-4 font-medium">{{ $order->product->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">${{ number_format($order->amount, 2) }}</td>
                            <td class="px-6 py-4">{{ ucfirst($order->status) }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                    class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $orders->links() }}</div>
    </x-card>
</x-layouts.backend-layout>
