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
    $rows = [
        ['label' => __('Direct Checkout'), 'key' => 'direct', 'color' => '#635BFF', 'bg' => 'rgba(99,91,255,.1)'],
        ['label' => __('Offer Checkout'),  'key' => 'offer',  'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.1)'],
        ['label' => __('Live Auction'),    'key' => 'live',   'color' => '#ef4444', 'bg' => 'rgba(239,68,68,.1)'],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- Donut card --}}
    <div class="md:col-span-1 relative rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5">

        <div wire:loading wire:target="setPeriod"
             class="absolute inset-0 rounded-md bg-white/70 dark:bg-gray-800/70 flex items-center justify-center z-10">
            <iconify-icon icon="lucide:loader-2" class="animate-spin" style="font-size:22px;color:#635BFF;"></iconify-icon>
        </div>

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
                        {{ $filterLabels[$period] }}
                        <iconify-icon icon="lucide:chevron-down" style="font-size:11px;"></iconify-icon>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition
                         class="absolute right-0 mt-1 w-40 rounded-md shadow-lg bg-white dark:bg-gray-700 z-20 border border-gray-100 dark:border-gray-600">
                        <ul class="py-1 text-xs text-gray-700 dark:text-gray-200">
                            @foreach($filterLabels as $value => $label)
                            <li>
                                <button wire:click="setPeriod('{{ $value }}')" @click="open = false"
                                        class="block w-full text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-600 {{ $period === $value ? 'bg-indigo-50 dark:bg-gray-600 font-semibold text-indigo-700 dark:text-indigo-300' : '' }}">
                                    {{ $label }}
                                </button>
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

        <div wire:ignore
             x-data="{
                 chart: null,
                 init() {
                     this.chart = new ApexCharts(this.$refs.chartEl, {
                         chart: {
                             type: 'donut',
                             height: '100%',
                             fontFamily: 'var(--font-sans)',
                             toolbar: { show: false },
                             animations: { enabled: true, easing: 'easeinout', speed: 700 },
                         },
                         series: [@js($sources['direct']), @js($sources['offer']), @js($sources['live'])],
                         labels: [@js(__('Direct Checkout')), @js(__('Offer Checkout')), @js(__('Live Auction'))],
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
                                             label: @js(__('Orders')),
                                             fontSize: '13px',
                                             fontFamily: 'var(--font-sans)',
                                             fontWeight: 600,
                                             color: '#6b7280',
                                             formatter: () => @js($total).toString(),
                                         },
                                     },
                                 },
                             },
                         },
                         tooltip: {
                             y: { formatter: (v) => v + ' ' + @js(__('orders')) },
                             style: { fontSize: '12px', fontFamily: 'var(--font-sans)' },
                         },
                         stroke: { width: 2, colors: ['#fff'] },
                     });
                     this.chart.render();
                 },
                 updateChart(direct, offer, live, total) {
                     this.chart.updateSeries([direct, offer, live]);
                     this.chart.updateOptions({
                         plotOptions: {
                             pie: {
                                 donut: {
                                     labels: {
                                         total: { formatter: () => total.toString() }
                                     }
                                 }
                             }
                         }
                     }, false, false);
                 }
             }"
             x-on:income-chart-sources-update.window="updateChart($event.detail.direct, $event.detail.offer, $event.detail.live, $event.detail.total)">
            <div x-ref="chartEl" style="height:220px;"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-4 mt-3">
            @foreach($rows as $row)
            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $row['color'] }};"></span>
                {{ $row['label'] }} — {{ $total ? round($sources[$row['key']] / $total * 100) : 0 }}%
            </div>
            @endforeach
        </div>

    </div>

    {{-- Progress bars --}}
    <div class="md:col-span-2 rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5 flex flex-col justify-center gap-3">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ __('Source Breakdown') }}</p>
        @foreach($rows as $row)
        @php
            $count = $sources[$row['key']];
            $pct = $total ? round($count / $total * 100) : 0;
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
