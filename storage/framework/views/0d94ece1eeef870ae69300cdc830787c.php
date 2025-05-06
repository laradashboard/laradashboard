<?php use \Illuminate\Support\Str; ?>
<?php if (isset($component)) { $__componentOriginaldf0c1f9d71acfa8b3005f4638b1a29f0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf0c1f9d71acfa8b3005f4638b1a29f0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.card','data' => ['cols' => $cols,'rows' => $rows,'class' => $class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cols),'rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rows),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($class)]); ?>
    <?php if (isset($component)) { $__componentOriginal7ce092db05b46b96a8ad5ab4b8902a89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ce092db05b46b96a8ad5ab4b8902a89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.card-header','data' => ['name' => 'Queues','xBind:title' => '`Time: '.e(number_format($time)).'ms; Run at: ${formatDate(\''.e($runAt).'\')};`','details' => 'past '.e($this->periodForHumans()).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::card-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'Queues','x-bind:title' => '`Time: '.e(number_format($time)).'ms; Run at: ${formatDate(\''.e($runAt).'\')};`','details' => 'past '.e($this->periodForHumans()).'']); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal6d2babd9bee3f22fa7a6e616484e3ebd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d2babd9bee3f22fa7a6e616484e3ebd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.queue-list','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.queue-list'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d2babd9bee3f22fa7a6e616484e3ebd)): ?>
<?php $attributes = $__attributesOriginal6d2babd9bee3f22fa7a6e616484e3ebd; ?>
<?php unset($__attributesOriginal6d2babd9bee3f22fa7a6e616484e3ebd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d2babd9bee3f22fa7a6e616484e3ebd)): ?>
<?php $component = $__componentOriginal6d2babd9bee3f22fa7a6e616484e3ebd; ?>
<?php unset($__componentOriginal6d2babd9bee3f22fa7a6e616484e3ebd); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[rgba(107,114,128,0.5)]"></div>
                    Queued
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[rgba(147,51,234,0.5)]"></div>
                    Processing
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[#9333ea]"></div>
                    Processed
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[#eab308]"></div>
                    Released
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 font-medium">
                    <div class="h-0.5 w-3 rounded-full bg-[#e11d48]"></div>
                    Failed
                </div>
            </div>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ce092db05b46b96a8ad5ab4b8902a89)): ?>
<?php $attributes = $__attributesOriginal7ce092db05b46b96a8ad5ab4b8902a89; ?>
<?php unset($__attributesOriginal7ce092db05b46b96a8ad5ab4b8902a89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ce092db05b46b96a8ad5ab4b8902a89)): ?>
<?php $component = $__componentOriginal7ce092db05b46b96a8ad5ab4b8902a89; ?>
<?php unset($__componentOriginal7ce092db05b46b96a8ad5ab4b8902a89); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalbea25b6319928d1c693b59ced602f799 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbea25b6319928d1c693b59ced602f799 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.scroll','data' => ['expand' => $expand,'wire:poll.5s' => '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::scroll'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expand' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($expand),'wire:poll.5s' => '']); ?>
        <!--[if BLOCK]><![endif]--><?php if($queues->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginal5fa7cfb847383b1e105a397b36250360 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fa7cfb847383b1e105a397b36250360 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.no-results','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::no-results'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5fa7cfb847383b1e105a397b36250360)): ?>
<?php $attributes = $__attributesOriginal5fa7cfb847383b1e105a397b36250360; ?>
<?php unset($__attributesOriginal5fa7cfb847383b1e105a397b36250360); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5fa7cfb847383b1e105a397b36250360)): ?>
<?php $component = $__componentOriginal5fa7cfb847383b1e105a397b36250360; ?>
<?php unset($__componentOriginal5fa7cfb847383b1e105a397b36250360); ?>
<?php endif; ?>
        <?php else: ?>
            <div class="grid gap-3 mx-px mb-px">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $queues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue => $readings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div wire:key="<?php echo e($queue); ?>">
                        <h3 class="font-bold text-gray-700 dark:text-gray-300">
                            <!--[if BLOCK]><![endif]--><?php if($showConnection): ?>
                                <?php echo e($queue); ?>

                            <?php else: ?>
                                <?php echo e(Str::after($queue, ':')); ?>

                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </h3>
                        <?php
                            $highest = $readings->flatten()->max();
                        ?>

                        <div class="mt-3 relative">
                            <div class="absolute -left-px -top-2 max-w-fit h-4 flex items-center px-1 text-xs leading-none text-white font-bold bg-purple-500 rounded after:[--triangle-size:4px] after:border-l-purple-500 after:absolute after:right-[calc(-1*var(--triangle-size))] after:top-[calc(50%-var(--triangle-size))] after:border-t-[length:var(--triangle-size)] after:border-b-[length:var(--triangle-size)] after:border-l-[length:var(--triangle-size)] after:border-transparent">
                                <!--[if BLOCK]><![endif]--><?php if($config['sample_rate'] < 1): ?>
                                    <span title="Sample rate: <?php echo e($config['sample_rate']); ?>, Raw value: <?php echo e(number_format($highest)); ?>">~<?php echo e(number_format($highest * (1 / $config['sample_rate']))); ?></span>
                                <?php else: ?>
                                    <?php echo e(number_format($highest)); ?>

                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div
                                wire:ignore
                                class="h-14"
                                x-data="queueChart({
                                    queue: '<?php echo e($queue); ?>',
                                    readings: <?php echo \Illuminate\Support\Js::from($readings)->toHtml() ?>,
                                    sampleRate: <?php echo e($config['sample_rate']); ?>,
                                })"
                            >
                                <canvas x-ref="canvas" class="ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbea25b6319928d1c693b59ced602f799)): ?>
<?php $attributes = $__attributesOriginalbea25b6319928d1c693b59ced602f799; ?>
<?php unset($__attributesOriginalbea25b6319928d1c693b59ced602f799); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbea25b6319928d1c693b59ced602f799)): ?>
<?php $component = $__componentOriginalbea25b6319928d1c693b59ced602f799; ?>
<?php unset($__componentOriginalbea25b6319928d1c693b59ced602f799); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf0c1f9d71acfa8b3005f4638b1a29f0)): ?>
<?php $attributes = $__attributesOriginaldf0c1f9d71acfa8b3005f4638b1a29f0; ?>
<?php unset($__attributesOriginaldf0c1f9d71acfa8b3005f4638b1a29f0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf0c1f9d71acfa8b3005f4638b1a29f0)): ?>
<?php $component = $__componentOriginaldf0c1f9d71acfa8b3005f4638b1a29f0; ?>
<?php unset($__componentOriginaldf0c1f9d71acfa8b3005f4638b1a29f0); ?>
<?php endif; ?>

    <?php
        $__scriptKey = '2690582070-0';
        ob_start();
    ?>
<script>
Alpine.data('queueChart', (config) => ({
    init() {
        let chart = new Chart(
            this.$refs.canvas,
            {
                type: 'line',
                data: {
                    labels: this.labels(config.readings),
                    datasets: [
                        {
                            label: 'Queued',
                            borderColor: 'rgba(107,114,128,0.5)',
                            data: this.scale(config.readings.queued),
                            order: 4,
                        },
                        {
                            label: 'Processing',
                            borderColor: 'rgba(147,51,234,0.5)',
                            data: this.scale(config.readings.processing),
                            order: 3,
                        },
                        {
                            label: 'Released',
                            borderColor: '#eab308',
                            data: this.scale(config.readings.released),
                            order: 2,
                        },
                        {
                            label: 'Processed',
                            borderColor: '#9333ea',
                            data: this.scale(config.readings.processed),
                            order: 1,
                        },
                        {
                            label: 'Failed',
                            borderColor: '#e11d48',
                            data: this.scale(config.readings.failed),
                            order: 0,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        autoPadding: false,
                        padding: {
                            top: 1,
                        },
                    },
                    datasets: {
                        line: {
                            borderWidth: 2,
                            borderCapStyle: 'round',
                            pointHitRadius: 10,
                            pointStyle: false,
                            tension: 0.2,
                            spanGaps: false,
                            segment: {
                                borderColor: (ctx) => ctx.p0.raw === 0 && ctx.p1.raw === 0 ? 'transparent' : undefined,
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                        },
                        y: {
                            display: false,
                            min: 0,
                            max: this.highest(config.readings),
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
                                beforeBody: (context) => context
                                    .map(item => `${item.dataset.label}: ${config.sampleRate < 1 ? '~' : ''}${item.formattedValue}`)
                                    .join(', '),
                                label: () => null,
                            },
                        },
                    },
                },
            }
        )

        Livewire.on('queues-chart-update', ({ queues }) => {
            if (chart === undefined) {
                return
            }

            if (queues[config.queue] === undefined && chart) {
                chart.destroy()
                chart = undefined
                return
            }

            chart.data.labels = this.labels(queues[config.queue])
            chart.options.scales.y.max = this.highest(queues[config.queue])
            chart.data.datasets[0].data = this.scale(queues[config.queue].queued)
            chart.data.datasets[1].data = this.scale(queues[config.queue].processing)
            chart.data.datasets[2].data = this.scale(queues[config.queue].released)
            chart.data.datasets[3].data = this.scale(queues[config.queue].processed)
            chart.data.datasets[4].data = this.scale(queues[config.queue].failed)
            chart.update()
        })
    },
    labels(readings) {
        return Object.keys(readings.queued).map(formatDate)
    },
    scale(data) {
        return Object.values(data).map(value => value * (1 / config.sampleRate ))
    },
    highest(readings) {
        return Math.max(...Object.values(readings).map(dataset => Math.max(...Object.values(dataset)))) * (1 / config.sampleRate)
    }
}))
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/livewire/queues.blade.php ENDPATH**/ ?>