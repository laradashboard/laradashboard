<?php
$friendlySize = function(int $mb, int $precision = 0) {
    if ($mb >= 1024 * 1024) {
        return round($mb / 1024 / 1024, $precision) . 'TB';
    }
    if ($mb >= 1024) {
        return round($mb / 1024, $precision) . 'GB';
    }
    return round($mb, $precision) . 'MB';
};

$cols = ! empty($cols) ? $cols : 'full';
$rows = ! empty($rows) ? $rows : 1;
?>

<section
    wire:poll.5s
    x-data="{
        loading: false,
        init() {
            Livewire.hook('commit', ({ component, succeed }) => {
                if (component.id === $wire.__instance.id) {
                    succeed(() => this.loading = false)
                }
            })
        },
    }"
    class="overflow-x-auto pb-px default:col-span-full default:lg:col-span-<?php echo e($cols); ?> default:row-span-<?php echo e($rows); ?> <?php echo e($class); ?>"
    :class="loading && 'opacity-25 animate-pulse'"
>
    <!--[if BLOCK]><![endif]--><?php if($servers->isNotEmpty()): ?>
        <div class="grid grid-cols-[max-content,minmax(max-content,1fr),max-content,minmax(min-content,2fr),max-content,minmax(min-content,2fr),minmax(max-content,1fr)]">
            <div></div>
            <div></div>
            <div class="text-xs uppercase text-left text-gray-500 dark:text-gray-400 font-bold">CPU</div>
            <div></div>
            <div class="text-xs uppercase text-left text-gray-500 dark:text-gray-400 font-bold">Memory</div>
            <div></div>
            <div class="text-xs uppercase text-left text-gray-500 dark:text-gray-400 font-bold">Storage</div>
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $servers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slug => $server): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div wire:key="<?php echo e($slug); ?>-indicator" class="flex items-center <?php echo e($servers->count() > 1 ? 'py-2' : ''); ?>" title="<?php echo e($server->updated_at->fromNow()); ?>">
                    <!--[if BLOCK]><![endif]--><?php if($server->recently_reported): ?>
                        <div class="w-5 flex justify-center mr-1">
                            <div class="h-1 w-1 bg-green-500 rounded-full animate-pulse"></div>
                        </div>
                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginal857115f97c046f7ae4146e4029156d3b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal857115f97c046f7ae4146e4029156d3b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.signal-slash','data' => ['class' => 'w-5 h-5 stroke-red-500 mr-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.signal-slash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 stroke-red-500 mr-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal857115f97c046f7ae4146e4029156d3b)): ?>
<?php $attributes = $__attributesOriginal857115f97c046f7ae4146e4029156d3b; ?>
<?php unset($__attributesOriginal857115f97c046f7ae4146e4029156d3b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal857115f97c046f7ae4146e4029156d3b)): ?>
<?php $component = $__componentOriginal857115f97c046f7ae4146e4029156d3b; ?>
<?php unset($__componentOriginal857115f97c046f7ae4146e4029156d3b); ?>
<?php endif; ?>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <div wire:key="<?php echo e($slug); ?>-name" class="flex items-center pr-8 xl:pr-12 <?php echo e($servers->count() > 1 ? 'py-2' : ''); ?> <?php echo e(! $server->recently_reported ? 'opacity-25 animate-pulse' : ''); ?>">
                    <?php if (isset($component)) { $__componentOriginalfc6db56b5f36df96362d68d607995f61 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc6db56b5f36df96362d68d607995f61 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.server','data' => ['class' => 'w-6 h-6 mr-2 stroke-gray-500 dark:stroke-gray-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.server'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 mr-2 stroke-gray-500 dark:stroke-gray-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc6db56b5f36df96362d68d607995f61)): ?>
<?php $attributes = $__attributesOriginalfc6db56b5f36df96362d68d607995f61; ?>
<?php unset($__attributesOriginalfc6db56b5f36df96362d68d607995f61); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc6db56b5f36df96362d68d607995f61)): ?>
<?php $component = $__componentOriginalfc6db56b5f36df96362d68d607995f61; ?>
<?php unset($__componentOriginalfc6db56b5f36df96362d68d607995f61); ?>
<?php endif; ?>
                    <span class="text-base font-bold text-gray-600 dark:text-gray-300" x-bind:title="`Time: <?php echo e(number_format($time)); ?>ms; Run at: ${formatDate('<?php echo e($runAt); ?>')};`"><?php echo e($server->name); ?></span>
                </div>
                <div wire:key="<?php echo e($slug); ?>-cpu" class="flex items-center <?php echo e($servers->count() > 1 ? 'py-2' : ''); ?> <?php echo e(! $server->recently_reported ? 'opacity-25 animate-pulse' : ''); ?>">
                    <div class="text-xl font-bold text-gray-700 dark:text-gray-200 w-14 whitespace-nowrap tabular-nums">
                        <?php echo e($server->cpu_current); ?>%
                    </div>
                </div>
                <div wire:key="<?php echo e($slug); ?>-cpu-graph" class="flex items-center pr-8 xl:pr-12 <?php echo e($servers->count() > 1 ? 'py-2' : ''); ?> <?php echo e(! $server->recently_reported ? 'opacity-25 animate-pulse' : ''); ?>">
                    <div
                        wire:ignore
                        class="w-full min-w-[5rem] max-w-xs h-9 relative"
                        x-data="cpuChart({
                            slug: '<?php echo e($slug); ?>',
                            labels: <?php echo \Illuminate\Support\Js::from($server->cpu->keys())->toHtml() ?>,
                            data: <?php echo \Illuminate\Support\Js::from($server->cpu->values())->toHtml() ?>,
                        })"
                    >
                        <canvas x-ref="canvas" class="w-full ring-1 ring-gray-900/5 bg-white dark:bg-gray-900 rounded-md shadow-sm"></canvas>
                    </div>
                </div>
                <div wire:key="<?php echo e($slug); ?>-memory" class="flex items-center <?php echo e($servers->count() > 1 ? 'py-2' : ''); ?> <?php echo e(! $server->recently_reported ? 'opacity-25 animate-pulse' : ''); ?>">
                    <div class="w-36 flex-shrink-0 whitespace-nowrap tabular-nums">
                        <span class="text-xl font-bold text-gray-700 dark:text-gray-200">
                            <?php echo e($friendlySize($server->memory_current, 1)); ?>

                        </span>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            / <?php echo e($friendlySize($server->memory_total, 1)); ?>

                        </span>
                    </div>
                </div>
                <div wire:key="<?php echo e($slug); ?>-memory-graph" class="flex items-center pr-8 xl:pr-12 <?php echo e($servers->count() > 1 ? 'py-2' : ''); ?> <?php echo e(! $server->recently_reported ? 'opacity-25 animate-pulse' : ''); ?>">
                    <div
                        wire:ignore
                        class="w-full min-w-[5rem] max-w-xs h-9 relative"
                        x-data="memoryChart({
                            slug: '<?php echo e($slug); ?>',
                            labels: <?php echo \Illuminate\Support\Js::from($server->memory->keys())->toHtml() ?>,
                            data: <?php echo \Illuminate\Support\Js::from($server->memory->values())->toHtml() ?>,
                            total: <?php echo \Illuminate\Support\Js::from($server->memory_total)->toHtml() ?>,
                        })"
                    >
                        <canvas x-ref="canvas" class="w-full ring-1 ring-gray-900/5 bg-white dark:bg-gray-900 rounded-md shadow-sm"></canvas>
                    </div>
                </div>
                <div wire:key="<?php echo e($slug); ?>-storage" class="flex items-center gap-8 <?php echo e($servers->count() > 1 ? 'py-2' : ''); ?> <?php echo e(! $server->recently_reported ? 'opacity-25 animate-pulse' : ''); ?>">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $server->storage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $storage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div wire:key="<?php echo e($slug.'-storage-'.$storage->directory); ?>" class="flex items-center gap-4" title="Directory: <?php echo e($storage->directory); ?>">
                            <div class="whitespace-nowrap tabular-nums">
                                <span class="text-xl font-bold text-gray-700 dark:text-gray-200"><?php echo e($friendlySize($storage->used)); ?></span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">/ <?php echo e($friendlySize($storage->total)); ?></span>
                            </div>

                            <div
                                wire:ignore
                                x-data="storageChart({
                                    slug: '<?php echo e($slug); ?>',
                                    directory: '<?php echo e($storage->directory); ?>',
                                    used: <?php echo e($storage->used); ?>,
                                    total: <?php echo e($storage->total); ?>,
                                })"
                            >
                                <canvas x-ref="canvas" class="h-8 w-8"></canvas>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</section>

    <?php
        $__scriptKey = '779173250-0';
        ob_start();
    ?>
<script>
Alpine.data('cpuChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.canvas,
            {
                type: 'line',
                data: {
                    labels: config.labels.map(formatDate),
                    datasets: [
                        {
                            label: 'CPU Percent',
                            borderColor: '#9333ea',
                            borderWidth: 2,
                            borderCapStyle: 'round',
                            data: config.data,
                            pointHitRadius: 10,
                            pointStyle: false,
                            tension: 0.2,
                            spanGaps: false,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        autoPadding: false,
                    },
                    scales: {
                        x: {
                            display: false,
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            display: false,
                            min: 0,
                            max: 100,
                            grid: {
                                display: false,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            mode: 'index',
                            position: 'nearest',
                            intersect: false,
                            callbacks: {
                                title: () => '',
                                label: (context) => `${context.label} - ${context.formattedValue}%`
                            },
                            displayColors: false,
                        },
                    },
                },
            }
        )

        Livewire.on('servers-chart-update', ({ servers }) => {
            if (chart === undefined) {
                return
            }

            if (servers[config.slug] === undefined && chart) {
                chart.destroy()
                chart = undefined
                return
            }

            chart.data.labels = Object.keys(servers[config.slug].cpu).map(formatDate)
            chart.data.datasets[0].data = Object.values(servers[config.slug].cpu)
            chart.update()
        })
    }
}))

Alpine.data('memoryChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.canvas,
            {
                type: 'line',
                data: {
                    labels: config.labels.map(formatDate),
                    datasets: [
                        {
                            label: 'Memory Used',
                            borderColor: '#9333ea',
                            borderWidth: 2,
                            borderCapStyle: 'round',
                            data: config.data,
                            pointHitRadius: 10,
                            pointStyle: false,
                            tension: 0.2,
                            spanGaps: false,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        autoPadding: false,
                    },
                    scales: {
                        x: {
                            display: false,
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            display: false,
                            min: 0,
                            max: config.total,
                            grid: {
                                display: false,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            mode: 'index',
                            position: 'nearest',
                            intersect: false,
                            callbacks: {
                                title: () => '',
                                label: (context) => `${context.label} - ${context.formattedValue} MB`
                            },
                            displayColors: false,
                        },
                    },
                },
            }
        )

        Livewire.on('servers-chart-update', ({ servers }) => {
            if (chart === undefined) {
                return
            }

            if (servers[config.slug] === undefined && chart) {
                chart.destroy()
                chart = undefined
                return
            }

            chart.data.labels = Object.keys(servers[config.slug].memory).map(formatDate)
            chart.data.datasets[0].data = Object.values(servers[config.slug].memory)
            chart.update()
        })
    }
}))

Alpine.data('storageChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.canvas,
            {
                type: 'doughnut',
                data: {
                    labels: ['Used', 'Free'],
                    datasets: [
                        {
                            data: [
                                config.used,
                                config.total - config.used,
                            ],
                            backgroundColor: [
                                '#9333ea',
                                '#c084fc30',
                            ],
                            hoverBackgroundColor: [
                                '#9333ea',
                                '#c084fc30',
                            ],
                        },
                    ],
                },
                options: {
                    borderWidth: 0,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            enabled: false,
                            callbacks: {
                                label: (context) => context.formattedValue + ' MB',
                            },
                            displayColors: false,
                        },
                    },
                },
            }
        )

        Livewire.on('servers-chart-update', ({ servers }) => {
            const storage = servers[config.slug]?.storage?.find(storage => storage.directory === config.directory)

            if (chart === undefined) {
                return
            }

            if (storage === undefined && chart) {
                chart.destroy()
                chart = undefined
                return
            }

            chart.data.datasets[0].data = [
                storage.used,
                storage.total - storage.used,
            ]
            chart.update()
        })
    }
}))
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/livewire/servers.blade.php ENDPATH**/ ?>