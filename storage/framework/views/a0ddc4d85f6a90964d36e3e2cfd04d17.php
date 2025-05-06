<a href="<?php echo e(url()->previous()); ?>" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
    <i class="bi bi-arrow-left mr-2"></i>
    <?php echo e(__('Back')); ?>

</a>

<a href="<?php echo e(route('admin.dashboard')); ?>" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
    <i class="bi bi-grid mr-2"></i>
    <?php echo e(__('Back to Dashboard')); ?>

</a>

<form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
    <?php echo csrf_field(); ?>
    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
        <?php echo e(__('Login Again')); ?>

        <i class="bi bi-arrow-right ml-2"></i>
    </button>
</form>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/errors/partials/links.blade.php ENDPATH**/ ?>