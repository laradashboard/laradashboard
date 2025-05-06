

<?php $__env->startSection('title'); ?>
    <?php echo e(__('Users')); ?> | <?php echo e(config('app.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('admin-content'); ?>

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div x-data="{ pageName: <?php echo e(__('Users')); ?> }">
        <!-- Page Header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName"><?php echo e(__('Users')); ?></h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="<?php echo e(route('admin.dashboard')); ?>">
                            <?php echo e(__('Home')); ?>

                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName"><?php echo e(__('Users')); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Users Table -->
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90"><?php echo e(__('Users')); ?></h3>

                <?php echo $__env->make('backend.partials.search-form', [
                    'placeholder' => __('Search by name or email'),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center">
                        <button id="roleDropdownButton" data-dropdown-toggle="roleDropdown" class="btn-default flex items-center justify-center gap-2" type="button">
                            <i class="bi bi-sliders"></i>
                            <?php echo e(__('Filter by Role')); ?>

                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="roleDropdown" class="z-10 hidden w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                            <ul class="space-y-2">
                                <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded"
                                    onclick="handleRoleFilter('')">
                                    <?php echo e(__('All Roles')); ?>

                                </li>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded <?php echo e($name === request('role') ? 'bg-gray-200 dark:bg-gray-600' : ''); ?>"
                                        onclick="handleRoleFilter('<?php echo e($name); ?>')">
                                        <?php echo e(ucfirst($name)); ?>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>

                    <?php if(auth()->user()->can('user.edit')): ?>
                        <a href="<?php echo e(route('admin.users.create')); ?>" class="btn-primary">
                            <i class="bi bi-plus-circle mr-2"></i>
                            <?php echo e(__('New User')); ?>

                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                <?php echo $__env->make('backend.layouts.partials.messages', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <table id="dataTable" class="w-full dark:text-gray-400">
                    <thead class="bg-light text-capitalize">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th width="5%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5"><?php echo e(__('Sl')); ?></th>
                            <th width="15%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5"><?php echo e(__('Name')); ?></th>
                            <th width="10%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5"><?php echo e(__('Email')); ?></th>
                            <th width="30%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5"><?php echo e(__('Roles')); ?></th>
                            <?php ld_apply_filters('user_list_page_table_header_before_action', '') ?>
                            <th width="15%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5"><?php echo e(__('Action')); ?></th>
                            <?php ld_apply_filters('user_list_page_table_header_after_action', '') ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="<?php echo e($loop->index + 1 != count($users) ?  'border-b border-gray-100 dark:border-gray-800' : ''); ?>">
                                <td class="px-5 py-4 sm:px-6"><?php echo e($loop->index + 1); ?></td>
                                <td class="px-5 py-4 sm:px-6 flex items-center md:min-w-[200px]">
                                    <a data-tooltip-target="tooltip-user-<?php echo e($user->id); ?>" href="<?php echo e(auth()->user()->canBeModified($user) ? route('admin.users.edit', $user->id) : '#'); ?>" class="flex items-center">
                                        <img src="<?php echo e(ld_apply_filters('user_list_page_avatar_item', $user->getGravatarUrl(40), $user)); ?>" alt="<?php echo e($user->name); ?>" class="w-10 h-10 rounded-full mr-3">
                                        <?php echo e($user->name); ?>

                                    </a>
                                    <?php if(auth()->user()->canBeModified($user)): ?>
                                    <div id="tooltip-user-<?php echo e($user->id); ?>" href="<?php echo e(route('admin.users.edit', $user->id)); ?>" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                        <?php echo e(__('Edit User')); ?>

                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 sm:px-6"><?php echo e($user->email); ?></td>
                                <td class="px-5 py-4 sm:px-6">
                                    <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="capitalize inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-white">
                                            <?php echo e($role->name); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </td>
                                
                                <?php ld_apply_filters('user_list_page_table_row_before_action', '', $user) ?>

                                <td class="flex px-5 py-4 sm:px-6 text-center gap-1">
                                    <?php if(auth()->user()->canBeModified($user) && !($user->hasRole('superadmin') && config('app.demo_mode') == true)): ?>
                                        <a data-tooltip-target="tooltip-edit-user-<?php echo e($user->id); ?>" class="btn-default !p-3" href="<?php echo e(route('admin.users.edit', $user->id)); ?>">
                                            <i class="bi bi-pencil text-sm"></i>
                                        </a>
                                        <div id="tooltip-edit-user-<?php echo e($user->id); ?>" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                            <?php echo e(__('Edit User')); ?>

                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(auth()->user()->canBeModified($user, 'user.delete') && !($user->hasRole('superadmin') && config('app.demo_mode') == true)): ?>
                                        <a data-modal-target="delete-modal-<?php echo e($user->id); ?>" data-modal-toggle="delete-modal-<?php echo e($user->id); ?>" data-tooltip-target="tooltip-delete-user-<?php echo e($user->id); ?>" class="btn-danger !p-3" href="javascript:void(0);">
                                            <i class="bi bi-trash text-sm"></i>
                                        </a>
                                        <div id="tooltip-delete-user-<?php echo e($user->id); ?>" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                            <?php echo e(__('Delete User')); ?>

                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>

                                        <div id="delete-modal-<?php echo e($user->id); ?>" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                                            <!-- Modal Content -->
                                            <div class="relative p-4 w-full max-w-md bg-white rounded-lg shadow-lg dark:bg-gray-700 z-60">
                                                <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-modal-<?php echo e($user->id); ?>">
                                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                                    </svg>
                                                    <span class="sr-only"><?php echo e(__('Close modal')); ?></span>
                                                </button>
                                                <div class="p-4 md:p-5 text-center">
                                                    <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                    </svg>
                                                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400"><?php echo e(__('Are you sure you want to delete this user?')); ?></h3>
                                                    <form id="delete-form-<?php echo e($user->id); ?>" action="<?php echo e(route('admin.users.destroy', $user->id)); ?>" method="POST">
                                                        <?php echo method_field('DELETE'); ?>
                                                        <?php echo csrf_field(); ?>

                                                        <button type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                                                            <?php echo e(__('Yes, Confirm')); ?>

                                                        </button>
                                                        <button data-modal-hide="delete-modal-<?php echo e($user->id); ?>" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"><?php echo e(__('No, cancel')); ?></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(auth()->user()->can('user.login_as') && $user->id != auth()->user()->id): ?>
                                        <a data-tooltip-target="tooltip-login-as-user-<?php echo e($user->id); ?>" class="btn-warning !p-3" href="<?php echo e(route('admin.users.login-as', $user->id)); ?>">
                                            <i class="bi bi-box-arrow-in-right text-sm"></i>
                                        </a>
                                        <div id="tooltip-login-as-user-<?php echo e($user->id); ?>" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                            <?php echo e(__('Login as')); ?> <?php echo e($user->name); ?>

                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php ld_apply_filters('user_list_page_table_row_after_action', '', $user) ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-gray-500 dark:text-gray-400"><?php echo e(__('No users found')); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="my-4 px-4 sm:px-6">
                    <?php echo e($users->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
    <script>
        function handleRoleFilter(value) {
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('role', value);
            window.location.href = currentUrl.toString();
        }
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/pages/users/index.blade.php ENDPATH**/ ?>