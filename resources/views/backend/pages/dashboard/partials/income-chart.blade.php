{{--
    Shared filter bar for all income charts.
    Variables expected: $income_data, $incomeFilter, $filterLabels
--}}
@php
    $incomeFilter = request()->get('income_filter_period', 'last_6_months');
    $filterLabels = [
        'last_6_months'  => __('Last 6 months'),
        'last_12_months' => __('Last 12 months'),
        'this_year'      => __('This year'),
        'last_year'      => __('Last year'),
        'last_30_days'   => __('Last 30 days'),
        'last_7_days'    => __('Last 7 days'),
        'this_month'     => __('This month'),
    ];
@endphp

{{-- Filter bar (full-width row above the four cards) --}}
<div class="col-span-12 flex flex-wrap justify-between items-center gap-3 mb-2">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ $filterLabels[$incomeFilter] }}
    </p>
    <div class="flex gap-2 items-center">
        <span class="bg-indigo-100 text-indigo-900 px-4 py-2 rounded-full text-sm">
            {{ $filterLabels[$incomeFilter] }}
        </span>
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="btn-primary flex items-center gap-2">
                <iconify-icon icon="lucide:sliders"></iconify-icon>
                {{ __('Filter') }}
                <iconify-icon icon="lucide:chevron-down"></iconify-icon>
            </button>
            <div x-show="open" @click.outside="open = false" x-transition
                 class="absolute right-0 mt-2 w-48 rounded-md shadow-sm bg-white dark:bg-gray-700 z-10">
                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                    @foreach($filterLabels as $value => $label)
                    <li>
                        <a href="{{ route('admin.dashboard') }}?income_filter_period={{ $value }}"
                           class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 {{ $incomeFilter === $value ? 'bg-blue-100 dark:bg-gray-600' : '' }}">
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Four individual chart cards --}}
@include('backend.pages.dashboard.partials.income-chart-total')
@include('backend.pages.dashboard.partials.income-chart-commission')
@include('backend.pages.dashboard.partials.income-chart-protection')
@include('backend.pages.dashboard.partials.income-chart-verification')
