<x-layouts.backend-layout>
    <x-slot name="title">Order #{{ $order->id }}</x-slot>
    
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Order Details: #{{ $order->id }}</h2>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <x-card>
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Product in this Order</h3>
                    <div class="flex justify-between items-center py-2">
                        <div>
                            <p class="font-semibold">{{ $order->product->name ?? 'Product not found' }}</p>
                            <p class="text-sm text-gray-500">Vendor : {{ $order->vendor->fullname ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">Customer: {{ $order->user->fullname ?? 'N/A' }}</p>
                        </div>
                        <span class="font-semibold">${{ number_format($order->amount, 2) }}</span>
                    </div>
                </div>
            </x-card>
        </div>
       
        <div>
            <x-card class="mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Update Status</h3>
                    <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-input mb-2">
                            <option value="pending" @selected($order->status == 'pending')>Pending</option>
                            <option value="processing" @selected($order->status == 'processing')>Processing</option>
                            <option value="shipped" @selected($order->status == 'shipped')>Shipped</option>
                            <option value="delivered" @selected($order->status == 'delivered')>Delivered</option>
                            <option value="cancelled" @selected($order->status == 'cancelled')>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary w-full">Update Status</button>
                    </form>
                </div>
            </x-card>

            @if(in_array($order->status, ['shipped', 'delivered']))
            <x-card>
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delivery Receipt</h3>
                    @if($order->delivery_receipt_path)
                        <p class="mb-2">A receipt has been uploaded.</p>
                        <a href="{{ asset('storage/' . $order->delivery_receipt_path) }}" target="_blank" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View Receipt</a>
                    @endif
                    <form action="{{ route('admin.orders.uploadReceipt', $order) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <label for="receipt" class="form-label">Upload New Receipt</label>
                        <input type="file" name="receipt" class="form-input mb-2">
                        <button type="submit" class="btn btn-secondary w-full">Upload</button>
                         @error('receipt')<span class="text-red-500 text-sm mt-1">{{ $message }}</span>@enderror
                    </form>
                </div>
            </x-card>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>