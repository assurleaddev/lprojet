<x-layouts.backend-layout>
    <x-slot name="title">{{ __('Brands') }}</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Brands') }}</h2>
    </div>

    @livewire('datatable.brand-datatable')
</x-layouts.backend-layout>
