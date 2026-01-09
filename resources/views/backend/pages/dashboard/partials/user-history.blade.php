<div class="w-full bg-white border border-gray-200 dark:border-gray-700 rounded-md shadow-sm dark:bg-gray-800 p-4"
     x-data="{ collapsed: localStorage.getItem('dashboard_user_history_collapsed') === 'true' }"
     x-init="$watch('collapsed', val => localStorage.setItem('dashboard_user_history_collapsed', val))">
    <div class="flex justify-between items-center">
        <div class="flex justify-center items-center">
            <h5 class="text-lg font-semibold leading-none text-gray-700 dark:text-white pe-1">
                {{ __('Users History') }}
            </h5>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" data-tooltip-target="data-tooltip" data-tooltip-placement="bottom"
                onclick="window.location.href='{{ route('admin.users.index') }}'"
                class="hidden sm:inline-flex items-center justify-center text-gray-500 w-8 h-8 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-md text-sm">
            </button>
            <button @click="collapsed = !collapsed"
                    class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :title="collapsed ? '{{ __('Expand') }}' : '{{ __('Collapse') }}'">
                <iconify-icon icon="heroicons:chevron-down"
                              class="text-gray-500 dark:text-gray-400 transition-transform duration-200"
                              :class="{ 'rotate-180': collapsed }"></iconify-icon>
            </button>
        </div>
    </div>

    <!-- Donut Chart -->
    <div x-show="!collapsed" x-collapse>
        <div id="donut-chart"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get brand color from CSS variable
            const brandColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#6366f1';

            // Get user counts from controller data
            const newUsers = @json($user_history_data['new_users'] ?? 0);
            const oldUsers = @json($user_history_data['old_users'] ?? 0);

            const getChartOptions = () => {
                return {
                    series: [oldUsers, newUsers], // Old Users, New Users
                    colors: ["#f3f4f6", brandColor], // Slight gray and Brand color
                    chart: {
                        height: 320,
                        width: "100%",
                        type: "donut",
                    },
                    stroke: {
                        colors: ["transparent"],
                        lineCap: "",
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontFamily: "var(--font-sans)",
                                        offsetY: 20,
                                    },
                                    total: {
                                        showAlways: true,
                                        show: true,
                                        fontFamily: "var(--font-sans)",
                                        label: "{{ __('Total') }}",
                                        formatter: function(w) {
                                            const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                            return sum + " {{ __('users') }}"
                                        },
                                    },
                                    value: {
                                        show: true,
                                        fontFamily: "var(--font-sans)",
                                        offsetY: -20,
                                        formatter: function(value) {
                                            return value + " {{ __('users') }}"
                                        },
                                    },
                                },
                                size: "80%",
                            },
                        },
                    },
                    grid: {
                        padding: {
                            top: -2,
                        },
                    },
                    labels: [
                        "{{ __('Old Users (before 1 month)') }}",
                        "{{ __('New Users (last 30 days)') }}"
                    ],
                    dataLabels: {
                        enabled: false,
                    },
                    legend: {
                        position: "bottom",
                        fontFamily: "var(--font-sans)",
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value) {
                                return value + " users"
                            },
                        },
                    },
                    xaxis: {
                        labels: {
                            formatter: function(value) {
                                return value + " users"
                            },
                        },
                        axisTicks: {
                            show: false,
                        },
                        axisBorder: {
                            show: false,
                        },
                    },
                }
            }

            if (document.getElementById("donut-chart") && typeof ApexCharts !== 'undefined') {
                const chart = new ApexCharts(document.getElementById("donut-chart"), getChartOptions());
                chart.render();
            }
        });
    </script>
</div>
