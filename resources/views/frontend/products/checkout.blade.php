@extends('layouts.app')

@section('content')
    <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-3 gap-6 mx-auto">
        <!-- Left Section -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Section -->
            <div class="bg-white rounded-lg shadow-sm p-4 flex gap-4">
                <img src="{{ $product->getFeaturedImageUrl('preview') }}" alt="See inside science book"
                    class="w-20 h-28 rounded object-cover border" />
                <div>
                    <h2 class="font-semibold text-gray-800">{{ $product->name }}</h2>
                    <div class="space-y-3 text-sm border-b border-vinted-gray-200 pb-4 mb-4">
                        <div class="flex"><span class="w-1/3 text-vinted-gray-500">TAILLE</span> <span
                                class="text-vinted-gray-700">{{ $product->size }}</span></div>
                        <div class="flex"><span class="w-1/3 text-vinted-gray-500">ÉTAT</span> <span
                                class="text-vinted-gray-900 font-semibold">{{ $product->condition }}</span></div>
                        <div class="flex"><span class="w-1/3 text-vinted-gray-500 mr-4">COULEUR</span> <span
                                class="text-vinted-gray-700">{{ $product->color }}</span></div>
                        <div class="flex"><span class="w-1/3 text-vinted-gray-500">Brand</span> <span
                                class="text-vinted-gray-700">{{ $product->brand }}</span></div>
                    </div>
                    <p class="mt-2 font-medium">£1.20</p>
                </div>
            </div>


            <!-- Address Section -->
            <div class="bg-white rounded-lg shadow-sm p-4 space-y-3">
                <h3 class="font-semibold text-gray-800">Address</h3>
                <button class="w-full border rounded-lg p-3 flex justify-between items-center hover:bg-gray-50">
                    Add your address <span class="text-xl">＋</span>
                </button>
            </div>


            <!-- Delivery Option -->
            <div class="bg-white rounded-lg shadow-sm p-4 space-y-3">
                <h3 class="font-semibold text-gray-800">Delivery option</h3>
                <div class="space-y-2">
                    <label class="flex items-center justify-between p-3 border rounded-lg cursor-pointer">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="delivery" value="pickup" />
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>

                                Ship to pick-up point
                            </span>
                        </div>
                        <span class="text-sm text-gray-500">From £0.00</span>
                    </label>


                    <label
                        class="flex items-center justify-between p-3 border rounded-lg bg-teal-50 border-teal-500 cursor-pointer">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="delivery" value="home" checked />
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>

                                Ship to home
                            </span>
                        </div>
                        <span class="text-sm text-gray-500">From £1.49</span>
                    </label>
                </div>
            </div>


            <!-- Delivery Details -->
            <div class="bg-white rounded-lg shadow-sm p-4 space-y-3">
                <h3 class="font-semibold text-gray-800">Delivery details</h3>
                <div class="flex justify-between items-center border rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="evri-logo.png" alt="Evri" class="h-5 object-contain" />
                        <div>
                            <p class="font-medium">Evri Home Delivery</p>
                            <p class="text-xs text-red-500">-50%</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-medium">£1.49</p>
                        <p class="text-xs line-through text-gray-400">£2.99</p>
                    </div>
                </div>
                <p class="text-sm text-gray-500 pl-1">Home delivery, 3–5 business days</p>
            </div>


            <!-- Contact Details -->
            <div class="bg-white rounded-lg shadow-sm p-4 space-y-3">
                <h3 class="font-semibold text-gray-800">Your contact details</h3>
                <button class="w-full border rounded-lg p-3 flex justify-between items-center hover:bg-gray-50">
                    Add a phone number <span class="text-xl">＋</span>
                </button>
            </div>


            <!-- Payment -->
            <div class="bg-white rounded-lg shadow-sm p-4 space-y-3">
                <h3 class="font-semibold text-gray-800">Payment</h3>
                <div class="flex items-center gap-3 border rounded-lg p-3">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>

                    </span>
                    <div>
                        <p class="font-medium">Credit card</p>
                        <p class="text-sm text-gray-500">Use a credit or debit card</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 border rounded-lg p-3">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>

                    </span>
                    <div>
                        <p class="font-medium">Cash on delivery</p>
                        <p class="text-sm text-gray-500">Pay when good are delivered</p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Right Section -->
        <div>
            <div class="bg-white rounded-lg shadow-sm p-4 space-y-3">
                <h3 class="font-semibold text-gray-800">Price summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Order</span><span>£1.20</span></div>
                    <div class="flex justify-between"><span>Buyer Protection fee</span><span>£0.76</span></div>
                    <div class="flex justify-between"><span>Shipping</span><span>£1.49</span></div>
                </div>
                <div class="bg-green-50 text-green-700 p-2 rounded-md text-sm flex items-center gap-2">
                    <span>💰</span> You saved £1.50 on shipping
                </div>
                <div class="flex justify-between font-semibold text-lg pt-2">
                    <span>Total to pay</span>
                    <span>£3.45</span>
                </div>
                <button class="w-full bg-vinted-teal hover:bg-vinted-teal-dark text-white py-2 rounded-lg font-medium">
                    Pay
                </button>
                <p class="text-xs text-center text-gray-500">
                    Your payment details are encrypted and secure
                </p>
            </div>
        </div>
    </div>
@endsection
