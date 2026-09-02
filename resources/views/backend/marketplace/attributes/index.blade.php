<x-layouts.backend-layout>
    <x-slot name="title">{{ __('Attributes') }}</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Attributes') }}</h2>
    </div>

    @livewire('datatable.attribute-datatable')
</x-layouts.backend-layout>
