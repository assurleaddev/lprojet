<x-layouts.backend-layout>
    <x-slot name="title">Shipping Options</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Shipping Options</h2>
    </div>

    @livewire('datatable.shipping-option-datatable')

</x-layouts.backend-layout>