@php $currentFilter = request()->get('chart_filter_period', 'last_6_months'); @endphp

<div class="rounded-md shadow-sm border border-gray-200 dark:border-gray-700 p-4 py-6 z-1 bg-white dark:bg-gray-800">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-white">
            {{ __('User Growth') }}
        </h3>
        <div class="flex gap-2 items-center">
            <span
                class="bg-indigo-100 text-indigo-900 px-4 py-2 rounded-full text-sm">
                {{ __(ucfirst(str_replace('_', ' ', $currentFilter))) }}
            </span>

            <!-- Alpine Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="btn-primary flex items-center gap-2">
                    <iconify-icon icon="lucide:sliders"></iconify-icon>
                    {{ __('Filter') }}
                    <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute right-0 mt-2 w-44 rounded-md shadow-sm bg-white dark:bg-gray-700 z-10">
                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=last_6_months"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_6_months' ? 'bg-blue-100 dark:bg-gray-600' : '' }}">
                                <span class="ml-2"> {{ __('Last 6 months') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=last_12_months"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_12_months' ? 'bg-blue-100 dark:bg-gray-600' : '' }}">
                                <span class="ml-2"> {{ __('Last 12 months') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=this_year"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'this_year' ? 'bg-blue-100 dark:bg-gray-600' : '' }}">
                                <span class="ml-2"> {{ __('This year') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=last_year"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_year' ? 'bg-blue-100 dark:bg-gray-600' : '' }}">
                                <span class="ml-2"> {{ __('Last year') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=last_30_days"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_30_days' ? 'bg-blue-100 dark:bg-gray-600' : '' }}">
                                <span class="ml-2"> {{ __('Last 30 days') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=last_7_days"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_7_days' ? 'bg-blue-100 dark:bg-gray-600' : '' }}">
                                <span class="ml-2"> {{ __('Last 7 days') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=this_month"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'this_month' ? 'bg-blue-100 dark:bg-gray-600' : '' }}">
                                <span class="ml-2"> {{ __('This month') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section with ApexCharts - Increased height -->
    <div class="h-60" id="area-chart"></div>

    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pass the current filter to JavaScript
            const currentFilter = "{{ $currentFilter }}";

            // The server returns labels + data already shaped for the selected
            // period (see UserChartService) — render them as-is. NEVER fabricate
            // fallback numbers: an empty range must show an empty chart.
            const chartCategories = Array.isArray(userGrowthLabels) ? userGrowthLabels : [];
            const chartData = Array.isArray(userGrowthData) ? userGrowthData : [];

            const options = {
                chart: {
                    height: "100%",
                    maxWidth: "100%",
                    type: "area",
                    fontFamily: "var(--font-sans)",
                    dropShadow: {
                        enabled: false,
                    },
                    toolbar: {
                        show: false,
                    },
                    sparkline: {
                        enabled: false,
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    // Add padding to ensure chart content stays within bounds
                    padding: {
                        top: 0,
                        right: 20,
                        bottom: 0,
                        left: 20
                    }
                },
                tooltip: {
                    enabled: true,
                    x: {
                        show: false,
                    },
                    y: {
                        formatter: function(value) {
                            return value;
                        },
                        title: {
                            show: false
                        }
                    },
                    theme: 'light',
                    style: {
                        fontSize: '14px',
                        fontFamily: 'var(--font-sans)'
                    },
                    marker: {
                        show: false,
                    },
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        const value = series[seriesIndex][dataPointIndex];
                        return `<div class="relative px-3 py-1 bg-indigo-50 text-indigo-600 font-medium">
                            ${value}

                        </div>`;
                    },
                    intersect: false,
                    shared: false,
                    fixed: {
                        enabled: false
                    }
                },
                markers: {
                    size: 0,
                    strokeWidth: 0,
                    hover: {
                        size: 6,
                        sizeOffset: 3
                    }
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        opacityFrom: 0.55,
                        opacityTo: 0,
                        shade: "#635BFF",
                        gradientToColors: ["#635BFF"],
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    width: 6,
                    curve: 'smooth',
                    colors: ['#635BFF'],
                    lineCap: 'round' // Rounded line ends prevent edge cutoffs
                },
                grid: {
                    show: false,
                    strokeDashArray: 4,
                    padding: {
                        left: 15,
                        right: 15,
                        top: 20,
                        bottom: 20 // Increased bottom padding
                    },
                    yaxis: {
                        lines: {
                            show: true
                        }
                    },
                    xaxis: {
                        lines: {
                            show: false
                        }
                    },
                    position: 'back'
                },
                series: [{
                    name: "Users",
                    data: chartData,
                    color: "#635BFF",
                }],
                xaxis: {
                    categories: chartCategories,
                    labels: {
                        show: true,
                        style: {
                            colors: '#64748b',
                            fontSize: '12px',
                            fontFamily: 'var(--font-sans)',
                            fontWeight: 500,
                        },
                    },
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    min: 0, // Explicitly set minimum to keep line within bounds
                    // Increase max slightly to provide more headroom
                    max: function(max) {
                        //
                        return max;
                    },
                    labels: {
                        show: true,
                        style: {
                            colors: '#64748b',
                            fontSize: '12px',
                            fontFamily: 'var(--font-sans)',
                            fontWeight: 500
                        },
                        formatter: function(value) {
                            return value;
                        }
                    },
                    floating: false,
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    }
                },
                responsive: [{
                    breakpoint: 640,
                    options: {
                        chart: {
                            height: 300
                        }
                    }
                }]
            };

            if (document.getElementById("area-chart") && typeof ApexCharts !== 'undefined') {
                document.getElementById("area-chart").style.minHeight = "300px"; // Increased minimum height
                const chart = new ApexCharts(document.getElementById("area-chart"), options);
                chart.render();
            }
        });
    </script>
</div>
