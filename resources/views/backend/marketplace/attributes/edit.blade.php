<x-layouts.backend-layout>
    <x-slot name="title">Edit Attribute</x-slot>

    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Edit Attribute</h2>

    <x-card>
        <form action="{{ route('admin.marketplace.attributes.update', $attribute) }}" method="POST">
            @csrf
            @method('PUT')
            @include('backend.marketplace.attributes._form', ['attribute' => $attribute])
        </form>
    </x-card>

</x-layouts.backend-layout>