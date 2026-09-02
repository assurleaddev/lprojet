<x-layouts.backend-layout>
    <x-slot name="title">Categories</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Category List</h2>
        {{-- <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add New Category
        </a> --}}
    </div>

    {{-- @livewire('datatable.category-datatable', ['lazy' => true]) --}}

    <x-card>
        <div class="overflow-x-auto">
            <div class="mb-4">
                <form action="{{ route('admin.categories.index') }}" method="GET" class="flex items-center">
                    <input type="text" name="search" placeholder="Search by category name..."
                        class="form-input flex-grow" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary ml-2">Search</button>
                    @if(request('search'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary ml-2">Clear</a>
                    @endif
                </form>
            </div>
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-1/2">Name</th>
                        <th scope="col" class="px-6 py-3">Slug</th>
                        <th scope="col" class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                @include('backend.marketplace.categories._table_body', ['categories' => $categories])
            </table>
        </div>
        <div class="p-4">
            {{ $categories->links() }}
        </div>
    </x-card>

    @push('scripts')
        <script>
            $(document).on("click", ".togggler", function () {
                // Your custom jQuery logic here
                const targetId = $(this)[0].dataset.tergetId;
                $(`[data-parent-id="${targetId}"]`).css('display', 'table-row')
            });
        </script>
    @endpush

</x-layouts.backend-layout>