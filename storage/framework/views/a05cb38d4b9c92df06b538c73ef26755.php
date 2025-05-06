

<?php $__env->startSection('title'); ?>
    <?php echo e(__('User Edit')); ?> - <?php echo e(config('app.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('admin-content'); ?>

<div class="p-4 mx-auto max-w-7xl md:p-6">
    <div x-data="{ pageName: '<?php echo e(__('Edit User')); ?>' }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white" x-text="pageName"><?php echo e(__('Edit User')); ?></h2>
            <nav>
                <ol class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                    <li>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="inline-flex items-center gap-1.5">
                            <?php echo e(__('Home')); ?>

                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.users.index')); ?>" class="inline-flex items-center gap-1.5">
                            <?php echo e(__('Users')); ?>

                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-gray-800 dark:text-white" x-text="pageName">
                        <?php echo e(__('Edit User')); ?>

                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="px-5 py-2.5 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white"><?php echo e(__('Edit User')); ?> - <?php echo e($user->name); ?></h3>
            </div>
            <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                <?php echo $__env->make('backend.layouts.partials.messages', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <form action="<?php echo e(route('admin.users.update', $user->id)); ?>" method="POST" class="space-y-6" enctype="multipart/form-data">
                    <?php echo method_field('PUT'); ?>
                    <?php echo csrf_field(); ?>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-400"><?php echo e(__('Full Name')); ?></label>
                            <input type="text" name="name" id="name" required value="<?php echo e($user->name); ?>" placeholder="Enter Full Name" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-400"><?php echo e(__('User Email')); ?></label>
                            <input type="email" name="email" id="email" required value="<?php echo e($user->email); ?>" placeholder="Enter Email" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-400"><?php echo e(__('Password (Optional)')); ?></label>
                            <input type="password" name="password" id="password" placeholder="Enter Password" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-400"><?php echo e(__('Confirm Password (Optional)')); ?></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        <div>
                            <label for="roles" class="block text-sm font-medium text-gray-700 dark:text-gray-400"><?php echo e(__('Assign Roles')); ?></label>
                            <div class="space-y-2">
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="roles[]" id="role_<?php echo e($id); ?>" value="<?php echo e($name); ?>" <?php echo e($user->roles->pluck('id')->contains($id) ? 'checked' : ''); ?> class="h-4 w-4 text-brand-500 border-gray-300 rounded focus:ring-brand-400 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-brand-500">
                                        <label for="role_<?php echo e($id); ?>" class="ml-2 text-sm text-gray-700 dark:text-gray-400"><?php echo e(ucfirst($name)); ?></label>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-400"><?php echo e(__('Username')); ?></label>

                            <input type="text" name="username" id="username" required value="<?php echo e($user->username); ?>" placeholder="Enter Username" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        <?php echo ld_apply_filters('after_username_field', '', $user); ?>

                    </div>
                    <div class="mt-6 flex justify-start gap-4">
                        <button type="submit" class="btn-primary"><?php echo e(__('Save')); ?></button>
                        <a href="<?php echo e(route('admin.users.index')); ?>" class="btn-default"><?php echo e(__('Cancel')); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/pages/users/edit.blade.php ENDPATH**/ ?>