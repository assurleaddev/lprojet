@php
    $incomeFilter = request()->get('income_filter_period', 'last_6_months');
    $filterLabels = [
        'last_6_months'  => __('Last 6 months'),
        'last_12_months' => __('Last 12 months'),
        'this_year'      => __('This year'),
        'last_year'      => __('Last year'),
        'last_30_days'   => __('Last 30 days'),
        'last_7_days'    => __('Last 7 days'),
        'this_month'     => __('This month'),
    ];
@endphp

<div class="col-span-12">

    {{-- Filter bar --}}
    <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $filterLabels[$incomeFilter] }}</p>
        <div class="flex gap-2 items-center">
            <span class="bg-indigo-100 text-indigo-900 px-4 py-2 rounded-full text-sm">
                {{ $filterLabels[$incomeFilter] }}
            </span>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="btn-primary flex items-center gap-2">
                    <iconify-icon icon="lucide:sliders"></iconify-icon>
                    {{ __('Filter') }}
                    <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute right-0 mt-2 w-48 rounded-md shadow-sm bg-white dark:bg-gray-700 z-10">
                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                        @foreach($filterLabels as $value => $label)
                        <li>
                            <a href="{{ route('admin.dashboard') }}?income_filter_period={{ $value }}"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 {{ $incomeFilter === $value ? 'bg-blue-100 dark:bg-gray-600' : '' }}">
                                {{ $label }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- 2 columns × 2 rows: area charts + pie --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @include('backend.pages.dashboard.partials.income-chart-total')
        @include('backend.pages.dashboard.partials.income-chart-commission')
        @include('backend.pages.dashboard.partials.income-chart-protection')
        @include('backend.pages.dashboard.partials.income-chart-verification')
    </div>

    {{-- Purchase source pie (full-width row below) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="md:col-span-1">
            @include('backend.pages.dashboard.partials.income-chart-sources')
        </div>
        <div class="md:col-span-2 rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5 flex flex-col justify-center gap-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ __('Source Breakdown') }}</p>
            @php
                $total = $order_source_data['direct'] + $order_source_data['offer'] + $order_source_data['live'];
            @endphp
            @foreach([
                ['label' => __('Direct Checkout'), 'key' => 'direct', 'color' => '#635BFF', 'bg' => 'rgba(99,91,255,.1)'],
                ['label' => __('Offer Checkout'),  'key' => 'offer',  'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.1)'],
                ['label' => __('Live Auction'),    'key' => 'live',   'color' => '#ef4444', 'bg' => 'rgba(239,68,68,.1)'],
            ] as $row)
            @php
                $count = $order_source_data[$row['key']];
                $pct   = $total ? round($count / $total * 100) : 0;
            @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-600 dark:text-gray-300">{{ $row['label'] }}</span>
                    <span class="font-semibold" style="color:{{ $row['color'] }};">{{ $count }} &nbsp;({{ $pct }}%)</span>
                </div>
                <div class="w-full rounded-full h-2.5" style="background:{{ $row['bg'] }};">
                    <div class="h-2.5 rounded-full transition-all" style="width:{{ $pct }}%; background:{{ $row['color'] }};"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
