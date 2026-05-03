@php
    $direct = $order_source_data['direct'];
    $offer  = $order_source_data['offer'];
    $live   = $order_source_data['live'];
    $total  = $direct + $offer + $live;
@endphp

<div class="rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5">
    <div class="flex items-start justify-between mb-4">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Purchases by Source') }}</p>
            <p class="text-2xl font-bold text-gray-700 dark:text-white">{{ number_format($total) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ __('total orders') }}</p>
        </div>
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/30">
            <iconify-icon icon="heroicons:chart-pie" style="color:#635BFF;font-size:20px;"></iconify-icon>
        </span>
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
