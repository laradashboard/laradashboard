

<?php $__env->startSection('title'); ?>
    <?php echo e(__('Roles')); ?> | <?php echo e(config('app.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div x-data="{ pageName: '<?php echo e(__('Roles')); ?>' }">
        <!-- Page Header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName"><?php echo e(__('Roles')); ?></h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="<?php echo e(route('admin.dashboard')); ?>">
                            <?php echo e(__('Home')); ?>

                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName"><?php echo e(__('Roles')); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90"><?php echo e(__('Roles')); ?></h3>

                <?php echo $__env->make('backend.partials.search-form', [
                    'placeholder' => __('Search by role name'),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php if(auth()->user()->can('role.create')): ?>
                    <a href="<?php echo e(route('admin.roles.create')); ?>" class="btn-primary">
                        <i class="bi bi-plus-circle mr-2"></i>
                        <?php echo e(__('New Role')); ?>

                    </a>
                <?php endif; ?>
            </div>
            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                <?php echo $__env->make('backend.layouts.partials.messages', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <table id="dataTable" class="w-full dark:text-gray-400">
                    <thead class="bg-light text-capitalize">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th width="5%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5"><?php echo e(__('Sl')); ?></th>
                            <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5"><?php echo e(__('Name')); ?></th>
                            <th width="40%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white"><?php echo e(__('Permissions')); ?></th>
                            <th width="12%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white"><?php echo e(__('Action')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="<?php echo e($loop->index + 1 != count($roles) ?  'border-b border-gray-100 dark:border-gray-800' : ''); ?>">
                                <td class="px-5 py-4 sm:px-6"><?php echo e($loop->index + 1); ?></td>
                                <td class="px-5 py-4 sm:px-6">
                                    <?php echo e($role->name); ?>


                                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo e(__('Total Permissions:')); ?> <?php echo e($role->permissions->count()); ?>

                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div x-data="{ showAll: false }">
                                        <div>
                                            <?php $__currentLoopData = $role->permissions->take(7); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-white">
                                                    <?php echo e($permission->name); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <template x-if="showAll">
                                                <div>
                                                    <?php $__currentLoopData = $role->permissions->skip(7); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-white">
                                                            <?php echo e($permission->name); ?>

                                                        </span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </template>
                                        </div>
                                        <?php if($role->permissions->count() > 7): ?>
                                            <button @click="showAll = !showAll" class="text-primary text-sm mt-2">
                                                <span x-show="!showAll">+<?php echo e($role->permissions->count() - 7); ?> <?php echo e(__('more')); ?></span>
                                                <span x-show="showAll"><?php echo e(__('Show less')); ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6 text-center flex items-center justify-center gap-1">
                                    <?php if(auth()->user()->can('role.edit')): ?>
                                        <a data-tooltip-target="tooltip-edit-role-<?php echo e($role->id); ?>" class="btn-default !p-3" href="<?php echo e(route('admin.roles.edit', $role->id)); ?>">
                                            <i class="bi bi-pencil text-sm"></i>
                                        </a>
                                        <div id="tooltip-edit-role-<?php echo e($role->id); ?>" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                            <?php echo e(__('Edit Role')); ?>

                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(auth()->user()->can('role.delete')): ?>
                                        <a data-modal-target="delete-modal-<?php echo e($role->id); ?>" data-modal-toggle="delete-modal-<?php echo e($role->id); ?>" data-tooltip-target="tooltip-delete-role-<?php echo e($role->id); ?>" class="btn-danger !p-3" href="javascript:void(0);">
                                            <i class="bi bi-trash text-sm"></i>
                                        </a>
                                        <div id="tooltip-delete-role-<?php echo e($role->id); ?>" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                            <?php echo e(__('Delete Role')); ?>

                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>

                                        <div id="delete-modal-<?php echo e($role->id); ?>" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full z-99999">
                                            <div class="relative p-4 w-full max-w-md max-h-full">
                                                <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                                                    <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-modal-<?php echo e($role->id); ?>">
                                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                                        </svg>
                                                        <span class="sr-only"><?php echo e(__('Close modal')); ?></span>
                                                    </button>
                                                    <div class="p-4 md:p-5 text-center">
                                                        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                        </svg>
                                                        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400"><?php echo e(__('Are you sure you want to delete this role?')); ?></h3>
                                                        <form id="delete-form-<?php echo e($role->id); ?>" action="<?php echo e(route('admin.roles.destroy', $role->id)); ?>" method="POST">
                                                            <?php echo method_field('DELETE'); ?>
                                                            <?php echo csrf_field(); ?>

                                                            <button type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                                                                <?php echo e(__('Yes, Confirm')); ?>

                                                            </button>
                                                            <button data-modal-hide="delete-modal-<?php echo e($role->id); ?>" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"><?php echo e(__('No, cancel')); ?></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td colspan="4" class="px-5 py-4 sm:px-6 text-center">
                                    <span class="text-gray-500 dark:text-gray-400"><?php echo e(__('No roles found')); ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="my-4 px-4 sm:px-6">
                    <?php echo e($roles->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/pages/roles/index.blade.php ENDPATH**/ ?>