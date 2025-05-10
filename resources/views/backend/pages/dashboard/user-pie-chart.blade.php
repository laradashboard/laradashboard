<!-- filepath: g:\Development\Maniruzzaman Akash\laradashboard\resources\views\backend\pages\dashboard\user-pie-chart.blade.php -->
<div class="w-full bg-white rounded-xl shadow-sm dark:bg-gray-800 p-4 ">
    <div class="flex justify-between">
        <div class="flex justify-center items-center">
            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white pe-1">Users History</h5>
        </div>
        <div>
            <button type="button" data-tooltip-target="data-tooltip" data-tooltip-placement="bottom"
                class="hidden sm:inline-flex items-center justify-center text-gray-500 w-8 h-8 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm">
                <img src="{{ asset('/images/icons/move.svg') }}">
            </button>

        </div>
    </div>

    @php
        use Carbon\Carbon;
        use App\Models\User;
        $newUsers = \App\Models\User::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count();
        $oldUsers = \App\Models\User::where('created_at', '<', \Carbon\Carbon::now()->subDays(30))->count();
    @endphp

    <!-- Donut Chart -->
    <div class="" id="donut-chart"></div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get user counts from Blade variables
            const newUsers = @json($newUsers ?? 0);
            const oldUsers = @json($oldUsers ?? 0);

            const getChartOptions = () => {
                return {
                    series: [oldUsers, newUsers], // Old Users, New Users
                    colors: ["#f3f4f6", "#6366f1"], // Slight gray and Indigo
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
                                        fontFamily: "Inter, sans-serif",
                                        offsetY: 20,
                                    },
                                    total: {
                                        showAlways: true,
                                        show: true,
                                        fontFamily: "Inter, sans-serif",
                                        formatter: function(w) {
                                            const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                            return sum + ' users'
                                        },
                                    },
                                    value: {
                                        show: true,
                                        fontFamily: "Inter, sans-serif",
                                        offsetY: -20,
                                        formatter: function(value) {
                                            return value + " users"
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
                    labels: ["Old Users (before 1 month)", "New Users (last 30 days)"], // Users History labels
                    dataLabels: {
                        enabled: false,
                    },
                    legend: {
                        position: "bottom",
                        fontFamily: "Inter, sans-serif",
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

                // Optionally, remove or update the checkbox logic if not needed
                // Get all the checkboxes by their class name
                const checkboxes = document.querySelectorAll('#devices input[type="checkbox"]');

                // Function to handle the checkbox change event
                function handleCheckboxChange(event, chart) {
                    const checkbox = event.target;
                    if (checkbox.checked) {
                        switch (checkbox.value) {
                            case 'desktop':
                                chart.updateSeries([15.1, 22.5]); // Only 2 values
                                break;
                            case 'tablet':
                                chart.updateSeries([25.1, 26.5]);
                                break;
                            case 'mobile':
                                chart.updateSeries([45.1, 27.5]);
                                break;
                            default:
                                chart.updateSeries([55.1, 28.5]);
                        }
                    } else {
                        chart.updateSeries([35.1, 23.5]);
                    }
                }

                // Attach the event listener to each checkbox
                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', (event) => handleCheckboxChange(event, chart));
                });
            }
        });
    </script>
</div>
