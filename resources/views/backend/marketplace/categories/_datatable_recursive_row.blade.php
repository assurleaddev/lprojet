{{-- This view will call itself to render nested categories --}}
@php
    $indentation = $level * 32; // 32px indentation per level
@endphp

<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700" x-data="{ open: false }">
    <td class="w-4 p-4">
        <input type="checkbox" wire:model.live="selected" value="{{ $category->id }}" class="form-checkbox">
    </td>
    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
        <div class="flex items-center" style="padding-left: {{ $indentation }}px;">
            @if($category->children->isNotEmpty())
                <button @click="open = !open" class="mr-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            @else
                <span class="w-4 h-4 mr-2"></span> {{-- Placeholder for alignment --}}
            @endif
            <span>{{ $category->name }}</span>
        </div>
    </td>
    <td class="px-6 py-4">{{ $category->slug }}</td>
    <td class="px-6 py-4 text-right">
        @include('backend.livewire.datatable.action-buttons', ['item' => $category])
    </td>
</tr>

{{-- Render children if they exist and the row is open --}}
@if($category->children->isNotEmpty())
    <tr x-show="open" x-cloak class="bg-gray-50 dark:bg-gray-900/50">
        <td colspan="{{ count($this->getHeaders()) + 1 }}" class="p-0">
            <table class="w-full">
                <tbody>
                @foreach($category->children as $child)
                    @include('backend.marketplace.categories._datatable_recursive_row', ['category' => $child, 'level' => $level + 1])
                @endforeach
                </tbody>
            </table>
        </td>
    </tr>
@endif