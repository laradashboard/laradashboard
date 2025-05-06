

<?php $__env->startSection('title'); ?>
    <?php echo e(__('Edit Role')); ?> | <?php echo e(config('app.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('admin-content'); ?>

<div class="p-4 mx-auto max-w-[var(--breakpoint-2xl)] md:p-6">
    <div x-data="{ pageName: `<?php echo e(__('Edit Role')); ?>`}">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2
                class="text-xl font-semibold text-gray-800 dark:text-white/90"
                x-text="pageName"
            >
<div class="p-6 mx-auto max-w-7xl">
    <div x-data="{ pageName: `<?php echo e(__('Edit Role')); ?>`}">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white" x-text="pageName">
                <?php echo e(__('Edit Role')); ?>

            </h2>

            <nav>
                <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="hover:text-gray-800 dark:hover:text-white">
                            <?php echo e(__('Home')); ?>

                        </a>
                        <i class="bi bi-chevron-right"></i>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.roles.index')); ?>" class="hover:text-gray-800 dark:hover:text-white">
                            <?php echo e(__('Roles')); ?>

                        </a>
                        <i class="bi bi-chevron-right"></i>
                    </li>
                    <li
                        class="text-sm text-gray-800 dark:text-white/90"
                        x-text="pageName"
                    >
                        <?php echo e(__('Edit Role')); ?>

                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="space-y-8">
        <!-- Role Details Section -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    <?php echo e(__('Role Details')); ?>

                </h3>
            </div>
            <div class="p-4">
                <form action="<?php echo e(route('admin.roles.update', $role->id)); ?>" method="POST">
                    <?php echo method_field('PUT'); ?>
                    <?php echo csrf_field(); ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                <?php echo e(__('Role Name')); ?>

                            </label>
                            <input required autofocus name="name" value="<?php echo e($role->name); ?>" type="text" placeholder="<?php echo e(__('Enter a Role Name')); ?>" class="mt-2 form-control">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Permissions Section -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    <?php echo e(__('Permissions')); ?>

                </h3>
            </div>
            <div class="p-4">
                <div class="mb-4">
                    <input type="checkbox" id="checkPermissionAll" class="mr-2" <?php echo e(App\Models\User::roleHasPermissions($role, $all_permissions) ? 'checked' : ''); ?>>
                    <label for="checkPermissionAll" class="text-sm text-gray-700 dark:text-gray-400">
                        <?php echo e(__('Select All')); ?>

                    </label>
                </div>
                <hr class="mb-6">
                <?php $i = 1; ?>
                <?php $__currentLoopData = $permission_groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <div class="mb-6">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="<?php echo e($i); ?>Management" class="mr-2" <?php echo e(App\Models\User::roleHasPermissions($role, App\Models\User::getpermissionsByGroupName($group->name)) ? 'checked' : ''); ?>>
                        <label for="<?php echo e($i); ?>Management" class="capitalize text-sm font-medium text-gray-700 dark:text-gray-400">
                            <?php echo e(ucfirst($group->name)); ?>

                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                        <?php
                            $permissions = App\Models\User::getpermissionsByGroupName($group->name);
                        ?>
                        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <input type="checkbox" id="checkPermission<?php echo e($permission->id); ?>" name="permissions[]" value="<?php echo e($permission->name); ?>" class="mr-2" <?php echo e($role->hasPermissionTo($permission->name) ? 'checked' : ''); ?>>
                            <label for="checkPermission<?php echo e($permission->id); ?>" class="capitalize text-sm text-gray-700 dark:text-gray-400">
                                <?php echo e($permission->name); ?>

                            </label>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php $i++; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-start gap-4">
            <button type="submit" class="btn-primary">
                <?php echo e(__('Save')); ?>

            </button>
            <a href="<?php echo e(route('admin.roles.index')); ?>" class="btn-default">
                <?php echo e(__('Cancel')); ?>

            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo $__env->make('backend.pages.roles.partials.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('backend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/pages/roles/edit.blade.php ENDPATH**/ ?>