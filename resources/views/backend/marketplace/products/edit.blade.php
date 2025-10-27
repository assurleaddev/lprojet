<x-layouts.backend-layout>
    <x-slot name="title">Edit Product</x-slot>

    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Edit Product</h2>

    <x-card>
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('backend.marketplace.products._form', ['product' => $product])
        </form>
    </x-card>

</x-layouts.backend-layout>