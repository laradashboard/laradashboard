<?php
$cols = ! empty($cols) ? $cols : 'full';
$rows = ! empty($rows) ? $rows : 1;
?>
<section class="h-[52px] flex items-center justify-between default:col-span-full default:lg:col-span-<?php echo e($cols); ?> default:row-span-<?php echo e($rows); ?> <?php echo e($class ?? ''); ?>">
    <div class="mt-4 h-8 w-1/12 bg-gray-100 dark:bg-gray-900 rounded animate-pulse"></div>
    <div class="mt-4 h-8 w-3/12 bg-gray-100 dark:bg-gray-900 rounded animate-pulse"></div>
    <div class="mt-4 h-8 w-3/12 bg-gray-100 dark:bg-gray-900 rounded animate-pulse"></div>
    <div class="mt-4 h-8 w-2/12 bg-gray-100 dark:bg-gray-900 rounded animate-pulse"></div>
</section>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/components/servers-placeholder.blade.php ENDPATH**/ ?>