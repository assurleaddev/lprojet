{{-- <x-layouts.backend-layout>
    <x-slot name="title">Add New Product</x-slot>

    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Add New Product</h2>

    <x-card>
        <form action="{{ route('admin.marketplace.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('backend.marketplace.products._form')
        </form>
    </x-card>

</x-layouts.backend-layout> --}}
<x-layouts.backend-layout>
    <x-slot name="title">Add New Product</x-slot>

    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Add New Product</h2>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('backend.marketplace.products._form')
    </form>

</x-layouts.backend-layout>
