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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.card-header','data' => ['name' => 'Cache','xBind:title' => '`Global Time: '.e(number_format($allTime)).'ms; Global run at: ${formatDate(\''.e($allRunAt).'\')}; Key Time: '.e(number_format($keyTime)).'ms; Key run at: ${formatDate(\''.e($keyRunAt).'\')};`','details' => 'past '.e($this->periodForHumans()).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::card-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'Cache','x-bind:title' => '`Global Time: '.e(number_format($allTime)).'ms; Global run at: ${formatDate(\''.e($allRunAt).'\')}; Key Time: '.e(number_format($keyTime)).'ms; Key run at: ${formatDate(\''.e($keyRunAt).'\')};`','details' => 'past '.e($this->periodForHumans()).'']); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal197841d80392575485f3cb39b2fb6a72 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal197841d80392575485f3cb39b2fb6a72 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.rocket-launch','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.rocket-launch'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal197841d80392575485f3cb39b2fb6a72)): ?>
<?php $attributes = $__attributesOriginal197841d80392575485f3cb39b2fb6a72; ?>
<?php unset($__attributesOriginal197841d80392575485f3cb39b2fb6a72); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal197841d80392575485f3cb39b2fb6a72)): ?>
<?php $component = $__componentOriginal197841d80392575485f3cb39b2fb6a72; ?>
<?php unset($__componentOriginal197841d80392575485f3cb39b2fb6a72); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php
                $count = count($config['groups']);
                $message = sprintf(
                    "Keys may be normalized using groups.\n\nThere %s currently %d %s configured.",
                    $count === 1 ? 'is' : 'are',
                    $count,
                    Str::plural('group', $count)
                );
            ?>
            <button title="<?php echo e($message); ?>" @click="alert(<?php echo \Illuminate\Support\Js::from($message)->toHtml() ?>)">
                <?php if (isset($component)) { $__componentOriginal95da57cee6437fd1b7658be6198e84c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95da57cee6437fd1b7658be6198e84c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.information-circle','data' => ['class' => 'w-5 h-5 stroke-gray-400 dark:stroke-gray-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.information-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 stroke-gray-400 dark:stroke-gray-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95da57cee6437fd1b7658be6198e84c4)): ?>
<?php $attributes = $__attributesOriginal95da57cee6437fd1b7658be6198e84c4; ?>
<?php unset($__attributesOriginal95da57cee6437fd1b7658be6198e84c4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95da57cee6437fd1b7658be6198e84c4)): ?>
<?php $component = $__componentOriginal95da57cee6437fd1b7658be6198e84c4; ?>
<?php unset($__componentOriginal95da57cee6437fd1b7658be6198e84c4); ?>
<?php endif; ?>
            </button>
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
        <!--[if BLOCK]><![endif]--><?php if($allCacheInteractions->hits === 0 && $allCacheInteractions->misses === 0): ?>
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
            <div class="flex flex-col gap-6">
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="flex flex-col justify-center @sm:block">
                        <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                            <!--[if BLOCK]><![endif]--><?php if($config['sample_rate'] < 1): ?>
                                <span title="Sample rate: <?php echo e($config['sample_rate']); ?>, Raw value: <?php echo e(number_format($allCacheInteractions->hits)); ?>">~<?php echo e(number_format($allCacheInteractions->hits * (1 / $config['sample_rate']))); ?></span>
                            <?php else: ?>
                                <?php echo e(number_format($allCacheInteractions->hits)); ?>

                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </span>
                        <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                            Hits
                        </span>
                    </div>
                    <div class="flex flex-col justify-center @sm:block">
                        <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                            <!--[if BLOCK]><![endif]--><?php if($config['sample_rate'] < 1): ?>
                                <span title="Sample rate: <?php echo e($config['sample_rate']); ?>, Raw value: <?php echo e(number_format($allCacheInteractions->misses)); ?>">~<?php echo e(number_format(($allCacheInteractions->misses) * (1 / $config['sample_rate']))); ?></span>
                            <?php else: ?>
                                <?php echo e(number_format($allCacheInteractions->misses)); ?>

                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </span>
                        <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                            Misses
                        </span>
                    </div>
                    <div class="flex flex-col justify-center @sm:block">
                        <span class="text-xl uppercase font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                            <?php echo e(((int) ($allCacheInteractions->hits / ($allCacheInteractions->hits + $allCacheInteractions->misses) * 10000)) / 100); ?>%
                        </span>
                        <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                            Hit Rate
                        </span>
                    </div>
                </div>
                <div>
                    <?php if (isset($component)) { $__componentOriginal57b56d9bf4bb712ab0672e731342ca20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b56d9bf4bb712ab0672e731342ca20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <colgroup>
                            <col width="100%" />
                            <col width="0%" />
                            <col width="0%" />
                            <col width="0%" />
                        </colgroup>
                        <?php if (isset($component)) { $__componentOriginala671ab62c9d1ec15f3a4b40b3dc642f2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala671ab62c9d1ec15f3a4b40b3dc642f2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.thead','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::thead'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                            <tr>
                                <?php if (isset($component)) { $__componentOriginalb946e47637b44b2c956915152ad6423b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb946e47637b44b2c956915152ad6423b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.th','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>Key <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb946e47637b44b2c956915152ad6423b)): ?>
<?php $attributes = $__attributesOriginalb946e47637b44b2c956915152ad6423b; ?>
<?php unset($__attributesOriginalb946e47637b44b2c956915152ad6423b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb946e47637b44b2c956915152ad6423b)): ?>
<?php $component = $__componentOriginalb946e47637b44b2c956915152ad6423b; ?>
<?php unset($__componentOriginalb946e47637b44b2c956915152ad6423b); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginalb946e47637b44b2c956915152ad6423b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb946e47637b44b2c956915152ad6423b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.th','data' => ['class' => 'text-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-right']); ?>Hits <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb946e47637b44b2c956915152ad6423b)): ?>
<?php $attributes = $__attributesOriginalb946e47637b44b2c956915152ad6423b; ?>
<?php unset($__attributesOriginalb946e47637b44b2c956915152ad6423b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb946e47637b44b2c956915152ad6423b)): ?>
<?php $component = $__componentOriginalb946e47637b44b2c956915152ad6423b; ?>
<?php unset($__componentOriginalb946e47637b44b2c956915152ad6423b); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginalb946e47637b44b2c956915152ad6423b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb946e47637b44b2c956915152ad6423b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.th','data' => ['class' => 'text-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-right']); ?>Misses <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb946e47637b44b2c956915152ad6423b)): ?>
<?php $attributes = $__attributesOriginalb946e47637b44b2c956915152ad6423b; ?>
<?php unset($__attributesOriginalb946e47637b44b2c956915152ad6423b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb946e47637b44b2c956915152ad6423b)): ?>
<?php $component = $__componentOriginalb946e47637b44b2c956915152ad6423b; ?>
<?php unset($__componentOriginalb946e47637b44b2c956915152ad6423b); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginalb946e47637b44b2c956915152ad6423b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb946e47637b44b2c956915152ad6423b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.th','data' => ['class' => 'text-right whitespace-nowrap']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-right whitespace-nowrap']); ?>Hit Rate <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb946e47637b44b2c956915152ad6423b)): ?>
<?php $attributes = $__attributesOriginalb946e47637b44b2c956915152ad6423b; ?>
<?php unset($__attributesOriginalb946e47637b44b2c956915152ad6423b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb946e47637b44b2c956915152ad6423b)): ?>
<?php $component = $__componentOriginalb946e47637b44b2c956915152ad6423b; ?>
<?php unset($__componentOriginalb946e47637b44b2c956915152ad6423b); ?>
<?php endif; ?>
                            </tr>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala671ab62c9d1ec15f3a4b40b3dc642f2)): ?>
<?php $attributes = $__attributesOriginala671ab62c9d1ec15f3a4b40b3dc642f2; ?>
<?php unset($__attributesOriginala671ab62c9d1ec15f3a4b40b3dc642f2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala671ab62c9d1ec15f3a4b40b3dc642f2)): ?>
<?php $component = $__componentOriginala671ab62c9d1ec15f3a4b40b3dc642f2; ?>
<?php unset($__componentOriginala671ab62c9d1ec15f3a4b40b3dc642f2); ?>
<?php endif; ?>
                        <tbody>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cacheKeyInteractions->take(100); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $interaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr wire:key="<?php echo e($interaction->key); ?>-spacer" class="h-2 first:h-0"></tr>
                                <tr wire:key="<?php echo e($interaction->key); ?>-row">
                                    <?php if (isset($component)) { $__componentOriginal0f7c58665987afe4db27bde6fd86e915 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0f7c58665987afe4db27bde6fd86e915 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.td','data' => ['class' => 'max-w-[1px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'max-w-[1px]']); ?>
                                        <code class="block text-xs text-gray-900 dark:text-gray-100 truncate" title="<?php echo e($interaction->key); ?>">
                                            <?php echo e($interaction->key); ?>

                                        </code>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0f7c58665987afe4db27bde6fd86e915)): ?>
<?php $attributes = $__attributesOriginal0f7c58665987afe4db27bde6fd86e915; ?>
<?php unset($__attributesOriginal0f7c58665987afe4db27bde6fd86e915); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0f7c58665987afe4db27bde6fd86e915)): ?>
<?php $component = $__componentOriginal0f7c58665987afe4db27bde6fd86e915; ?>
<?php unset($__componentOriginal0f7c58665987afe4db27bde6fd86e915); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginal0f7c58665987afe4db27bde6fd86e915 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0f7c58665987afe4db27bde6fd86e915 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.td','data' => ['numeric' => true,'class' => 'text-gray-700 dark:text-gray-300 font-bold']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['numeric' => true,'class' => 'text-gray-700 dark:text-gray-300 font-bold']); ?>
                                        <!--[if BLOCK]><![endif]--><?php if($config['sample_rate'] < 1): ?>
                                            <span title="Sample rate: <?php echo e($config['sample_rate']); ?>, Raw value: <?php echo e(number_format($interaction->hits)); ?>">~<?php echo e(number_format($interaction->hits * (1 / $config['sample_rate']))); ?></span>
                                        <?php else: ?>
                                            <?php echo e(number_format($interaction->hits)); ?>

                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0f7c58665987afe4db27bde6fd86e915)): ?>
<?php $attributes = $__attributesOriginal0f7c58665987afe4db27bde6fd86e915; ?>
<?php unset($__attributesOriginal0f7c58665987afe4db27bde6fd86e915); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0f7c58665987afe4db27bde6fd86e915)): ?>
<?php $component = $__componentOriginal0f7c58665987afe4db27bde6fd86e915; ?>
<?php unset($__componentOriginal0f7c58665987afe4db27bde6fd86e915); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginal0f7c58665987afe4db27bde6fd86e915 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0f7c58665987afe4db27bde6fd86e915 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.td','data' => ['numeric' => true,'class' => 'text-gray-700 dark:text-gray-300 font-bold']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['numeric' => true,'class' => 'text-gray-700 dark:text-gray-300 font-bold']); ?>
                                        <!--[if BLOCK]><![endif]--><?php if($config['sample_rate'] < 1): ?>
                                            <span title="Sample rate: <?php echo e($config['sample_rate']); ?>, Raw value: <?php echo e(number_format($interaction->misses)); ?>">~<?php echo e(number_format($interaction->misses * (1 / $config['sample_rate']))); ?></span>
                                        <?php else: ?>
                                            <?php echo e(number_format($interaction->misses)); ?>

                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0f7c58665987afe4db27bde6fd86e915)): ?>
<?php $attributes = $__attributesOriginal0f7c58665987afe4db27bde6fd86e915; ?>
<?php unset($__attributesOriginal0f7c58665987afe4db27bde6fd86e915); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0f7c58665987afe4db27bde6fd86e915)): ?>
<?php $component = $__componentOriginal0f7c58665987afe4db27bde6fd86e915; ?>
<?php unset($__componentOriginal0f7c58665987afe4db27bde6fd86e915); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginal0f7c58665987afe4db27bde6fd86e915 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0f7c58665987afe4db27bde6fd86e915 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.td','data' => ['numeric' => true,'class' => 'text-gray-700 dark:text-gray-300 font-bold']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['numeric' => true,'class' => 'text-gray-700 dark:text-gray-300 font-bold']); ?>
                                        <?php echo e(((int) ($interaction->hits / ($interaction->hits + $interaction->misses) * 10000)) / 100); ?>%
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0f7c58665987afe4db27bde6fd86e915)): ?>
<?php $attributes = $__attributesOriginal0f7c58665987afe4db27bde6fd86e915; ?>
<?php unset($__attributesOriginal0f7c58665987afe4db27bde6fd86e915); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0f7c58665987afe4db27bde6fd86e915)): ?>
<?php $component = $__componentOriginal0f7c58665987afe4db27bde6fd86e915; ?>
<?php unset($__componentOriginal0f7c58665987afe4db27bde6fd86e915); ?>
<?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal57b56d9bf4bb712ab0672e731342ca20)): ?>
<?php $attributes = $__attributesOriginal57b56d9bf4bb712ab0672e731342ca20; ?>
<?php unset($__attributesOriginal57b56d9bf4bb712ab0672e731342ca20); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal57b56d9bf4bb712ab0672e731342ca20)): ?>
<?php $component = $__componentOriginal57b56d9bf4bb712ab0672e731342ca20; ?>
<?php unset($__componentOriginal57b56d9bf4bb712ab0672e731342ca20); ?>
<?php endif; ?>

                    <!--[if BLOCK]><![endif]--><?php if($cacheKeyInteractions->count() > 100): ?>
                        <div class="mt-2 text-xs text-gray-400 text-center">Limited to 100 entries</div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
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
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/livewire/cache.blade.php ENDPATH**/ ?>