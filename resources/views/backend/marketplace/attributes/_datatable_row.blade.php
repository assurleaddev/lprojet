<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
    <td class="w-4 p-4">
        <input type="checkbox" wire:model.live="selected" value="{{ $attribute->id }}" class="form-checkbox">
    </td>
    {{-- Name --}}
    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
        <div class="flex items-center gap-2">
            @if($attribute->icon)
                <i class="{{ $attribute->icon }}"></i>
            @endif
            <span>{{ $attribute->name }}</span>
        </div>
    </td>
    {{-- Type --}}
    <td class="px-6 py-4">
        @if($attribute->type)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                {{ $attribute->type }}
            </span>
        @else
            <span class="text-gray-400">—</span>
        @endif
    </td>
    {{-- Code --}}
    <td class="px-6 py-4 font-mono text-sm text-gray-500">
        {{ $attribute->code ?: '—' }}
    </td>
    {{-- Options --}}
    <td class="px-6 py-4">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 shrink-0">
                {{ $attribute->options_count }}
            </span>
            @if($attribute->options->isNotEmpty())
                <span class="text-xs text-gray-500 line-clamp-1" title="{{ $attribute->options->pluck('value')->implode(', ') }}">
                    {{ $attribute->options->pluck('value')->implode(', ') }}
                </span>
            @endif
        </div>
    </td>
    {{-- Actions --}}
    <td class="px-6 py-4 text-right">
        @include('backend.livewire.datatable.action-buttons', ['item' => $attribute, 'routePrefix' => 'admin.marketplace.attributes'])
    </td>
</tr>
