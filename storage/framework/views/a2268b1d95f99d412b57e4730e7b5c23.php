<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name' => '', 'title' => '', 'details' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['name' => '', 'title' => '', 'details' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<header class="flex flex-wrap justify-between items-center gap-4 mb-3 @md:mb-6">
    <div class="flex-1 basis-0 flex-grow-[10000] max-w-full">
        <div class="flex overflow-hidden gap-2 items-start">
            <!--[if BLOCK]><![endif]--><?php if(isset($icon)): ?>
                <div class="[&>svg]:flex-shrink-0 [&>svg]:w-6 [&>svg]:h-6 [&>svg]:stroke-gray-400 [&>svg]:dark:stroke-gray-600">
                    <?php echo e($icon); ?>

                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <hgroup class="flex flex-wrap items-baseline gap-x-2 overflow-hidden">
                <h2 class="text-base font-bold text-gray-600 dark:text-gray-300 truncate" <?php if($attributes->has('x-bind:title')): ?> x-bind:title="<?php echo e($attributes->get('x-bind:title')); ?>" <?php else: ?> title="<?php echo e($title); ?>" <?php endif; ?>><?php echo e($name); ?></h2>
                <!--[if BLOCK]><![endif]--><?php if($details): ?>
                    <p class="text-gray-400 dark:text-gray-600 font-medium truncate"><small class="text-xs"><?php echo e($details); ?></small></p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </hgroup>
        </div>
    </div>
    <!--[if BLOCK]><![endif]--><?php if($actions ?? false): ?>
        <div class="flex flex-grow">
            <div class="w-full flex items-center gap-4">
                <?php echo e($actions); ?>

            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</header>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/components/card-header.blade.php ENDPATH**/ ?>