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

<div class="relative rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5">

    <div wire:loading wire:target="setPeriod"
         class="absolute inset-0 rounded-md bg-white/70 dark:bg-gray-800/70 flex items-center justify-center z-10">
        <iconify-icon icon="lucide:loader-2" class="animate-spin" style="font-size:22px;color:#635BFF;"></iconify-icon>
    </div>

    <div class="flex items-start justify-between mb-4">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Total Income') }}</p>
            <p class="text-2xl font-bold" style="color:#635BFF;">{{ $total }} MAD</p>
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
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full" style="background:rgba(99,91,255,.12);">
                <iconify-icon icon="heroicons:currency-dollar" style="color:#635BFF;font-size:20px;"></iconify-icon>
            </span>
        </div>
    </div>

    <div wire:ignore
         x-data="{
             chart: null,
             init() {
                 this.chart = new ApexCharts(
                     this.$refs.chartEl,
                     incomeChartOptions(@js($labels), @js($series), '#635BFF', @js(__('Total Income')))
                 );
                 this.chart.render();
             },
             updateChart(labels, series) {
                 this.chart.updateOptions({ xaxis: { categories: labels } }, false, false);
                 this.chart.updateSeries([{ name: @js(__('Total Income')), data: series }]);
             }
         }"
         x-on:income-chart-total-update.window="updateChart($event.detail.labels, $event.detail.series)">
        <div x-ref="chartEl" style="height:220px;"></div>
    </div>

</div>
