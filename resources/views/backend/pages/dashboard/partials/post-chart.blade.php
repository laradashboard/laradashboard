<div class="col-span-12">
    <x-dashboard-collapsible-card
        :title="__('Post Activity')"
        icon="heroicons:chart-bar"
        icon-bg="bg-emerald-100 dark:bg-emerald-900/30"
        icon-color="text-emerald-600 dark:text-emerald-400"
        storage-key="dashboard_post_activity"
    >
        <div id="post-activity-chart" class="h-80"></div>
    </x-dashboard-collapsible-card>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const brandColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#635bff';
        const brandColor300 = getComputedStyle(document.documentElement).getPropertyValue('--color-brand-300').trim() || '#fb923c';

        const postData = @json($post_stats);

        const options = {
            series: [
                { name: '{{ __("Published") }}', data: postData.published },
                { name: '{{ __("Draft") }}', data: postData.draft }
            ],
            chart: {
                type: 'bar',
                height: 320,
                stacked: true,
                fontFamily: 'var(--font-sans)',
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            plotOptions: {
                bar: { horizontal: false, columnWidth: '55%', borderRadius: 5, dataLabels: { total: { enabled: false } } }
            },
            dataLabels: { enabled: false },
            stroke: { width: 2, colors: ['transparent'] },
            xaxis: {
                categories: postData.labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#64748b', fontFamily: 'var(--font-sans)' } }
            },
            yaxis: {
                title: { text: '{{ __("Number of Posts") }}', style: { color: '#64748b', fontFamily: 'var(--font-sans)' } },
                labels: { style: { colors: '#64748b', fontFamily: 'var(--font-sans)' } }
            },
            fill: { opacity: 1, colors: [brandColor, brandColor300] },
            tooltip: {
                y: { formatter: function (val) { return val + " {{ __('posts') }}" } },
                style: { fontFamily: 'var(--font-sans)' }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                offsetY: 0,
                fontFamily: 'var(--font-sans)',
                markers: { width: 12, height: 12, radius: 12 }
            },
            grid: { show: true, borderColor: '#e2e8f0', strokeDashArray: 4, position: 'back' },
            responsive: [{ breakpoint: 480, options: { legend: { position: 'bottom', offsetY: 0 } } }]
        };

        const chart = new ApexCharts(document.querySelector("#post-activity-chart"), options);
        chart.render();
    });
</script>
@endpush
