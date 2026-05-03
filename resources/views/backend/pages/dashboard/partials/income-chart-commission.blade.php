<div class="rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Commission Income') }}</p>
                <p class="text-2xl font-bold text-green-600">
                    {{ number_format(array_sum($income_data['commission']), 2) }} MAD
                </p>
            </div>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full" style="background:rgba(34,197,94,.12);">
                <iconify-icon icon="heroicons:percent-badge" style="color:#22c55e;font-size:20px;"></iconify-icon>
            </span>
        </div>
        <div id="chart-income-commission" style="height:220px;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('chart-income-commission');
    if (!el || typeof ApexCharts === 'undefined') return;
    new ApexCharts(el, incomeChartOptions(
        @json($income_data['labels']),
        @json($income_data['commission']),
        '#22c55e',
        '{{ __('Commission') }}'
    )).render();
});
</script>
