

<?php $__env->startSection('title'); ?>
<?php echo e(__('Action Logs - ' . config('app.name'))); ?>

<?php $__env->stopSection(); ?>

<?php
    $isActionLogExist = false;
?>
<?php $__env->startSection('admin-content'); ?>
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <div x-data="{ pageName: <?php echo e(__('Action Logs')); ?> }">
            <!-- Page Header -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName"><?php echo e(__('Action Logs')); ?></h2>

                <nav>
                    <ol class="flex items-center gap-1.5">
                        <li>
                            <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                                href="<?php echo e(route('admin.dashboard')); ?>">
                                <?php echo e(__('Home')); ?>

                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName"><?php echo e(__('Action Logs')); ?></li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Action Logs Table -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90"><?php echo e(__('Action Logs')); ?></h3>
                    <?php echo $__env->make('backend.partials.search-form', [
                        'placeholder' => __('Search by title or type'),
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <div class="flex items-center justify-center">
                        <button id="dropdownDefault" data-dropdown-toggle="dropdown" class="btn-primary flex items-center justify-center gap-2" type="button ">
                            <i class="bi bi-sliders"></i>
                            <?php echo e(__('Filter')); ?>

                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="dropdown" class="z-10 hidden w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                            <ul class="space-y-2">
                                <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded"
                                    onclick="handleSelect('')">
                                    <?php echo e(__('All')); ?>

                                </li>
                                <?php $__currentLoopData = \App\Enums\ActionType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded <?php echo e($type->value === request('type') ? 'bg-gray-200 dark:bg-gray-600' : ''); ?>"
                                        onclick="handleSelect('<?php echo e($type->value); ?>')">
                                        <?php echo e(__(ucfirst($type->value))); ?>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                    <table id="actionLogsTable" class="w-full dark:text-gray-400">
                        <thead class="bg-light text-capitalize">
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    <?php echo e(__('Sl')); ?></th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    <?php echo e(__('Type')); ?></th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    <?php echo e(__('Title')); ?></th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    <?php echo e(__('Action By')); ?></th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    <?php echo e(__('Data')); ?></th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    <?php echo e(__('Date')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $actionLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="<?php echo e($loop->index + 1 != count($actionLogs) ?  'border-b border-gray-100 dark:border-gray-800' : ''); ?>">
                                    <td class="px-5 py-4 sm:px-6 text-left"><?php echo e($loop->index + 1); ?></td>
                                    <td class="px-5 py-4 sm:px-6 text-left capitalize"><?php echo e($log->type); ?></td>
                                    <td class="px-5 py-4 sm:px-6 text-left"><?php echo e($log->title); ?></td>
                                    <td class="px-5 py-4 sm:px-6 text-left">
                                        <?php echo e($log->user->name . ' (' . $log->user->username . ')' ?? ''); ?></td>
                                    <td class="px-5 py-4 sm:px-6 text-left">
                                        <button id="expand-btn-<?php echo e($log->id); ?>" class="text-blue-500 text-sm mt-2"
                                            data-modal-target="json-modal-<?php echo e($log->id); ?>"
                                            data-modal-toggle="json-modal-<?php echo e($log->id); ?>">
                                            <?php echo e(__('Expand JSON')); ?>

                                        </button>

                                        <?php if (isset($component)) { $__componentOriginal7dad860b88c6aad331aae8c85065ce47 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7dad860b88c6aad331aae8c85065ce47 = $attributes; } ?>
<?php $component = App\View\Components\ActionLogModal::resolve(['log' => $log] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-log-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\ActionLogModal::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7dad860b88c6aad331aae8c85065ce47)): ?>
<?php $attributes = $__attributesOriginal7dad860b88c6aad331aae8c85065ce47; ?>
<?php unset($__attributesOriginal7dad860b88c6aad331aae8c85065ce47); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7dad860b88c6aad331aae8c85065ce47)): ?>
<?php $component = $__componentOriginal7dad860b88c6aad331aae8c85065ce47; ?>
<?php unset($__componentOriginal7dad860b88c6aad331aae8c85065ce47); ?>
<?php endif; ?>
                                    </td>

                                    <td class="px-5 py-4 sm:px-6 text-left">
                                        <?php echo e($log->created_at->format('d M Y H:i A')); ?>

                                    </td>
                                </tr>
                                <?php
                                    $isActionLogExist = true;
                                ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <?php
                                    $isActionLogExist = false;
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-gray-500 dark:text-gray-400"><?php echo e(__('No action logs found')); ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="my-4 px-4 sm:px-6">
                        <?php echo e($actionLogs->links()); ?>

                    </div>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>


<?php if($isActionLogExist): ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.querySelector('[data-modal-toggle="json-modal-<?php echo e($log->id); ?>"]').addEventListener('click',
                function() {
                    document.getElementById('json-modal-<?php echo e($log->id); ?>').classList.remove('hidden');
                });

            document.querySelector('[data-modal-hide="json-modal-<?php echo e($log->id); ?>"]').addEventListener('click', function() {
                document.getElementById('json-modal-<?php echo e($log->id); ?>').classList.add('hidden');
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function handleSelect(value) {
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('type', value);
            window.location.href = currentUrl.toString();
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('backend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/pages/action-logs/index.blade.php ENDPATH**/ ?>