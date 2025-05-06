<div class="flex items-center space-x-4">
    <!-- Badge for selected filter -->
    <span
        class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-gray-700 dark:text-gray-200"
    >
        <?php echo e(ucfirst(str_replace("_", " ", $currentFilter))); ?>

    </span>

    <button id="dropdownDefaultButton" data-dropdown-toggle="dropdown" class="btn-primary flex items-center justify-center gap-2" type="button ">
        <i class="bi bi-sliders"></i>
        <?php echo e(__('Filter')); ?>

        <i class="bi bi-chevron-down"></i>
    </button>

    <!-- Dropdown menu -->
    <div
        id="dropdown"
        class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700"
    >
        <ul
            class="py-2 text-sm text-gray-700 dark:text-gray-200"
            aria-labelledby="dropdownDefaultButton"
        >
            <li>
                <a
                    href="<?php echo e(route('admin.dashboard')); ?>?chart_filter_period=last_12_months"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo e($currentFilter === 'last_12_months'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''); ?>"
                >
                    <span class="ml-2"> <?php echo e(__('Last 12 Months')); ?></span>
                </a>
            </li>
            <li>
                <a
                    href="<?php echo e(route('admin.dashboard')); ?>?chart_filter_period=this_year"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo e($currentFilter === 'this_year'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''); ?>"
                >
                    <span class="ml-2"> <?php echo e(__('This Year')); ?></span>
                </a>
            </li>
            <li>
                <a
                    href="<?php echo e(route('admin.dashboard')); ?>?chart_filter_period=last_year"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo e($currentFilter === 'last_year'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''); ?>"
                >
                    <span class="ml-2"> <?php echo e(__('Last Year')); ?></span>
                </a>
            </li>
            <li>
                <a
                    href="<?php echo e(route('admin.dashboard')); ?>?chart_filter_period=last_30_days"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo e($currentFilter === 'last_30_days'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''); ?>"
                >
                    <span class="ml-2"> <?php echo e(__('Last 30 Days')); ?></span>
                </a>
            </li>
            <li>
                <a
                    href="<?php echo e(route('admin.dashboard')); ?>?chart_filter_period=last_7_days"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo e($currentFilter === 'last_7_days'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''); ?>"
                >
                    <span class="ml-2"> <?php echo e(__('Last 7 Days')); ?></span>
                </a>
            </li>
            <li>
                <a
                    href="<?php echo e(route('admin.dashboard')); ?>?chart_filter_period=this_month"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo e($currentFilter === 'this_month'
                            ? 'bg-blue-100 dark:bg-gray-600'
                            : ''); ?>"
                >
                    <span class="ml-2"> <?php echo e(__('This Month')); ?></span>
                </a>
            </li>
        </ul>
    </div>
</div>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/components/filters/date-filter.blade.php ENDPATH**/ ?>