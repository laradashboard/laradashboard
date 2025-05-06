<?php if($errors->any()): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-1 mb-2" role="alert">
        <div>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($error); ?></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" aria-label="Close" onclick="this.parentElement.remove()">
            <span class="text-red-500">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(Session::has('success')): ?>
    <div id="success-message" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative m-1 mb-2 mx-0" role="alert">
        <span><?php echo e(Session::get('success')); ?></span>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" aria-label="Close" onclick="this.parentElement.remove()">
            <span class="text-green-500">&times;</span>
        </button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const successMessage = document.getElementById('success-message');
                if (successMessage) {
                    successMessage.remove();
                }
            }, 2000);
        });
    </script>
<?php endif; ?>

<?php if(Session::has('error')): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-1 mb-2" role="alert">
        <span><?php echo e(Session::get('error')); ?></span>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" aria-label="Close" onclick="this.parentElement.remove()">
            <span class="text-red-500">&times;</span>
        </button>
    </div>
<?php endif; ?><?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/layouts/partials/messages.blade.php ENDPATH**/ ?>