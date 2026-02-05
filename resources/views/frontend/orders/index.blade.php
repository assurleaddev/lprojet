@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">My Orders</h1>

        <div x-data="{ activeTab: 'purchases' }">
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'purchases'"
                        :class="{ 'border-vinted-teal text-vinted-teal': activeTab === 'purchases', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'purchases' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        My Purchases
                        <span
                            class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $purchases->count() }}</span>
                    </button>

                    <button @click="activeTab = 'sales'"
                        :class="{ 'border-vinted-teal text-vinted-teal': activeTab === 'sales', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'sales' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        My Sales
                        <span
                            class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $sales->count() }}</span>
                    </button>
                </nav>
            </div>

            <!-- Purchases Content -->
            <div x-show="activeTab === 'purchases'" x-transition.opacity>
                @if($purchases->count() > 0)
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($purchases as $order)
                                <li>
                                    <a href="{{ route('products.show', $order->product) }}"
                                        class="block hover:bg-gray-50 transition">
                                        <div class="px-4 py-4 sm:px-6">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-16 w-16">
                                                        <img class="h-16 w-16 rounded object-cover"
                                                            src="{{ $order->product->getFeaturedImageUrl('preview') }}" alt="">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-vinted-teal truncate">
                                                            {{ $order->product->name }}</div>
                                                        <div class="flex items-center mt-1">
                                                            <div class="text-sm text-gray-500">
                                                                Bought from <span
                                                                    class="font-medium text-gray-900">{{ $order->vendor->username ?? 'Unknown' }}</span>
                                                            </div>
                                                            <span class="mx-2 text-gray-300">&bull;</span>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $order->created_at->format('M d, Y') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ml-2 flex-shrink-0 flex flex-col items-end">
                                                    <div
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        {{ ucfirst($order->status) }}
                                                    </div>
                                                    <div class="mt-2 text-sm font-bold text-gray-900">
                                                        {{ number_format($order->amount, 2) }} MAD
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No purchases yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Start exploring products tailored just for you.</p>
                        <div class="mt-6">
                            <a href="{{ route('home') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-vinted-teal hover:bg-vinted-teal-dark">
                                Browser Items
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sales Content -->
            <div x-show="activeTab === 'sales'" x-transition.opacity style="display: none;">
                @if($sales->count() > 0)
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($sales as $order)
                                <li>
                                    <div class="block hover:bg-gray-50 transition">
                                        <div class="px-4 py-4 sm:px-6">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-16 w-16">
                                                        <a href="{{ route('products.show', $order->product) }}">
                                                            <img class="h-16 w-16 rounded object-cover"
                                                                src="{{ $order->product->getFeaturedImageUrl('preview') }}" alt="">
                                                        </a>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-vinted-teal truncate">
                                                            {{ $order->product->name }}</div>
                                                        <div class="flex items-center mt-1">
                                                            <div class="text-sm text-gray-500">
                                                                Sold to <span
                                                                    class="font-medium text-gray-900">{{ $order->user->username ?? 'Unknown' }}</span>
                                                            </div>
                                                            <span class="mx-2 text-gray-300">&bull;</span>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $order->created_at->format('M d, Y') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ml-2 flex-shrink-0 flex flex-col items-end">
                                                    <div
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        {{ ucfirst($order->status) }}
                                                    </div>
                                                    <div class="mt-2 text-sm font-bold text-gray-900">
                                                        {{ number_format($order->amount, 2) }} MAD
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No sales yet</h3>
                        <p class="mt-1 text-sm text-gray-500">List more items to increase your meaningful sales.</p>
                        <div class="mt-6">
                            <a href="{{ route('items.create') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-vinted-teal hover:bg-vinted-teal-dark">
                                Sell Now
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection