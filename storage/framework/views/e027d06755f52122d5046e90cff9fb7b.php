<div <?php echo e($attributes->merge(['class' => 'h-full flex flex-col items-center justify-center p-4'])); ?>>
    <?php if (isset($component)) { $__componentOriginal46c97e57c97de5a473566ea233e78af9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal46c97e57c97de5a473566ea233e78af9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.no-pulse','data' => ['class' => 'h-8 w-8 stroke-gray-300 dark:stroke-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.no-pulse'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-8 w-8 stroke-gray-300 dark:stroke-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal46c97e57c97de5a473566ea233e78af9)): ?>
<?php $attributes = $__attributesOriginal46c97e57c97de5a473566ea233e78af9; ?>
<?php unset($__attributesOriginal46c97e57c97de5a473566ea233e78af9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal46c97e57c97de5a473566ea233e78af9)): ?>
<?php $component = $__componentOriginal46c97e57c97de5a473566ea233e78af9; ?>
<?php unset($__componentOriginal46c97e57c97de5a473566ea233e78af9); ?>
<?php endif; ?>
    <p class="mt-2 text-sm text-gray-400 dark:text-gray-600">
        No results
    </p>
</div>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/components/no-results.blade.php ENDPATH**/ ?>