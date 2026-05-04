<div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 shrink-0">{{ __('My Analytics') }}</h1>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 shrink-0">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ __('Total Views') }}</p>
            <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($totals['views']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ __('Total Clicks') }}</p>
            <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($totals['clicks']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ __('Total Likes') }}</p>
            <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($totals['likes']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ __('Total Orders') }}</p>
            <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($totals['orders']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1 min-h-0">

        {{-- Product List --}}
        <div class="lg:col-span-1 flex flex-col min-h-0">
            <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-3 shrink-0">{{ __('My Listings') }}</h2>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-y-auto" style="max-height: 520px;">
                @forelse($products as $product)
                    <button wire:click="selectProduct({{ $product->id }})" type="button"
                       class="w-full flex items-center gap-3 px-4 py-3 border-b border-gray-50 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left {{ $selectedProduct?->id === $product->id ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                        <img src="{{ $product->getFirstMediaUrl('products', 'thumb') ?: $product->getFeaturedImageUrl('thumb') ?: asset('images/placeholder.png') }}"
                             alt="{{ $product->name }}"
                             class="w-10 h-10 rounded-lg object-cover shrink-0 border border-gray-100 dark:border-gray-600">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $product->name }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($product->price) }} MAD
                                &middot;
                                <span class="@if($product->status === 'approved') text-green-500 @else text-gray-400 @endif">
                                    {{ __($product->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs text-gray-500">{{ number_format($product->views_count) }} {{ __('views') }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($product->clicks_count) }} {{ __('clicks') }}</p>
                        </div>
                        @if($selectedProduct?->id === $product->id)
                            <div class="w-1 h-8 rounded-full bg-gray-900 dark:bg-white shrink-0"></div>
                        @endif
                    </button>
                @empty
                    <div class="px-4 py-10 text-center text-sm text-gray-400">{{ __('No listings yet.') }}</div>
                @endforelse
            </div>
        </div>

        {{-- Chart Panel --}}
        <div class="lg:col-span-2 overflow-y-auto min-h-0">
            @if($selectedProduct && $chartData)
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500">{{ $selectedProduct->name }}</h2>
                    <span class="text-xs text-gray-400">{{ __('Last 30 days') }}</span>
                </div>

                {{-- Mini stat row for selected product --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5">
                    @foreach([
                        ['label' => __('Views'),  'value' => $selectedProduct->views_count,     'color' => 'text-blue-500'],
                        ['label' => __('Clicks'),  'value' => $selectedProduct->clicks_count,    'color' => 'text-purple-500'],
                        ['label' => __('Likes'),   'value' => $selectedProduct->favorites_count, 'color' => 'text-red-500'],
                        ['label' => __('Orders'),  'value' => $selectedProduct->orders_count,    'color' => 'text-green-500'],
                    ] as $stat)
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 text-center">
                            <p class="text-xs font-semibold text-gray-400 mb-1">{{ $stat['label'] }}</p>
                            <p class="text-xl font-extrabold {{ $stat['color'] }}">{{ number_format($stat['value']) }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- CTR --}}
                @if($selectedProduct->views_count > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 mb-5 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Click-through Rate') }}</p>
                            <p class="text-2xl font-extrabold text-gray-900 dark:text-white">
                                {{ number_format(($selectedProduct->clicks_count / $selectedProduct->views_count) * 100, 1) }}%
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Conversion Rate') }}</p>
                            <p class="text-2xl font-extrabold text-gray-900 dark:text-white">
                                {{ number_format(($selectedProduct->orders_count / $selectedProduct->views_count) * 100, 1) }}%
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Line Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5" style="height: 320px;">
                    <canvas id="analyticsChart" wire:ignore style="width:100%; height:100%;"></canvas>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 flex items-center justify-center h-64">
                    <p class="text-sm text-gray-400">{{ __('Select a listing to see its analytics.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

@script
<script>
    // Load Chart.js once
    if (!window.Chart) {
        let s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        s.onload = () => initChart();
        document.head.appendChild(s);
    } else {
        initChart();
    }

    let chartInstance = null;

    function initChart() {
        const canvas = document.getElementById('analyticsChart');
        if (!canvas) return;

        @if($chartData)
            buildChart(@json($chartData));
        @endif
    }

    function buildChart(data) {
        const canvas = document.getElementById('analyticsChart');
        if (!canvas) return;

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        chartInstance = new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: @json(__('Views')),
                        data: data.views,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.08)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: @json(__('Clicks')),
                        data: data.clicks,
                        borderColor: '#a855f7',
                        backgroundColor: 'rgba(168,85,247,0.08)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: @json(__('Likes')),
                        data: data.likes,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239,68,68,0.08)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.4,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 12 } } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}` } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, maxTicksLimit: 10 } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 11 }, precision: 0 } },
                },
            },
        });
    }

    $wire.on('chart-data-updated', (event) => {
        // Small delay to allow DOM to settle after Livewire morph
        setTimeout(() => buildChart(event.chartData), 50);
    });
</script>
@endscript
