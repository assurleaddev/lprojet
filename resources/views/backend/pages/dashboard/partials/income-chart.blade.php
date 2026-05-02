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

    $totalIncome      = array_sum($income_data['total']);
    $totalCommission  = array_sum($income_data['commission']);
    $totalProtection  = array_sum($income_data['protection']);
    $totalVerification = array_sum($income_data['verification']);
@endphp

<div class="col-span-12">
    <div class="rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-6">

        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-white">{{ __('Income Overview') }}</h3>
                <p class="text-sm text-gray-400 mt-0.5">{{ $filterLabels[$incomeFilter] }}</p>
            </div>
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

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg p-4" style="background:rgba(99,91,255,.08);">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Total Income') }}</p>
                <p class="text-xl font-bold" style="color:#635BFF;">{{ number_format($totalIncome, 2) }} MAD</p>
            </div>
            <div class="rounded-lg p-4" style="background:rgba(34,197,94,.08);">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Commission') }}</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($totalCommission, 2) }} MAD</p>
            </div>
            <div class="rounded-lg p-4" style="background:rgba(245,158,11,.08);">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Buyer Protection') }}</p>
                <p class="text-xl font-bold text-amber-500">{{ number_format($totalProtection, 2) }} MAD</p>
            </div>
            <div class="rounded-lg p-4" style="background:rgba(239,68,68,.08);">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Verification') }}</p>
                <p class="text-xl font-bold text-red-500">{{ number_format($totalVerification, 2) }} MAD</p>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Total income area chart --}}
            <div>
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">{{ __('Total Income') }}</p>
                <div id="income-total-chart" class="h-48"></div>
            </div>
            {{-- Commission area chart --}}
            <div>
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">{{ __('Commission') }}</p>
                <div id="income-commission-chart" class="h-48"></div>
            </div>
            {{-- Buyer protection --}}
            <div>
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">{{ __('Buyer Protection') }}</p>
                <div id="income-protection-chart" class="h-48"></div>
            </div>
            {{-- Verification --}}
            <div>
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">{{ __('Verification Fees') }}</p>
                <div id="income-verification-chart" class="h-48"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const labels       = @json($income_data['labels']);
    const totalData    = @json($income_data['total']);
    const commData     = @json($income_data['commission']);
    const protData     = @json($income_data['protection']);
    const verData      = @json($income_data['verification']);

    function makeChart(elId, data, color, name) {
        const el = document.getElementById(elId);
        if (!el || typeof ApexCharts === 'undefined') return;
        new ApexCharts(el, {
            chart: {
                type: 'area',
                height: '100%',
                toolbar: { show: false },
                sparkline: { enabled: false },
                fontFamily: 'var(--font-sans)',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            series: [{ name: name, data: data }],
            xaxis: {
                categories: labels,
                labels: {
                    style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'var(--font-sans)' },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                min: 0,
                labels: {
                    formatter: v => v.toFixed(0) + ' MAD',
                    style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'var(--font-sans)' },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            stroke: { width: 2.5, curve: 'smooth', colors: [color] },
            fill: {
                type: 'gradient',
                gradient: { opacityFrom: 0.45, opacityTo: 0.02, gradientToColors: [color] },
            },
            dataLabels: { enabled: false },
            markers: { size: 0, hover: { size: 5 } },
            grid: {
                strokeDashArray: 4,
                padding: { left: 8, right: 8, top: 4, bottom: 0 },
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } },
            },
            tooltip: {
                y: { formatter: v => v.toFixed(2) + ' MAD' },
                theme: 'light',
                style: { fontSize: '12px', fontFamily: 'var(--font-sans)' },
            },
            colors: [color],
        }).render();
    }

    document.addEventListener('DOMContentLoaded', function () {
        makeChart('income-total-chart',        totalData, '#635BFF', '{{ __('Total') }}');
        makeChart('income-commission-chart',   commData,  '#22c55e', '{{ __('Commission') }}');
        makeChart('income-protection-chart',   protData,  '#f59e0b', '{{ __('Buyer Protection') }}');
        makeChart('income-verification-chart', verData,   '#ef4444', '{{ __('Verification') }}');
    });
})();
</script>
