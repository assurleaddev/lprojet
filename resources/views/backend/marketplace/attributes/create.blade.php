<x-layouts.backend-layout>
    <x-slot name="title">Add New Attribute</x-slot>

    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Add New Attribute</h2>

    <x-card>
        <form action="{{ route('admin.marketplace.attributes.store') }}" method="POST">
            @csrf
            @include('backend.marketplace.attributes._form')
        </form>
    </x-card>

</x-layouts.backend-layout>