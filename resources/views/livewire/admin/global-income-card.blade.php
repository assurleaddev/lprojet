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

<div class="relative rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 mb-6">

    <div wire:loading wire:target="setPeriod"
         class="absolute inset-0 rounded-md bg-white/70 dark:bg-gray-800/70 flex items-center justify-center z-10">
        <iconify-icon icon="lucide:loader-2" class="animate-spin" style="font-size:22px;color:#635BFF;"></iconify-icon>
    </div>

    {{-- Header row --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-4">

        <div class="flex items-center gap-4">
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-full shrink-0" style="background:rgba(99,91,255,.12);">
                <iconify-icon icon="heroicons:banknotes" style="color:#635BFF;font-size:26px;"></iconify-icon>
            </span>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-0.5">{{ __('Global Order Volume') }}</p>
                <p class="text-3xl font-bold" style="color:#635BFF;">{{ $gross }} MAD</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $order_count }} {{ __('orders') }}</p>
            </div>
        </div>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2">
                <iconify-icon icon="lucide:calendar" style="font-size:15px;"></iconify-icon>
                {{ $filterLabels[$period] }}
                <iconify-icon icon="lucide:chevron-down" style="font-size:13px;"></iconify-icon>
            </button>
            <div x-show="open" @click.outside="open = false" x-transition
                 class="absolute right-0 mt-1 w-44 rounded-md shadow-lg bg-white dark:bg-gray-700 z-20 border border-gray-100 dark:border-gray-600">
                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                    @foreach($filterLabels as $value => $label)
                    <li>
                        <button wire:click="setPeriod('{{ $value }}')" @click="open = false"
                                class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 {{ $period === $value ? 'bg-indigo-50 dark:bg-gray-600 font-semibold text-indigo-700 dark:text-indigo-300' : '' }}">
                            {{ $label }}
                        </button>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>

    {{-- Chart --}}
    <div wire:ignore
         x-data="{
             chart: null,
             init() {
                 this.chart = new ApexCharts(
                     this.$refs.chartEl,
                     incomeChartOptions(@js($labels), @js($series), '#635BFF', @js(__('Global Volume')))
                 );
                 this.chart.render();
             },
             updateChart(labels, series) {
                 this.chart.updateOptions({ xaxis: { categories: labels } }, false, false);
                 this.chart.updateSeries([{ name: @js(__('Global Volume')), data: series }]);
             }
         }"
         x-on:global-income-update.window="updateChart($event.detail.labels, $event.detail.series)">
        <div x-ref="chartEl" style="height:180px;"></div>
    </div>

</div>
