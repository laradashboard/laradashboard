<script>
setDarkClass = () => {
    if (localStorage.theme === 'dark' || ((!('theme' in localStorage) || !['light', 'dark'].includes(localStorage.theme)) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
}

setDarkClass()

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', setDarkClass)
</script>

<div
    class="relative"
    x-data="{
        menu: false,
        theme: localStorage.theme,
        darkMode() {
            this.theme = 'dark'
            localStorage.theme = 'dark'
            setDarkClass()
        },
        lightMode() {
            this.theme = 'light'
            localStorage.theme = 'light'
            setDarkClass()
        },
        systemMode() {
            this.theme = undefined
            localStorage.removeItem('theme')
            setDarkClass()
        },
    }"
    @click.outside="menu = false"
>
    <button
        x-cloak
        class="block p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
        :class="theme ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-600 hover:text-gray-500 focus:text-gray-500 dark:hover:text-gray-500 dark:focus:text-gray-500'"
        @click="menu = ! menu"
    >
        <?php if (isset($component)) { $__componentOriginal858855d499f9eb5a6031e5d5375badf5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal858855d499f9eb5a6031e5d5375badf5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.sun','data' => ['class' => 'block dark:hidden w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.sun'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'block dark:hidden w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal858855d499f9eb5a6031e5d5375badf5)): ?>
<?php $attributes = $__attributesOriginal858855d499f9eb5a6031e5d5375badf5; ?>
<?php unset($__attributesOriginal858855d499f9eb5a6031e5d5375badf5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal858855d499f9eb5a6031e5d5375badf5)): ?>
<?php $component = $__componentOriginal858855d499f9eb5a6031e5d5375badf5; ?>
<?php unset($__componentOriginal858855d499f9eb5a6031e5d5375badf5); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalb9442beca206e9e1a6f2d6c61ea6424d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb9442beca206e9e1a6f2d6c61ea6424d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.moon','data' => ['class' => 'hidden dark:block w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.moon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'hidden dark:block w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb9442beca206e9e1a6f2d6c61ea6424d)): ?>
<?php $attributes = $__attributesOriginalb9442beca206e9e1a6f2d6c61ea6424d; ?>
<?php unset($__attributesOriginalb9442beca206e9e1a6f2d6c61ea6424d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb9442beca206e9e1a6f2d6c61ea6424d)): ?>
<?php $component = $__componentOriginalb9442beca206e9e1a6f2d6c61ea6424d; ?>
<?php unset($__componentOriginalb9442beca206e9e1a6f2d6c61ea6424d); ?>
<?php endif; ?>
    </button>

    <div x-show="menu" class="z-10 absolute origin-top-right right-0 bg-white dark:bg-gray-800 rounded-md ring-1 ring-gray-900/5 shadow-xl flex flex-col" style="display: none;" @click="menu = false">
        <button class="flex items-center px-4 py-2 gap-3 hover:bg-gray-100 dark:hover:bg-gray-700" :class="theme === 'light' ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'" @click="lightMode()">
            <?php if (isset($component)) { $__componentOriginal858855d499f9eb5a6031e5d5375badf5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal858855d499f9eb5a6031e5d5375badf5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.sun','data' => ['class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.sun'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal858855d499f9eb5a6031e5d5375badf5)): ?>
<?php $attributes = $__attributesOriginal858855d499f9eb5a6031e5d5375badf5; ?>
<?php unset($__attributesOriginal858855d499f9eb5a6031e5d5375badf5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal858855d499f9eb5a6031e5d5375badf5)): ?>
<?php $component = $__componentOriginal858855d499f9eb5a6031e5d5375badf5; ?>
<?php unset($__componentOriginal858855d499f9eb5a6031e5d5375badf5); ?>
<?php endif; ?>
            Light
        </button>
        <button class="flex items-center px-4 py-2 gap-3 hover:bg-gray-100 dark:hover:bg-gray-700" :class="theme === 'dark' ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'" @click="darkMode()">
            <?php if (isset($component)) { $__componentOriginalb9442beca206e9e1a6f2d6c61ea6424d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb9442beca206e9e1a6f2d6c61ea6424d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.moon','data' => ['class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.moon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb9442beca206e9e1a6f2d6c61ea6424d)): ?>
<?php $attributes = $__attributesOriginalb9442beca206e9e1a6f2d6c61ea6424d; ?>
<?php unset($__attributesOriginalb9442beca206e9e1a6f2d6c61ea6424d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb9442beca206e9e1a6f2d6c61ea6424d)): ?>
<?php $component = $__componentOriginalb9442beca206e9e1a6f2d6c61ea6424d; ?>
<?php unset($__componentOriginalb9442beca206e9e1a6f2d6c61ea6424d); ?>
<?php endif; ?>
            Dark
        </button>
        <button class="flex items-center px-4 py-2 gap-3 hover:bg-gray-100 dark:hover:bg-gray-700" :class="theme === undefined ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'" @click="systemMode()">
            <?php if (isset($component)) { $__componentOriginal441f229d609349d912fb10cce9320feb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal441f229d609349d912fb10cce9320feb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.computer-desktop','data' => ['class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.computer-desktop'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal441f229d609349d912fb10cce9320feb)): ?>
<?php $attributes = $__attributesOriginal441f229d609349d912fb10cce9320feb; ?>
<?php unset($__attributesOriginal441f229d609349d912fb10cce9320feb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal441f229d609349d912fb10cce9320feb)): ?>
<?php $component = $__componentOriginal441f229d609349d912fb10cce9320feb; ?>
<?php unset($__componentOriginal441f229d609349d912fb10cce9320feb); ?>
<?php endif; ?>
            System
        </button>
    </div>
</div>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/components/theme-switcher.blade.php ENDPATH**/ ?>