@php $currentFilter = request()->get('chart_filter_period', 'last_6_months'); @endphp

<x-dashboard-collapsible-card
    :title="__('User Growth')"
    icon="heroicons:chart-bar-square"
    icon-bg="bg-brand-100 dark:bg-brand-900/30"
    icon-color="text-brand-600 dark:text-brand-400"
    storage-key="dashboard_user_growth"
>
    <x-slot:headerActions>
        <div class="flex gap-2 items-center">
            <span class="px-4 py-2 rounded-full text-sm" style="background-color: var(--color-brand-100); color: var(--color-brand-800);">
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
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_6_months' ? 'dark:bg-gray-600' : '' }}" style="{{ $currentFilter === 'last_6_months' ? 'background-color: var(--color-brand-100);' : '' }}">
                                <span class="ml-2"> {{ __('Last 6 months') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=last_12_months"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_12_months' ? 'dark:bg-gray-600' : '' }}" style="{{ $currentFilter === 'last_12_months' ? 'background-color: var(--color-brand-100);' : '' }}">
                                <span class="ml-2"> {{ __('Last 12 months') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=this_year"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'this_year' ? 'dark:bg-gray-600' : '' }}" style="{{ $currentFilter === 'this_year' ? 'background-color: var(--color-brand-100);' : '' }}">
                                <span class="ml-2"> {{ __('This year') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=last_year"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_year' ? 'dark:bg-gray-600' : '' }}" style="{{ $currentFilter === 'last_year' ? 'background-color: var(--color-brand-100);' : '' }}">
                                <span class="ml-2"> {{ __('Last year') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=last_30_days"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_30_days' ? 'dark:bg-gray-600' : '' }}" style="{{ $currentFilter === 'last_30_days' ? 'background-color: var(--color-brand-100);' : '' }}">
                                <span class="ml-2"> {{ __('Last 30 days') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=last_7_days"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'last_7_days' ? 'dark:bg-gray-600' : '' }}" style="{{ $currentFilter === 'last_7_days' ? 'background-color: var(--color-brand-100);' : '' }}">
                                <span class="ml-2"> {{ __('Last 7 days') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}?chart_filter_period=this_month"
                               class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white {{ $currentFilter === 'this_month' ? 'dark:bg-gray-600' : '' }}" style="{{ $currentFilter === 'this_month' ? 'background-color: var(--color-brand-100);' : '' }}">
                                <span class="ml-2"> {{ __('This month') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </x-slot:headerActions>

    <div class="h-60" id="area-chart"></div>
</x-dashboard-collapsible-card>

<!-- ApexCharts JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get brand color from CSS variable
        const brandColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#635BFF';

        // Pass the current filter to JavaScript
        const currentFilter = "{{ $currentFilter }}";

        // Adjust chart options based on filter
        let chartCategories, chartData;

        if (currentFilter === 'last_6_months') {
            chartCategories = (userGrowthLabels || ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN']).slice(-6);
            chartData = (userGrowthData || [120, 270, 340, 415, 320, 560]).slice(-6);
        } else if (currentFilter === 'this_year') {
            const now = new Date();
            const currentMonth = now.getMonth();
            const thisYearLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'].slice(0, currentMonth + 1);
            chartCategories = userGrowthLabels ? userGrowthLabels.slice(0, currentMonth + 1) : thisYearLabels;
            chartData = userGrowthData ? userGrowthData.slice(0, currentMonth + 1) : [230, 280, 350, 310, 285, 390].slice(0, currentMonth + 1);
        } else if (currentFilter === 'last_year') {
            chartCategories = userGrowthLabels || ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            chartData = userGrowthData || [190, 220, 270, 330, 320, 410, 390, 380, 360, 300, 340, 370];
        } else if (currentFilter === 'last_30_days') {
            const last30DaysLabels = [];
            const last30DaysData = [];
            for (let i = 30; i > 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i + 1);
                last30DaysLabels.push(date.getDate().toString().padStart(2, '0'));
                last30DaysData.push(Math.floor(Math.random() * 40) + 10);
            }
            chartCategories = userGrowthLabels && userGrowthLabels.length >= 30 ? userGrowthLabels.slice(-30) : last30DaysLabels;
            chartData = userGrowthData && userGrowthData.length >= 30 ? userGrowthData.slice(-30) : last30DaysData;
        } else if (currentFilter === 'last_7_days') {
            const last7DaysLabels = [];
            const last7DaysData = [];
            for (let i = 7; i > 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i + 1);
                last7DaysLabels.push(date.getDate().toString().padStart(2, '0'));
                last7DaysData.push(Math.floor(Math.random() * 30) + 15);
            }
            chartCategories = userGrowthLabels && userGrowthLabels.length >= 7 ? userGrowthLabels.slice(-7) : last7DaysLabels;
            chartData = userGrowthData && userGrowthData.length >= 7 ? userGrowthData.slice(-7) : last7DaysData;
        } else if (currentFilter === 'this_month') {
            const now = new Date();
            const currentDay = now.getDate();
            const thisMonthLabels = [];
            const thisMonthData = [];
            for (let i = 1; i <= currentDay; i++) {
                thisMonthLabels.push(i.toString().padStart(2, '0'));
                thisMonthData.push(Math.floor(Math.random() * 28) + 12);
            }
            chartCategories = userGrowthLabels && userGrowthLabels.length >= currentDay ? userGrowthLabels.slice(0, currentDay) : thisMonthLabels;
            chartData = userGrowthData && userGrowthData.length >= currentDay ? userGrowthData.slice(0, currentDay) : thisMonthData;
        } else {
            chartCategories = userGrowthLabels || ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            chartData = userGrowthData || [120, 270, 340, 415, 320, 560, 420, 380, 365, 390, 400, 450];
        }

        const options = {
            chart: {
                height: "100%",
                maxWidth: "100%",
                type: "area",
                fontFamily: "var(--font-sans)",
                dropShadow: { enabled: false },
                toolbar: { show: false },
                sparkline: { enabled: false },
                animations: { enabled: true, easing: 'easeinout', speed: 800 },
                padding: { top: 0, right: 20, bottom: 0, left: 20 }
            },
            tooltip: {
                enabled: true,
                x: { show: false },
                y: { formatter: function(value) { return value; }, title: { show: false } },
                theme: 'light',
                style: { fontSize: '14px', fontFamily: 'var(--font-sans)' },
                marker: { show: false },
                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                    const value = series[seriesIndex][dataPointIndex];
                    return `<div class="relative px-3 py-1 font-medium" style="background-color: var(--color-brand-100); color: var(--color-brand-700);">${value}</div>`;
                },
                intersect: false,
                shared: false,
                fixed: { enabled: false }
            },
            markers: { size: 0, strokeWidth: 0, hover: { size: 6, sizeOffset: 3 } },
            fill: {
                type: "gradient",
                gradient: { opacityFrom: 0.55, opacityTo: 0, shade: brandColor, gradientToColors: [brandColor] }
            },
            dataLabels: { enabled: false },
            stroke: { width: 6, curve: 'smooth', colors: [brandColor], lineCap: 'round' },
            grid: {
                show: false,
                strokeDashArray: 4,
                padding: { left: 15, right: 15, top: 20, bottom: 20 },
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } },
                position: 'back'
            },
            series: [{ name: "Users", data: chartData, color: brandColor }],
            xaxis: {
                categories: chartCategories,
                labels: { show: true, style: { colors: '#64748b', fontSize: '12px', fontFamily: 'var(--font-sans)', fontWeight: 500 } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                max: function(max) { return max; },
                labels: { show: true, style: { colors: '#64748b', fontSize: '12px', fontFamily: 'var(--font-sans)', fontWeight: 500 }, formatter: function(value) { return value; } },
                floating: false,
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            responsive: [{ breakpoint: 640, options: { chart: { height: 300 } } }]
        };

        if (document.getElementById("area-chart") && typeof ApexCharts !== 'undefined') {
            document.getElementById("area-chart").style.minHeight = "300px";
            const chart = new ApexCharts(document.getElementById("area-chart"), options);
            chart.render();
        }
    });
</script>
