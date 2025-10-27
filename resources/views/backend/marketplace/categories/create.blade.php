<x-layouts.backend-layout>
    <x-slot name="title">Add New Category</x-slot>

    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Add New Category</h2>

    <x-card>
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            @include('backend.marketplace.categories._form')
        </form>
    </x-card>

</x-layouts.backend-layout>