@php
    $direct = $order_source_data['direct'];
    $offer  = $order_source_data['offer'];
    $live   = $order_source_data['live'];
    $total  = $direct + $offer + $live;

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
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Purchases by Source') }}</p>
            <p class="text-2xl font-bold text-gray-700 dark:text-white">{{ number_format($total) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ __('total orders') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex items-center gap-1 border border-gray-200 dark:border-gray-600 rounded px-2 py-1">
                    {{ $filterLabels[$income_period_sources] }}
                    <iconify-icon icon="lucide:chevron-down" style="font-size:11px;"></iconify-icon>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute right-0 mt-1 w-40 rounded-md shadow-lg bg-white dark:bg-gray-700 z-20 border border-gray-100 dark:border-gray-600">
                    <ul class="py-1 text-xs text-gray-700 dark:text-gray-200">
                        @foreach($filterLabels as $value => $label)
                        <li>
                            <a href="{{ request()->fullUrlWithQuery(['income_period_sources' => $value]) }}"
                               class="block px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-600 {{ $income_period_sources === $value ? 'bg-indigo-50 dark:bg-gray-600 font-semibold text-indigo-700 dark:text-indigo-300' : '' }}">
                                {{ $label }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/30">
                <iconify-icon icon="heroicons:chart-pie" style="color:#635BFF;font-size:20px;"></iconify-icon>
            </span>
        </div>
    </div>

    <div id="chart-order-sources" style="height:220px;"></div>

    {{-- Legend --}}
    <div class="flex flex-wrap justify-center gap-4 mt-3">
        <div class="flex items-center gap-1.5 text-sm text-gray-500">
            <span class="w-3 h-3 rounded-full inline-block" style="background:#635BFF;"></span>
            {{ __('Direct') }} — {{ $total ? round($direct / $total * 100) : 0 }}%
        </div>
        <div class="flex items-center gap-1.5 text-sm text-gray-500">
            <span class="w-3 h-3 rounded-full inline-block" style="background:#f59e0b;"></span>
            {{ __('Offer') }} — {{ $total ? round($offer / $total * 100) : 0 }}%
        </div>
        <div class="flex items-center gap-1.5 text-sm text-gray-500">
            <span class="w-3 h-3 rounded-full inline-block" style="background:#ef4444;"></span>
            {{ __('Live') }} — {{ $total ? round($live / $total * 100) : 0 }}%
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('chart-order-sources');
    if (!el || typeof ApexCharts === 'undefined') return;

    new ApexCharts(el, {
        chart: {
            type: 'donut',
            height: '100%',
            fontFamily: 'var(--font-sans)',
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 700 },
        },
        series: [{{ $direct }}, {{ $offer }}, {{ $live }}],
        labels: ['{{ __('Direct Checkout') }}', '{{ __('Offer Checkout') }}', '{{ __('Live Auction') }}'],
        colors: ['#635BFF', '#f59e0b', '#ef4444'],
        legend: { show: false },
        dataLabels: {
            enabled: true,
            formatter: (val) => val.toFixed(1) + '%',
            style: { fontSize: '12px', fontFamily: 'var(--font-sans)', fontWeight: 600 },
            dropShadow: { enabled: false },
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: '{{ __('Orders') }}',
                            fontSize: '13px',
                            fontFamily: 'var(--font-sans)',
                            fontWeight: 600,
                            color: '#6b7280',
                            formatter: () => '{{ $total }}',
                        },
                    },
                },
            },
        },
        tooltip: {
            y: { formatter: (v) => v + ' {{ __('orders') }}' },
            style: { fontSize: '12px', fontFamily: 'var(--font-sans)' },
        },
        stroke: { width: 2, colors: ['#fff'] },
    }).render();
});
</script>
