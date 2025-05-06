<?php $currentFilter = request()->get('chart_filter_period', 'last_12_months'); ?>

<div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6"
>
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            <?php echo e(__("User Growth Report")); ?>

        </h3>

        <?php echo $__env->make('components.filters.date-filter', ['currentFilter' => $currentFilter], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <div class="ml-5 min-w-[650px] pl-2 xl:min-w-full">
            <div
                id="user-growth-chart"
                class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
            ></div>
        </div>
    </div>
</div>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/components/charts/user-growth-chart.blade.php ENDPATH**/ ?>