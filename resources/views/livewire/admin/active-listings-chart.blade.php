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
    $stats = [
        ['label' => __('Active'),  'key' => 'approved', 'color' => '#22c55e', 'bg' => 'rgba(34,197,94,.12)',  'icon' => 'heroicons:tag'],
        ['label' => __('Pending'), 'key' => 'pending',  'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.12)', 'icon' => 'heroicons:clock'],
        ['label' => __('Sold'),    'key' => 'sold',     'color' => '#635BFF', 'bg' => 'rgba(99,91,255,.12)',  'icon' => 'heroicons:shopping-bag'],
    ];
@endphp

<div class="relative rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5">

    <div wire:loading wire:target="setPeriod"
         class="absolute inset-0 rounded-md bg-white/70 dark:bg-gray-800/70 flex items-center justify-center z-10">
        <iconify-icon icon="lucide:loader-2" class="animate-spin" style="font-size:22px;color:#22c55e;"></iconify-icon>
    </div>

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Listing Network') }}</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($totals['total']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ __('listings in period') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex items-center gap-1 border border-gray-200 dark:border-gray-600 rounded px-2 py-1">
                    {{ $filterLabels[$period] }}
                    <iconify-icon icon="lucide:chevron-down" style="font-size:11px;"></iconify-icon>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute right-0 mt-1 w-40 rounded-md shadow-lg bg-white dark:bg-gray-700 z-20 border border-gray-100 dark:border-gray-600">
                    <ul class="py-1 text-xs text-gray-700 dark:text-gray-200">
                        @foreach($filterLabels as $value => $label)
                        <li>
                            <button wire:click="setPeriod('{{ $value }}')" @click="open = false"
                                    class="block w-full text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-600 {{ $period === $value ? 'bg-green-50 dark:bg-gray-600 font-semibold text-green-700 dark:text-green-300' : '' }}">
                                {{ $label }}
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full" style="background:rgba(34,197,94,.12);">
                <iconify-icon icon="heroicons:squares-2x2" style="color:#22c55e;font-size:20px;"></iconify-icon>
            </span>
        </div>
    </div>

    {{-- Stat pills --}}
    <div class="flex flex-wrap gap-3 mb-5">
        @foreach($stats as $s)
        <div class="flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold" style="background:{{ $s['bg'] }}; color:{{ $s['color'] }};">
            <iconify-icon icon="{{ $s['icon'] }}" style="font-size:13px;"></iconify-icon>
            {{ $s['label'] }}: {{ number_format($totals[$s['key']]) }}
        </div>
        @endforeach
    </div>

    {{-- Chart --}}
    <div wire:ignore
         x-data="{
             chart: null,
             init() {
                 this.chart = new ApexCharts(this.$refs.chartEl, {
                     chart: {
                         type: 'area',
                         height: 240,
                         toolbar: { show: false },
                         fontFamily: 'var(--font-sans)',
                         animations: { enabled: true, easing: 'easeinout', speed: 700 },
                     },
                     series: [
                         { name: @js(__('Active')),  data: @js($approved) },
                         { name: @js(__('Pending')), data: @js($pending)  },
                         { name: @js(__('Sold')),    data: @js($sold)     },
                     ],
                     xaxis: {
                         categories: @js($labels),
                         labels: { style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'var(--font-sans)' } },
                         axisBorder: { show: false },
                         axisTicks: { show: false },
                     },
                     yaxis: {
                         min: 0,
                         labels: {
                             formatter: v => Math.round(v),
                             style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'var(--font-sans)' },
                         },
                         axisBorder: { show: false },
                         axisTicks: { show: false },
                     },
                     colors: ['#22c55e', '#f59e0b', '#635BFF'],
                     stroke: { width: 2.5, curve: 'smooth' },
                     fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.02 } },
                     dataLabels: { enabled: false },
                     markers: { size: 0, hover: { size: 5 } },
                     grid: {
                         strokeDashArray: 4,
                         padding: { left: 8, right: 8, top: 4, bottom: 0 },
                         yaxis: { lines: { show: true } },
                         xaxis: { lines: { show: false } },
                     },
                     legend: {
                         show: true,
                         position: 'top',
                         horizontalAlign: 'right',
                         fontSize: '12px',
                         fontFamily: 'var(--font-sans)',
                         markers: { width: 8, height: 8, radius: 8 },
                     },
                     tooltip: {
                         shared: true,
                         intersect: false,
                         style: { fontSize: '12px', fontFamily: 'var(--font-sans)' },
                     },
                 });
                 this.chart.render();
             },
             updateChart(labels, approved, pending, sold) {
                 this.chart.updateOptions({ xaxis: { categories: labels } }, false, false);
                 this.chart.updateSeries([
                     { name: @js(__('Active')),  data: approved },
                     { name: @js(__('Pending')), data: pending  },
                     { name: @js(__('Sold')),    data: sold     },
                 ]);
             }
         }"
         x-on:active-listings-update.window="updateChart($event.detail.labels, $event.detail.approved, $event.detail.pending, $event.detail.sold)">
        <div x-ref="chartEl"></div>
    </div>

</div>
