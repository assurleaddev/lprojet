@forelse($categories as $category)
    <tbody x-data="{ open: false, loaded: false, loading: false }" class="border-b dark:border-gray-700">
        <tr class="bg-white dark:bg-gray-800">
            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                <div class="flex items-center">
                    @if ($category->children_count > 0)
                        <button @click="
                                            if (!loaded) {
                                                loading = true;
                                                fetch('{{ route('admin.categories.children', $category->id) }}')
                                                    .then(response => response.text())
                                                    .then(html => {
                                                        $refs.childTable.innerHTML = html;
                                                        loaded = true;
                                                        open = true;
                                                        loading = false;
                                                    })
                                                    .catch(err => {
                                                        console.error(err);
                                                        loading = false;
                                                    });
                                            } else {
                                                open = !open;
                                            }
                                        "
                            class="mr-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                            <!-- Loading Spinner -->
                            <svg x-show="loading" class="animate-spin h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <!-- Arrow -->
                            <svg x-show="!loading" class="w-4 h-4 transition-transform duration-200"
                                :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                    @else
                        <span class="w-6 mr-2"></span>
                    @endif
                    <span>{{ $category->name }}</span>
                </div>
            </td>
            <td class="px-6 py-4">{{ $category->slug }}</td>
            <td class="px-6 py-4 text-right">
                @include('backend.marketplace.categories._actions', ['category' => $category])
            </td>
        </tr>

        {{-- Container for Children --}}
        <tr x-show="open" x-cloak>
            <td colspan="3" class="p-0 border-0">
                <table class="w-full" x-ref="childTable">
                    {{-- AJAX Loaded content goes here --}}
                </table>
            </td>
        </tr>
    </tbody>
@empty
    <tbody>
        <tr>
            <td colspan="3" class="px-6 py-4 text-center">No categories found.</td>
        </tr>
    </tbody>
@endforelse