<?php if (isset($component)) { $__componentOriginal99769021b7e4c25d84bbeb0f3c1fb268 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99769021b7e4c25d84bbeb0f3c1fb268 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '7224ff0b1fa42156c352216deff15b56::pulse','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pulse.servers', ['cols' => 'full']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1026542496-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pulse.usage', ['cols' => '4','rows' => '2']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1026542496-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pulse.queues', ['cols' => '4']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1026542496-2', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pulse.cache', ['cols' => '4']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1026542496-3', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pulse.slow-queries', ['cols' => '8']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1026542496-4', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pulse.exceptions', ['cols' => '6']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1026542496-5', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pulse.slow-requests', ['cols' => '6']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1026542496-6', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pulse.slow-jobs', ['cols' => '6']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1026542496-7', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pulse.slow-outgoing-requests', ['cols' => '6']);

$__html = app('livewire')->mount($__name, $__params, 'lw-1026542496-8', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99769021b7e4c25d84bbeb0f3c1fb268)): ?>
<?php $attributes = $__attributesOriginal99769021b7e4c25d84bbeb0f3c1fb268; ?>
<?php unset($__attributesOriginal99769021b7e4c25d84bbeb0f3c1fb268); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99769021b7e4c25d84bbeb0f3c1fb268)): ?>
<?php $component = $__componentOriginal99769021b7e4c25d84bbeb0f3c1fb268; ?>
<?php unset($__componentOriginal99769021b7e4c25d84bbeb0f3c1fb268); ?>
<?php endif; ?>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/vendor/pulse/dashboard.blade.php ENDPATH**/ ?>