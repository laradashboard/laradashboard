<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $__env->yieldContent('title', config('app.name')); ?></title>

    <link rel="icon" href="<?php echo e(config('settings.site_favicon') ?? asset('favicon.ico')); ?>" type="image/x-icon">
    <?php echo $__env->yieldContent('before_vite_build'); ?>

    <?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js', 'resources/css/app.css'], 'build'); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <?php echo $__env->yieldContent('before_head'); ?>

    <?php echo ld_apply_filters('admin_head', ''); ?>
</head>

<body x-data="{ page: 'ecommerce', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode'));
$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" :class="{ 'dark bg-gray-900': darkMode === true }">
    <?php if(!empty(config('settings.google_tag_manager_script'))): ?>
        <?php echo config('settings.google_tag_manager_script'); ?>

    <?php endif; ?>
    <?php if(!empty(config('settings.google_analytics_script'))): ?>
        <?php echo config('settings.google_analytics_script'); ?>

    <?php endif; ?>
    <!-- Preloader -->
    <div x-show="loaded" x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 500) })"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
        </div>
    </div>
    <!-- End Preloader -->
    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <?php echo $__env->make('backend.layouts.partials.sidebar-logo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- Content Area -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Small Device Overlay -->
            <div @click="sidebarToggle = false" :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                class="fixed w-full h-screen z-9 bg-gray-900/50"></div>
            <!-- End Small Device Overlay -->

            <?php echo $__env->make('backend.layouts.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <!-- Main Content -->
            <main>
                <?php echo $__env->yieldContent('admin-content'); ?>
            </main>
            <!-- End Main Content -->
        </div>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const html = document.documentElement;
            const darkModeToggle = document.getElementById('darkModeToggle');
            const header = document.getElementById('appHeader');


            // Update header background based on current mode
            function updateHeaderBg() {
                if (!header) return;
                const isDark = html.classList.contains('dark');
            }

            // nitialize dark mode
            const savedDarkMode = localStorage.getItem('darkMode');
            if (savedDarkMode === 'true') {
                html.classList.add('dark');
            } else if (savedDarkMode === 'false') {
                html.classList.remove('dark');
            } else {
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    html.classList.add('dark');
                }
            }

            updateHeaderBg();

            const observer = new MutationObserver(updateHeaderBg);
            observer.observe(html, { attributes: true, attributeFilter: ['class'] });

            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    const isDark = html.classList.toggle('dark');
                    localStorage.setItem('darkMode', isDark);
                    updateHeaderBg();
                });
            }
        });
    </script>



</body>

</html>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/layouts/app.blade.php ENDPATH**/ ?>