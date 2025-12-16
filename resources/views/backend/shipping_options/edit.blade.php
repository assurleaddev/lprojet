<x-layouts.backend-layout>
    <x-slot name="title">{{ __('Edit Shipping Option') }}</x-slot>

    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Edit Shipping Option') }}</h2>
    </div>

    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <form action="{{ route('admin.shipping-options.update', $shippingOption) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('backend.shipping_options._form', ['shippingOption' => $shippingOption])
        </form>
    </div>

</x-layouts.backend-layout>