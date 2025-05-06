<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['expand' => false]));

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

foreach (array_filter((['expand' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<div
    x-data="{
        init() {
            $nextTick(() => this.scroll())
        },
        scroll() {
            const { content, fade } = this.$refs

            if (! fade) {
                return
            }

            const distanceToBottom = content.scrollHeight - (content.scrollTop + content.clientHeight)

            if (distanceToBottom >= 24) {
                fade.style.transform = `scaleY(1)`
            } else {
                fade.style.transform = `scaleY(${distanceToBottom / 24})`
            }
        }
    }"
    <?php echo e($attributes->merge(['class' => '@container/scroll-wrapper flex-grow flex w-full overflow-hidden' . ($expand ? '' : ' basis-56'), ':class' => "loading && 'opacity-25 animate-pulse'"])); ?>

>
    <div x-ref="content" class="flex-grow basis-full overflow-y-auto scrollbar:w-1.5 scrollbar:h-1.5 scrollbar:bg-transparent scrollbar-track:bg-gray-100 scrollbar-thumb:rounded scrollbar-thumb:bg-gray-300 scrollbar-track:rounded dark:scrollbar-track:bg-gray-500/10 dark:scrollbar-thumb:bg-gray-500/50 supports-scrollbars" @scroll.debounce.5ms="scroll">
        <?php echo e($slot); ?>

        <div x-ref="fade" class="h-6 origin-bottom fixed bottom-0 left-0 right-0 bg-gradient-to-t from-white dark:from-gray-900 pointer-events-none" wire:ignore></div>
    </div>
</div>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/components/scroll.blade.php ENDPATH**/ ?>