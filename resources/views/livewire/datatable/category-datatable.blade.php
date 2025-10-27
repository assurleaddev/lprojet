<div>
    {{-- Search and Filter Section --}}
    <div class="mb-4 flex justify-between items-center">
        @include('backend.livewire.datatable.searchbar')
    </div>

    {{-- Bulk Actions Section --}}
    @if ($this->hasSelection())
        @include('backend.livewire.datatable.bulk-actions')
    @endif

    {{-- Table Structure --}}
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                {{-- Table Header --}}
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="p-4">
                            <input type="checkbox" wire:model.live="selectPage" class="form-checkbox">
                        </th>
                        @foreach($this->getHeaders() as $header)
                            <th scope="col" class="px-6 py-3" @if($header['width']) style="width: {{ $header['width'] }}" @endif>
                                @if($header['sortable'])
                                    <button wire:click="sortBy('{{ $header['sortBy'] }}')" class="flex items-center">
                                        {{ $header['title'] }}
                                        <x-datatable.sort-icon field="{{ $header['sortBy'] }}" :sortField="$sortField" :sortDirection="$sortDirection" />
                                    </button>
                                @else
                                    {{ $header['title'] }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                {{-- Table Body --}}
                <tbody>
                    @forelse($items as $category)
                        {{-- Call the recursive partial for each top-level category --}}
                        @include('backend.marketplace.categories._datatable_recursive_row', ['category' => $category, 'level' => 0])
                    @empty
                        <tr>
                            <td colspan="{{ count($this->getHeaders()) + 1 }}" class="text-center py-6">
                                {{ __('No results found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="p-4">
            {{ $items->links() }}
        </div>
    </x-card>
</div>