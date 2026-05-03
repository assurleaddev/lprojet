<div class="col-span-12">

    {{-- 2 columns × 2 rows: area charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @include('backend.pages.dashboard.partials.income-chart-total')
        @include('backend.pages.dashboard.partials.income-chart-commission')
        @include('backend.pages.dashboard.partials.income-chart-protection')
        @include('backend.pages.dashboard.partials.income-chart-verification')
    </div>

    {{-- Purchase source pie (full-width row below) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="md:col-span-1">
            @include('backend.pages.dashboard.partials.income-chart-sources')
        </div>
        <div class="md:col-span-2 rounded-md shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 py-5 flex flex-col justify-center gap-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ __('Source Breakdown') }}</p>
            @php
                $total = $order_source_data['direct'] + $order_source_data['offer'] + $order_source_data['live'];
            @endphp
            @foreach([
                ['label' => __('Direct Checkout'), 'key' => 'direct', 'color' => '#635BFF', 'bg' => 'rgba(99,91,255,.1)'],
                ['label' => __('Offer Checkout'),  'key' => 'offer',  'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.1)'],
                ['label' => __('Live Auction'),    'key' => 'live',   'color' => '#ef4444', 'bg' => 'rgba(239,68,68,.1)'],
            ] as $row)
            @php
                $count = $order_source_data[$row['key']];
                $pct   = $total ? round($count / $total * 100) : 0;
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

</div>
