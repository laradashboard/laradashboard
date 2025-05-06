<?php
use \Doctrine\SqlFormatter\HtmlHighlighter;
use \Doctrine\SqlFormatter\SqlFormatter;

if ($this->wantsHighlighting()) {
    $sqlFormatter = new SqlFormatter(new HtmlHighlighter([
        HtmlHighlighter::HIGHLIGHT_RESERVED => 'class="font-semibold"',
        HtmlHighlighter::HIGHLIGHT_QUOTE => 'class="text-purple-200"',
        HtmlHighlighter::HIGHLIGHT_BACKTICK_QUOTE => 'class="text-purple-200"',
        HtmlHighlighter::HIGHLIGHT_BOUNDARY => 'class="text-cyan-200"',
        HtmlHighlighter::HIGHLIGHT_NUMBER => 'class="text-orange-200"',
        HtmlHighlighter::HIGHLIGHT_WORD => 'class="text-orange-200"',
        HtmlHighlighter::HIGHLIGHT_VARIABLE => 'class="text-orange-200"',
        HtmlHighlighter::HIGHLIGHT_ERROR => 'class="text-red-200"',
        HtmlHighlighter::HIGHLIGHT_COMMENT => 'class="text-gray-400"',
    ], false));
}
?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.card-header','data' => ['name' => 'Slow Queries','xBind:title' => '`Time: '.e(number_format($time)).'ms; Run at: ${formatDate(\''.e($runAt).'\')};`','details' => ''.e(is_array($config['threshold']) ? '' : $config['threshold'].'ms threshold, ').'past '.e($this->periodForHumans()).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::card-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'Slow Queries','x-bind:title' => '`Time: '.e(number_format($time)).'ms; Run at: ${formatDate(\''.e($runAt).'\')};`','details' => ''.e(is_array($config['threshold']) ? '' : $config['threshold'].'ms threshold, ').'past '.e($this->periodForHumans()).'']); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal2cae7b73642118b6b7fda70209953164 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2cae7b73642118b6b7fda70209953164 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.circle-stack','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.circle-stack'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2cae7b73642118b6b7fda70209953164)): ?>
<?php $attributes = $__attributesOriginal2cae7b73642118b6b7fda70209953164; ?>
<?php unset($__attributesOriginal2cae7b73642118b6b7fda70209953164); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2cae7b73642118b6b7fda70209953164)): ?>
<?php $component = $__componentOriginal2cae7b73642118b6b7fda70209953164; ?>
<?php unset($__componentOriginal2cae7b73642118b6b7fda70209953164); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal1b3e53436ecefa44bef3b401e2b29716 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b3e53436ecefa44bef3b401e2b29716 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.select','data' => ['wire:model.live' => 'orderBy','id' => 'select-slow-queries-order-by','label' => 'Sort by','options' => [
                    'slowest' => 'slowest',
                    'count' => 'count',
                ],'@change' => 'loading = true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'orderBy','id' => 'select-slow-queries-order-by','label' => 'Sort by','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                    'slowest' => 'slowest',
                    'count' => 'count',
                ]),'@change' => 'loading = true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b3e53436ecefa44bef3b401e2b29716)): ?>
<?php $attributes = $__attributesOriginal1b3e53436ecefa44bef3b401e2b29716; ?>
<?php unset($__attributesOriginal1b3e53436ecefa44bef3b401e2b29716); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b3e53436ecefa44bef3b401e2b29716)): ?>
<?php $component = $__componentOriginal1b3e53436ecefa44bef3b401e2b29716; ?>
<?php unset($__componentOriginal1b3e53436ecefa44bef3b401e2b29716); ?>
<?php endif; ?>
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
        <!--[if BLOCK]><![endif]--><?php if($slowQueries->isEmpty()): ?>
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
<?php $component->withAttributes([]); ?>Query <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes(['class' => 'text-right']); ?>Count <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes(['class' => 'text-right']); ?>Slowest <?php echo $__env->renderComponent(); ?>
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
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $slowQueries->take(100); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $query): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr wire:key="<?php echo e(md5($query->sql.$query->location)); ?>-spacer" class="h-2 first:h-0"></tr>
                        <tr wire:key="<?php echo e(md5($query->sql.$query->location)); ?>-row">
                            <?php if (isset($component)) { $__componentOriginal0f7c58665987afe4db27bde6fd86e915 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0f7c58665987afe4db27bde6fd86e915 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.td','data' => ['class' => '!p-0 truncate max-w-[1px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-0 truncate max-w-[1px]']); ?>
                                <div class="relative">
                                    <div class="bg-gray-700 dark:bg-gray-800 py-4 rounded-md text-gray-100 block text-xs whitespace-nowrap overflow-x-auto [scrollbar-color:theme(colors.gray.500)_transparent] [scrollbar-width:thin]">
                                        <code class="px-3"><?php echo $this->wantsHighlighting() ? $sqlFormatter->highlight($query->sql) : e($query->sql); ?></code>
                                        <!--[if BLOCK]><![endif]--><?php if($query->location): ?>
                                            <p class="px-3 mt-3 text-xs leading-none text-gray-400 dark:text-gray-500">
                                                <?php echo e($query->location); ?>

                                            </p>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <!--[if BLOCK]><![endif]--><?php if(is_array($config['threshold'])): ?>
                                            <p class="px-3 mt-3 text-xs leading-none text-gray-400 dark:text-gray-500">
                                                <?php echo e($query->threshold); ?>ms threshold
                                            </p>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="absolute top-0 right-0 bottom-0 rounded-r-md w-3 bg-gradient-to-r from-transparent to-gray-700 dark:to-gray-800 pointer-events-none"></div>
                                </div>
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
                                    <span title="Sample rate: <?php echo e($config['sample_rate']); ?>, Raw value: <?php echo e(number_format($query->count)); ?>">~<?php echo e(number_format($query->count * (1 / $config['sample_rate']))); ?></span>
                                <?php else: ?>
                                    <?php echo e(number_format($query->count)); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.td','data' => ['numeric' => true,'class' => 'text-gray-700 dark:text-gray-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::td'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['numeric' => true,'class' => 'text-gray-700 dark:text-gray-300']); ?>
                                <!--[if BLOCK]><![endif]--><?php if($query->slowest === null): ?>
                                    <strong>Unknown</strong>
                                <?php else: ?>
                                    <strong><?php echo e(number_format($query->slowest) ?: '<1'); ?></strong> ms
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
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($slowQueries->count() > 100): ?>
            <div class="mt-2 text-xs text-gray-400 text-center">Limited to 100 entries</div>
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
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/livewire/slow-queries.blade.php ENDPATH**/ ?>