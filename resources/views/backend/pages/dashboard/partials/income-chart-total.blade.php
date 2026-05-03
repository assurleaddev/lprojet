<div class="col-span-12 md:col-span-6">
    <div class="rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5 h-full">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ __('Total Income') }}</p>
                <p class="text-2xl font-bold" style="color:#635BFF;">
                    {{ number_format(array_sum($income_data['total']), 2) }} MAD
                </p>
            </div>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full" style="background:rgba(99,91,255,.12);">
                <iconify-icon icon="heroicons:currency-dollar" style="color:#635BFF;font-size:20px;"></iconify-icon>
            </span>
        </div>
        <div id="chart-income-total" class="h-44"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('chart-income-total');
    if (!el || typeof ApexCharts === 'undefined') return;
    new ApexCharts(el, incomeChartOptions(
        @json($income_data['labels']),
        @json($income_data['total']),
        '#635BFF',
        '{{ __('Total Income') }}'
    )).render();
});
</script>
