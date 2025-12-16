<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
    <td class="w-4 p-4">
        <input type="checkbox" wire:model.live="selected" value="{{ $option->id }}" class="form-checkbox">
    </td>
    {{-- Label Column --}}
    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
        <div class="flex items-center gap-2">
            @if($option->icon_class)
                <div class="rounded p-1 {{ $option->icon_class }}" style="width: 24px; height: 16px;"></div>
            @endif
            <span>{{ $option->label }}</span>
        </div>
    </td>
    {{-- Type Column --}}
    <td class="px-6 py-4">
        @if ($option->type == 'home_pickup')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Home Pickup
            </span>
        @else
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Drop Off
            </span>
        @endif
    </td>
    {{-- Key Column --}}
    <td class="px-6 py-4 font-mono text-sm text-gray-500">
        {{ $option->key }}
    </td>
    {{-- Status Column --}}
    <td class="px-6 py-4">
        @if ($option->is_active)
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Active
            </span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                Inactive
            </span>
        @endif
    </td>
    {{-- Actions Column --}}
    <td class="px-6 py-4 text-right">
        @include('backend.livewire.datatable.action-buttons', ['item' => $option, 'routePrefix' => 'admin.shipping-options'])
    </td>
</tr>