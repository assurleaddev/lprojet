@foreach ($children as $child)
    @php
        // Calculate indentation and color
        $currentDepth = $depth ?? 20;
        $nextDepth = $currentDepth + 40; // Increase step size for better visibility

        // Dynamic background based on depth level
        // Level 1 (20px) -> gray-50
        // Level 2 (60px) -> gray-100
        // Level 3 (100px) -> gray-200
        $level = floor($currentDepth / 40);
        $bgClass = match (true) {
            $level == 0 => 'bg-gray-50 dark:bg-gray-800/50',
            $level == 1 => 'bg-gray-100 dark:bg-gray-800',
            $level >= 2 => 'bg-gray-200 dark:bg-gray-900',
            default => 'bg-gray-50'
        };
    @endphp

    <tbody x-data="{ open: false, loaded: false, loading: false }" class="border-b dark:border-gray-700 {{ $bgClass }}">
        <tr>
            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white pl-8">
                <div class="flex items-center" style="padding-left: {{ $currentDepth }}px">
                    @if ($child->children_count > 0)
                        <button @click="
                                                    if (!loaded) {
                                                        loading = true;
                                                        fetch('{{ route('admin.categories.children', $child->id) }}?depth={{ $nextDepth }}')
                                                            .then(response => response.text())
                                                            .then(html => {
                                                                $refs.childTable.innerHTML = html;
                                                                loaded = true;
                                                                open = true;
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @else
                        <span class="w-6 mr-2"></span>
                    @endif
                    <span>{{ $child->name }}</span>
                </div>
            </td>
            <td class="px-6 py-3">{{ $child->slug }}</td>
            <td class="px-6 py-3 text-right">
                @include('backend.marketplace.categories._actions', ['category' => $child])
            </td>
        </tr>
        <!-- Container for Children -->
        <tr x-show="open" x-cloak>
            <td colspan="3" class="p-0 border-0">
                <table class="w-full" x-ref="childTable">
                    <!-- Child rows injected here -->
                </table>
            </td>
        </tr>
    </tbody>
@endforeach