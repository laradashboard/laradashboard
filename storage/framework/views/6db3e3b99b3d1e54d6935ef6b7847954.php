<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>

<?php if (isset($component)) { $__componentOriginalfe9411472bfb5f0ad02390097d2c9c02 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe9411472bfb5f0ad02390097d2c9c02 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.cursor-arrow-rays','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.cursor-arrow-rays'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

<?php echo e($slot ?? ""); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe9411472bfb5f0ad02390097d2c9c02)): ?>
<?php $attributes = $__attributesOriginalfe9411472bfb5f0ad02390097d2c9c02; ?>
<?php unset($__attributesOriginalfe9411472bfb5f0ad02390097d2c9c02); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe9411472bfb5f0ad02390097d2c9c02)): ?>
<?php $component = $__componentOriginalfe9411472bfb5f0ad02390097d2c9c02; ?>
<?php unset($__componentOriginalfe9411472bfb5f0ad02390097d2c9c02); ?>
<?php endif; ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\storage\framework\views/a32eb67a00b577b8bde93086dbc66202.blade.php ENDPATH**/ ?>