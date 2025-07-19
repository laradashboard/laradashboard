<div class="col-span-12">
    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-white">{{ __('Post Activity') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Posts created over time') }}</p>
            </div>
        </div>
        <div id="post-activity-chart" class="h-80"></div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Post data from the server
        const postData = @json($post_stats);

        // Chart options
        const options = {
            series: [
                {
                    name: '{{ __("Published") }}',
                    data: postData.published
                },
                {
                    name: '{{ __("Draft") }}',
                    data: postData.draft
                }
            ],
            chart: {
                type: 'bar',
                height: 320,
                stacked: true,
                fontFamily: 'var(--font-sans)',
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: false,
                        zoom: false,
                        zoomin: false,
                        zoomout: false,
                        pan: false,
                        reset: false
                    }
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 5,
                    dataLabels: {
                        total: {
                            enabled: false
                        }
                    }
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: postData.labels,
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#64748b',
                        fontFamily: 'var(--font-sans)'
                    }
                }
            },
            yaxis: {
                title: {
                    text: '{{ __("Number of Posts") }}',
                    style: {
                        color: '#64748b',
                        fontFamily: 'var(--font-sans)'
                    }
                },
                labels: {
                    style: {
                        colors: '#64748b',
                        fontFamily: 'var(--font-sans)'
                    }
                }
            },
            fill: {
                opacity: 1,
                colors: ['#635bff', '#fb923c']
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " {{ __('posts') }}"
                    }
                },
                style: {
                    fontFamily: 'var(--font-sans)'
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                offsetY: -30,
                fontFamily: 'var(--font-sans)',
                markers: {
                    width: 12,
                    height: 12,
                    radius: 12
                }
            },
            grid: {
                show: true,
                borderColor: '#e2e8f0',
                strokeDashArray: 4,
                position: 'back'
            },
            responsive: [
                {
                    breakpoint: 480,
                    options: {
                        legend: {
                            position: 'bottom',
                            offsetY: 0
                        }
                    }
                }
            ]
        };

        const chart = new ApexCharts(document.querySelector("#post-activity-chart"), options);
        chart.render();
    });
</script>
@endpush
