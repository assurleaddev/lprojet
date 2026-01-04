<x-layouts.backend-layout>
    <x-slot name="title">Orders</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Order Management</h2>
    </div>

    @livewire('datatable.order-datatable', ['lazy' => true])
</x-layouts.backend-layout>