

<?php $__env->startSection('title'); ?>
<?php echo e(__('Dashboard Page')); ?> - <?php echo e(config('app.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('before_vite_build'); ?>
<script>
    var userGrowthData = <?php echo json_encode($user_growth_data['data'], 15, 512) ?>;
    var userGrowthLabels = <?php echo json_encode($user_growth_data['labels'], 15, 512) ?>;
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 space-y-6">
            <div class="grid grid-cols-3 gap-4 md:grid-cols-5 md:gap-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800"
                    >
                        <i class="bi bi-people dark:text-white text-2xl"></i>
                    </div>

                    <div class="mt-5 flex items-end justify-between">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e(__('Users')); ?>

                            </span>
                            <h4
                                class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90"
                            >
                                <?php echo e($total_users); ?>

                            </h4>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800"
                    >
                        <i class="bi bi-shield-check dark:text-white text-2xl"></i>
                    </div>

                    <div class="mt-5 flex items-end justify-between">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e(__('Roles')); ?>

                            </span>
                            <h4
                                class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90"
                            >
                                <?php echo e($total_roles); ?>

                            </h4>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800"
                    >
                        <i class="bi bi-list-check dark:text-white text-2xl"></i>
                    </div>

                    <div class="mt-5 flex items-end justify-between">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo e(__('Permissions')); ?>

                            </span>
                            <h4
                                class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90"
                            >
                                <?php echo e($total_permissions); ?>

                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <!-- User growth chart. -->
        <?php echo $__env->make('components.charts.user-growth-chart', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/pages/dashboard/index.blade.php ENDPATH**/ ?>