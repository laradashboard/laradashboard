

<?php $__env->startSection('title'); ?>
    <?php echo e(__('Modules')); ?> - <?php echo e(config('settings.app_name') !== '' ? config('settings.app_name') : config('app.name')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('admin-content'); ?>

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <?php echo $__env->make('backend.layouts.partials.messages', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div x-data="{ pageName: '<?php echo e(__('Modules')); ?>', showUploadArea: false }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                <?php echo e(__('Modules')); ?>


                <?php if(count($modules) > 0): ?>
                    <button
                        @click="showUploadArea = !showUploadArea"
                        class="ml-4 px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:opacity-90 transition-all btn-upload-module"
                    >
                        <i class="bi bi-cloud-upload mr-2"></i>
                        <?php echo e(__('Upload Module')); ?>

                    </button>
                <?php endif; ?>
            </h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="<?php echo e(route('admin.dashboard')); ?>">
                            <?php echo e(__('Home')); ?>

                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName"><?php echo e(__('Modules')); ?></li>
                </ol>
            </nav>
        </div>

        <div x-show="showUploadArea" class="mb-6 p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600"
             @dragover.prevent
             @drop.prevent="$refs.uploadModule.files = $event.dataTransfer.files; $refs.uploadModule.dispatchEvent(new Event('change'))">
            <p class="text-center text-gray-600 dark:text-gray-400">
                <?php echo e(__('Drag and drop your module file here, or')); ?>

                <button
                    @click="$refs.uploadModule.click()"
                    class="text-primary underline hover:text-blue-600"
                >
                    <?php echo e(__('browse')); ?>

                </button>
                <?php echo e(__('to select a file.')); ?>

            </p>
            <form action="<?php echo e(route('admin.modules.upload')); ?>" method="POST" enctype="multipart/form-data" class="hidden">
                <?php echo csrf_field(); ?>
                <input type="file" name="module" accept=".zip" x-ref="uploadModule" @change="$event.target.form.submit()">
            </form>
        </div>
    </div>

    <?php if(empty($modules)): ?>
    <div class="flex flex-col items-center justify-center h-64 bg-gray-100 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300"
            @dragover.prevent
            @drop.prevent="$refs.uploadModule.files = $event.dataTransfer.files; $refs.uploadModule.dispatchEvent(new Event('change'))">
        <svg class="w-16 h-16 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <p class="mt-4 text-gray-600 dark:text-gray-400"><?php echo e(__('Drag and drop your module file here, or')); ?></p>
        <button
            @click="$refs.uploadModule.click()"
            class="mt-4 px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-blue-600"
        >
            <i class="bi bi-cloud-upload mr-2"></i>
            <?php echo e(__('Upload')); ?>

        </button>
        <form action="<?php echo e(route('admin.modules.upload')); ?>" method="POST" enctype="multipart/form-data" class="hidden">
            <?php echo csrf_field(); ?>
            <input type="file" name="module" accept=".zip" x-ref="uploadModule" @change="$event.target.form.submit()">
        </form>
    </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex justify-between">
                        <div class="py-3">
                            <h2>
                                <i class="bi <?php echo e($module['icon']); ?> text-3xl text-gray-500 dark:text-gray-400"></i>
                            </h2>
                            <h3 class="text-lg font-medium text-gray-800 dark:text-white">
                                <?php echo e($module['title']); ?>

                            </h3>
                        </div>

                        <button id="dropdownMenuIconButton" data-dropdown-toggle="dropdownMore-<?php echo e($module['name']); ?>" class="inline-flex items-right h-9 p-2 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-600" type="button">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>

                        <div id="dropdownMore-<?php echo e($module['name']); ?>" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownMenuIconButton">
                                <li>
                                    <button
                                        x-data="{ showDeleteModal: false }"
                                        data-modal-target="delete-modal-<?php echo e($module['name']); ?>" data-modal-toggle="delete-modal-<?php echo e($module['name']); ?>"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full px-2 text-left"
                                    >
                                        <?php echo e(__('Delete')); ?>

                                    </button>
                                </li>
                                <li>
                                    <button
                                        onclick="toggleModuleStatus('<?php echo e($module['name']); ?>', event)"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full px-2 text-left"
                                    >
                                        <?php echo e($module['status'] ? __('Disable') : __('Enable')); ?>

                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($module['description']); ?></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <?php echo e(__('Tags:')); ?>

                        <?php $__currentLoopData = $module['tags']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="inline-block px-2 py-1 text-xs font-medium text-white bg-gray-400 rounded-full mr-1 mb-1"><?php echo e($tag); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </p>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm font-medium <?php echo e($module['status'] ? 'text-green-500' : 'text-red-500'); ?>">
                            <?php echo e($module['status'] ? __('Enabled') : __('Disabled')); ?>

                        </span>
                    </div>
                </div>

                <!-- Delete modal -->
                <div id="delete-modal-<?php echo e($module['name']); ?>" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full z-99999">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-900 dark:hover:text-white" data-modal-hide="delete-modal-<?php echo e($module['name']); ?>">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <div class="p-6 text-center">
                            <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                                <?php echo e(__('Are you sure you want to delete this module?')); ?>

                            </h3>
                            <form x-ref="deleteForm<?php echo e($module['name']); ?>" action="<?php echo e(route('admin.modules.delete', $module['name'])); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                    <?php echo e(__('Yes, Confirm')); ?>

                                </button>
                                <button type="button" class="py-2.5 px-5 ml-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700" data-modal-hide="delete-modal-<?php echo e($module['name']); ?>">
                                    <?php echo e(__('No, Cancel')); ?>

                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleModuleStatus(moduleName, event) {
        fetch(`/admin/modules/toggle-status/${moduleName}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const button = event.target;
                button.textContent = data.status ? '<?php echo e(__("Disable")); ?>' : '<?php echo e(__("Enable")); ?>';
                button.classList.toggle('bg-green-500', data.status);
                button.classList.toggle('bg-red-500', !data.status);
            } else {
                alert(data.message);
            }
        });
    }
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/pages/modules/index.blade.php ENDPATH**/ ?>