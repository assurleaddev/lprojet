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
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-200 mb-3 uppercase tracking-wider">
                        Payment Summary</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Item Price</span>
                            <span class="font-medium text-gray-900">{{ number_format($order->amount, 2) }} MAD</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">
                                Shipping
                                @if($order->shippingOption)
                                    <span class="text-xs text-gray-500">({{ $order->shippingOption->label }})</span>
                                @endif
                            </span>
                            <span class="font-medium text-gray-900">{{ number_format($order->shipping_cost, 2) }}
                                MAD</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Buyer Protection Fee</span>
                            <span class="font-medium text-gray-900">{{ number_format($order->buyer_protection_fee, 2) }}
                                MAD</span>
                        </div>
                        @if($order->platform_commission > 0)
                            <div class="flex justify-between text-sm border-t border-gray-100 pt-1 mt-1">
                                <span class="text-gray-600 text-xs">Platform Commission (from Seller)</span>
                                <span
                                    class="font-medium text-gray-900 text-xs">{{ number_format($order->platform_commission, 2) }}
                                    MAD</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base font-bold border-t border-gray-200 pt-3 mt-2">
                            <span class="text-gray-900">Total</span>
                            <span class="text-vinted-teal">{{ number_format($order->total_amount, 2) }} MAD</span>
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card class="mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Customer Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium">{{ $order->user->email ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium">
                                @if($order->user->phone_number)
                                    {{ $order->user->phone_country_code }} {{ $order->user->phone_number }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-medium text-capitalize">
                                @if($order->payment_method === 'wallet')
                                    My Wallet
                                @elseif($order->payment_method === 'cod')
                                    Cash on Delivery
                                @elseif($order->payment_method === 'card')
                                    Credit Card
                                @else
                                    {{ $order->payment_method ?? 'N/A' }}
                                @endif
                            </span>
                        </div>
                        <div class="py-2">
                            <span class="text-gray-600 block mb-2">Delivery Address:</span>
                            @if($order->address)
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="font-medium">{{ $order->address->full_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $order->address->address_line_1 }}</p>
                                    @if($order->address->address_line_2)
                                        <p class="text-sm text-gray-600">{{ $order->address->address_line_2 }}</p>
                                    @endif
                                    <p class="text-sm text-gray-600">{{ $order->address->city }},
                                        {{ $order->address->postcode }}
                                    </p>
                                    <p class="text-sm text-gray-600">{{ $order->address->country }}</p>
                                </div>
                            @else
                                <span class="text-gray-500">No address provided</span>
                            @endif
                        </div>
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
                            <a href="{{ asset('storage/' . $order->delivery_receipt_path) }}" target="_blank"
                                class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View Receipt</a>
                        @endif
                        <form action="{{ route('admin.orders.uploadReceipt', $order) }}" method="POST"
                            enctype="multipart/form-data" class="mt-4">
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