<x-layouts.backend-layout>
    <x-slot name="title">Edit Category</x-slot>

    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Edit Category</h2>

    <x-card>
        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')
            @include('backend.marketplace.categories._form', ['category' => $category])
        </form>
    </x-card>

</x-layouts.backend-layout>