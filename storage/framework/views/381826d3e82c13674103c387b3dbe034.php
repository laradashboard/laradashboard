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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.card-header','data' => ['name' => 'Exceptions','xBind:title' => '`Time: '.e(number_format($time)).'ms; Run at: ${formatDate(\''.e($runAt).'\')};`','details' => 'past '.e($this->periodForHumans()).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::card-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'Exceptions','x-bind:title' => '`Time: '.e(number_format($time)).'ms; Run at: ${formatDate(\''.e($runAt).'\')};`','details' => 'past '.e($this->periodForHumans()).'']); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginalb7b02ea37fa7178ea61c6355f95c620a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb7b02ea37fa7178ea61c6355f95c620a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.bug-ant','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.bug-ant'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb7b02ea37fa7178ea61c6355f95c620a)): ?>
<?php $attributes = $__attributesOriginalb7b02ea37fa7178ea61c6355f95c620a; ?>
<?php unset($__attributesOriginalb7b02ea37fa7178ea61c6355f95c620a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb7b02ea37fa7178ea61c6355f95c620a)): ?>
<?php $component = $__componentOriginalb7b02ea37fa7178ea61c6355f95c620a; ?>
<?php unset($__componentOriginalb7b02ea37fa7178ea61c6355f95c620a); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal1b3e53436ecefa44bef3b401e2b29716 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b3e53436ecefa44bef3b401e2b29716 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.select','data' => ['wire:model.live' => 'orderBy','id' => 'select-exceptions-order-by','label' => 'Sort by','options' => [
                    'count' => 'count',
                    'latest' => 'latest',
                ],'@change' => 'loading = true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'orderBy','id' => 'select-exceptions-order-by','label' => 'Sort by','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                    'count' => 'count',
                    'latest' => 'latest',
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
        <!--[if BLOCK]><![endif]--><?php if($exceptions->isEmpty()): ?>
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
<?php $component->withAttributes([]); ?>Type <?php echo $__env->renderComponent(); ?>
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
<?php $component->withAttributes(['class' => 'text-right']); ?>Latest <?php echo $__env->renderComponent(); ?>
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
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $exceptions->take(100); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exception): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr wire:key="<?php echo e($exception->class.$exception->location); ?>-spacer" class="h-2 first:h-0"></tr>
                        <tr wire:key="<?php echo e($exception->class.$exception->location); ?>-row">
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
                                <code class="block text-xs text-gray-900 dark:text-gray-100 truncate" title="<?php echo e($exception->class); ?>">
                                    <?php echo e($exception->class); ?>

                                </code>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate" title="<?php echo e($exception->location); ?>">
                                    <?php echo e($exception->location); ?>

                                </p>
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
                                <?php echo e($exception->latest->ago(syntax: Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true)); ?>

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
                                    <span title="Sample rate: <?php echo e($config['sample_rate']); ?>, Raw value: <?php echo e(number_format($exception->count)); ?>">~<?php echo e(number_format($exception->count * (1 / $config['sample_rate']))); ?></span>
                                <?php else: ?>
                                    <?php echo e(number_format($exception->count)); ?>

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

        <!--[if BLOCK]><![endif]--><?php if($exceptions->count() > 100): ?>
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
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/livewire/exceptions.blade.php ENDPATH**/ ?>