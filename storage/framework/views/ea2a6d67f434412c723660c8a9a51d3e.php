<aside
    :class="sidebarToggle ? 'translate-x-0 lg:w-[85px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-10 flex h-screen w-[290px] flex-col overflow-y-hidden border-r <?php echo e(config('settings.sidebar_bg_lite') ? '' : 'bg-gray-800'); ?> px-5 border-gray-800 dark:bg-gray-900 lg:static lg:translate-x-0"
    id="appSidebar"
    x-data="{
        init() {
            this.updateBg();
            const observer = new MutationObserver(() => this.updateBg());
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },
        updateBg() {
            const htmlHasDark = document.documentElement.classList.contains('dark');
            const liteBg = '<?php echo e(config('settings.sidebar_bg_lite')); ?>';
            const darkBg = '<?php echo e(config('settings.sidebar_bg_dark')); ?>';
            this.$el.style.backgroundColor = htmlHasDark ? darkBg : liteBg;
        }
    }"
    x-init="init()"
>

    <!-- Sidebar Header -->
    <div
        :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="justify-center flex items-center gap-2 sidebar-header py-6"
    >
        <a href="<?php echo e(route('admin.dashboard')); ?>">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img
                    class="dark:hidden"
                    src="<?php echo e(config('settings.site_logo_lite') ?? '/images/logo/lara-dashboard-dark.png'); ?>"
                    alt="Logo"
                />
                <img
                    class="hidden dark:block"
                    src="<?php echo e(config('settings.site_logo_dark') ?? '/images/logo/lara-dashboard-dark.png'); ?>"
                    alt="Logo"
                />
            </span>
            <img
                class="logo-icon w-20 lg:w-12"
                :class="sidebarToggle ? 'lg:block' : 'hidden'"
                src="<?php echo e(config('settings.site_icon') ?? '/images/logo/icon.png'); ?>"
                alt="Logo"
            />
        </a>
    </div>
    <!-- End Sidebar Header -->

    <div
        class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar"
    >
        <?php echo $__env->make('backend.layouts.partials.sidebar-menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</aside>
<!-- End Sidebar -->
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/layouts/partials/sidebar-logo.blade.php ENDPATH**/ ?>