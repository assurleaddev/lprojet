<x-layouts.backend-layout>
    <x-slot name="title">Categories</x-slot>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Category List</h2>
        {{-- <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add New Category
        </a> --}}
    </div>

    @livewire('datatable.category-datatable', ['lazy' => true])

    {{-- <x-card>
        <div class="overflow-x-auto">
             <div class="mb-4">
                <form action="{{ route('admin.categories.index') }}" method="GET" class="flex items-center">
                    <input type="text" name="search" placeholder="Search by category name..." 
                        class="form-input flex-grow" 
                        value="{{ request('search') }}">
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
                <tbody>
                    @include('backend.marketplace.categories._table_body', ['categories' => $categories])
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $categories->links() }}
        </div>
    </x-card>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const tableBody = document.getElementById('categories-table-body');
            const paginationContainer = document.getElementById('categories-pagination');
            let searchTimeout;

            searchInput.addEventListener('keyup', function () {
                clearTimeout(searchTimeout);
                // Debounce the search to avoid sending requests on every keystroke
                searchTimeout = setTimeout(() => {
                    performSearch(searchInput.value);
                }, 300); // Wait for 300ms of inactivity before searching
            });

            function performSearch(query) {
                const url = new URL('{{ route('admin.categories.index') }}');
                url.searchParams.set('search', query);

                // Add a spinner or loading indicator
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center p-6">Loading...</td></tr>';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Replace the table body and pagination with the new content
                    tableBody.innerHTML = data.table;
                    paginationContainer.innerHTML = data.pagination;
                    
                    // Update the browser's URL without a full reload
                    window.history.pushState({}, '', url);
                })
                .catch(error => {
                    tableBody.innerHTML = '<tr><td colspan="3" class="text-center p-6 text-red-500">Error loading data.</td></tr>';
                    console.error('Search error:', error);
                });
            }
        });
    </script>
    @endpush --}}
    @push('scripts')
        <script>
            $(document).on("click", ".togggler", function() {
                // Your custom jQuery logic here
                const targetId = $(this)[0].dataset.tergetId; 
                $(`[data-parent-id="${targetId}"]`).css('display', 'table-row')
            });
        </script>
    @endpush

</x-layouts.backend-layout>