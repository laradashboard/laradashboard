<?php if (isset($component)) { $__componentOriginaldf0c1f9d71acfa8b3005f4638b1a29f0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf0c1f9d71acfa8b3005f4638b1a29f0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.card','data' => ['cols' => $cols ?? null,'rows' => $rows ?? null,'class' => $class ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cols ?? null),'rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rows ?? null),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($class ?? '')]); ?>
    <div class="h-[30px] flex items-center w-full mb-3 @md:mb-6">
        <div class="rounded bg-gray-50 dark:bg-gray-800 h-6 w-1/2 animate-pulse"></div>
    </div>
    <div class="space-y-4 h-56">
        <div class="rounded bg-gray-50 dark:bg-gray-800 h-12 animate-pulse"></div>
        <div class="rounded bg-gray-50 dark:bg-gray-800 h-12 animate-pulse"></div>
        <div class="rounded bg-gray-50 dark:bg-gray-800 h-12 animate-pulse"></div>
    </div>
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
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/components/placeholder.blade.php ENDPATH**/ ?>