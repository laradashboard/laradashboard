

<?php $__env->startSection('title'); ?>
    404 - <?php echo e(__('Page Not Found')); ?> - <?php echo e(config('app.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="relative z-1 flex min-h-screen flex-col items-center justify-center overflow-hidden p-6">
    <div class="mx-auto w-full max-w-[242px] text-center sm:max-w-[472px]">
        <h1 class="mb-8 text-title-md font-bold text-gray-800 dark:text-white/90 xl:text-title-2xl">
            <?php echo e(__('ERROR')); ?>

        </h1>
        <h1 class="mb-8 text-title-md font-bold text-gray-800 dark:text-white/90 xl:text-title-2xl">
            404
        </h1>

        <p class="mt-2">
            <?php echo e(__('Sorry! Page Not Found!')); ?>

        </p>

        <p class="mb-6 mt-10 text-base text-gray-700 dark:text-gray-400 sm:text-lg">
            <?php echo e(__('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.')); ?>

        </p>

        <?php echo $__env->make('errors.partials.links', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.auth.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/errors/404.blade.php ENDPATH**/ ?>