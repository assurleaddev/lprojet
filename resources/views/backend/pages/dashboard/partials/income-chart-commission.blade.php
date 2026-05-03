@php
    $filterLabels = [
        'last_7_days'    => __('Last 7 days'),
        'this_month'     => __('This month'),
        'last_30_days'   => __('Last 30 days'),
        'last_6_months'  => __('Last 6 months'),
        'last_12_months' => __('Last 12 months'),
        'this_year'      => __('This year'),
        'last_year'      => __('Last year'),
    ];
@endphp

<div class="rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5">
    <div class="flex items-start justify-between mb-4">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Commission Income') }}</p>
            <p class="text-2xl font-bold text-green-600">
                {{ number_format(array_sum($income_data_commission['commission']), 2) }} MAD
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex items-center gap-1 border border-gray-200 dark:border-gray-600 rounded px-2 py-1">
                    {{ $filterLabels[$income_period_commission] }}
                    <iconify-icon icon="lucide:chevron-down" style="font-size:11px;"></iconify-icon>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute right-0 mt-1 w-40 rounded-md shadow-lg bg-white dark:bg-gray-700 z-20 border border-gray-100 dark:border-gray-600">
                    <ul class="py-1 text-xs text-gray-700 dark:text-gray-200">
                        @foreach($filterLabels as $value => $label)
                        <li>
                            <a href="{{ request()->fullUrlWithQuery(['income_period_commission' => $value]) }}"
                               class="block px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-600 {{ $income_period_commission === $value ? 'bg-green-50 dark:bg-gray-600 font-semibold text-green-700 dark:text-green-300' : '' }}">
                                {{ $label }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full" style="background:rgba(34,197,94,.12);">
                <iconify-icon icon="heroicons:percent-badge" style="color:#22c55e;font-size:20px;"></iconify-icon>
            </span>
        </div>
    </div>
    <div id="chart-income-commission" style="height:220px;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('chart-income-commission');
    if (!el || typeof ApexCharts === 'undefined') return;
    new ApexCharts(el, incomeChartOptions(
        @json($income_data_commission['labels']),
        @json($income_data_commission['commission']),
        '#22c55e',
        '{{ __('Commission') }}'
    )).render();
});
</script>
