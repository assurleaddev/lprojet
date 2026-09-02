<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
    <td class="w-4 p-4">
        <input type="checkbox" wire:model.live="selected" value="{{ $brand->id }}" class="form-checkbox">
    </td>
    {{-- Name --}}
    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
        {{ $brand->name }}
    </td>
    {{-- Slug --}}
    <td class="px-6 py-4 font-mono text-sm text-gray-500">
        {{ $brand->slug }}
    </td>
    {{-- Products count --}}
    <td class="px-6 py-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
            {{ $brand->products_count }}
        </span>
    </td>
    {{-- Created --}}
    <td class="px-6 py-4 text-sm text-gray-500">
        {{ $brand->created_at?->diffForHumans() }}
    </td>
    {{-- Actions --}}
    <td class="px-6 py-4 text-right">
        @include('backend.livewire.datatable.action-buttons', ['item' => $brand, 'routePrefix' => 'admin.brands'])
    </td>
</tr>
