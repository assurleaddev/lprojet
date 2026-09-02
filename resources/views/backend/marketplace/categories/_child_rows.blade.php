@foreach ($children as $child)
    @php
        $currentDepth = $depth ?? 20;
        $nextDepth = $currentDepth + 40;

        $level = floor($currentDepth / 40);
        $bgClass = match (true) {
            $level == 0 => 'bg-gray-50 dark:bg-gray-800/50',
            $level == 1 => 'bg-gray-100 dark:bg-gray-800',
            $level >= 2 => 'bg-gray-200 dark:bg-gray-900',
            default => 'bg-gray-50',
        };
    @endphp

    <tbody
        x-data="{
            open: false,
            loaded: false,
            loading: false,
            toggle() {
                if (this.loaded) { this.open = !this.open; return; }
                this.loading = true;
                fetch('{{ route('admin.categories.children', $child->id) }}?depth={{ $nextDepth }}')
                    .then(r => r.text())
                    .then(html => {
                        this.$refs.childTable.innerHTML = html;
                        this.loaded = true;
                        this.open = true;
                        this.loading = false;
                    })
                    .catch(() => { this.loading = false; });
            }
        }"
        class="border-b dark:border-gray-700 {{ $bgClass }}"
    >
        <tr class="hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                <div class="flex items-center" style="padding-left: {{ $currentDepth }}px">
                    @if ($child->children_count > 0)
                        <button type="button" @click="toggle()"
                            class="flex items-center gap-2 text-left group focus:outline-none">
                            <span class="shrink-0 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300">
                                <svg x-show="loading" class="animate-spin h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg x-show="!loading" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                            <span class="group-hover:underline">{{ $child->name }}</span>
                            <span class="text-xs font-normal text-gray-400">({{ $child->children_count }})</span>
                        </button>
                    @else
                        <span class="inline-block w-4 mr-2"></span>
                        <span>{{ $child->name }}</span>
                    @endif
                </div>
            </td>
            <td class="px-6 py-3 text-gray-500">{{ $child->slug }}</td>
            <td class="px-6 py-3 text-right">
                @include('backend.marketplace.categories._actions', ['category' => $child])
            </td>
        </tr>

        {{-- Nested children (lazy-loaded, smooth collapse) --}}
        <tr>
            <td colspan="3" class="p-0 border-0">
                <div x-show="open" x-collapse x-cloak>
                    <table class="w-full" x-ref="childTable"></table>
                </div>
            </td>
        </tr>
    </tbody>
@endforeach
