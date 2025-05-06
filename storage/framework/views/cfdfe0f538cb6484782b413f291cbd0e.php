<?php $user = Auth::user(); ?>
<nav
    x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        textColor: '',
        init() {
            this.updateColor();
            const observer = new MutationObserver(() => this.updateColor());
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },
        updateColor() {
            this.isDark = document.documentElement.classList.contains('dark');
            this.textColor = this.isDark 
                ? '<?php echo e(config('settings.sidebar_text_dark')); ?>' 
                : '<?php echo e(config('settings.sidebar_text_lite')); ?>';
        }
    }"
    x-init="init()"
>


    <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
            <?php echo e(__('Menu')); ?>

        </h3>

        <ul class="flex flex-col gap-4 mb-6">
            <?php if($user->can('dashboard.view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.dashboard')); ?>"
                        class="menu-item group <?php echo e(Route::is('admin.dashboard') ? 'menu-item-active' : 'menu-item-inactive'); ?>">
                        <i class="bi bi-grid text-xl text-center"></i>
                        <span :style="`color: ${textColor}`"><?php echo e(__('Dashboard')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php echo ld_apply_filters('sidebar_menu_after_dashboard', '') ?>

            <?php if($user->can('role.create') || $user->can('role.view') || $user->can('role.edit') || $user->can('role.delete')): ?>
                <li>
                    <button
                        class="menu-item group w-full text-left <?php echo e(Route::is('admin.roles.*') ? 'menu-item-active' : 'menu-item-inactive'); ?>"
                        type="button" onclick="toggleSubmenu('roles-submenu')">
                        <i class="bi bi-shield-check text-xl text-center"></i>
                        <span :style="`color: ${textColor}`"> <?php echo e(__('Roles & Permissions')); ?></span>
                        <i class="bi bi-chevron-down ml-auto"></i>
                    </button>
                    <ul id="roles-submenu"
                        class="submenu <?php echo e(Route::is('admin.roles.*') ? '' : 'hidden'); ?> pl-12 mt-2 space-y-2">
                        <?php if($user->can('role.view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.roles.index')); ?>"
                                    class="block px-4 py-2 rounded-lg <?php echo e(Route::is('admin.roles.index') || Route::is('admin.roles.edit') ? 'menu-item-active' : 'menu-item-inactive'); ?>">
                                    <?php echo e(__('Roles')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if($user->can('role.create')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.roles.create')); ?>"
                                    class="block px-4 py-2 rounded-lg <?php echo e(Route::is('admin.roles.create') ? 'menu-item-active' : 'menu-item-inactive'); ?>">
                                    <?php echo e(__('New Role')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <?php echo ld_apply_filters('sidebar_menu_after_roles', '') ?>

            <?php if($user->can('user.create') || $user->can('user.view') || $user->can('user.edit') || $user->can('user.delete')): ?>
                <li>
                    <button
                        class="menu-item group w-full text-left <?php echo e(Route::is('admin.users.*') ? 'menu-item-active' : 'menu-item-inactive'); ?>"
                        type="button" onclick="toggleSubmenu('users-submenu')">
                        <i class="bi bi-person text-xl text-center"></i>
                        <span :style="`color: ${textColor}`"><?php echo e(__('User')); ?></span>
                        <i class="bi bi-chevron-down ml-auto"></i>
                    </button>
                    <ul id="users-submenu"
                        class="submenu <?php echo e(Route::is('admin.users.*') ? '' : 'hidden'); ?> pl-12 mt-2 space-y-2">
                        <?php if($user->can('user.view')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.users.index')); ?>"
                                    class="block px-4 py-2 rounded-lg <?php echo e(Route::is('admin.users.index') || Route::is('admin.users.edit') ? 'menu-item-active' : 'menu-item-inactive'); ?>">
                                    <?php echo e(__('Users')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if($user->can('user.create')): ?>
                            <li>
                                <a href="<?php echo e(route('admin.users.create')); ?>"
                                    class="block px-4 py-2 rounded-lg <?php echo e(Route::is('admin.users.create') ? 'menu-item-active' : 'menu-item-inactive'); ?>">
                                    <?php echo e(__('New User')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <?php echo ld_apply_filters('sidebar_menu_after_users', '') ?>

            <?php if($user->can('module.view')): ?>
                <li>
                    <a href="<?php echo e(route('admin.modules.index')); ?>"
                        class="menu-item group <?php echo e(Route::is('admin.modules.index') ? 'menu-item-active' : 'menu-item-inactive'); ?>">
                        <i class="bi bi-box text-xl text-center"></i>
                        <span :style="`color: ${textColor}`"><?php echo e(__('Modules')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php echo ld_apply_filters('sidebar_menu_after_modules', '') ?>

            <?php if($user->can('pulse.view') || $user->can('actionlog.view')): ?>
                <li>
                    <button
                        class="menu-item group w-full text-left <?php echo e(Route::is('actionlog.*') ? 'menu-item-active' : 'menu-item-inactive'); ?>"
                        type="button" onclick="toggleSubmenu('monitoring-submenu')">
                        <i class="bi bi-activity text-xl text-center"></i>
                        <span :style="`color: ${textColor}`"><?php echo e(__('Monitoring')); ?></span>
                        <i class="bi bi-chevron-down ml-auto"></i>
                    </button>
                    <ul id="monitoring-submenu"
                        class="submenu <?php echo e(Route::is('actionlog.*') ? '' : 'hidden'); ?> pl-12 mt-2 space-y-2">
                        <?php if($user->can('actionlog.view')): ?>
                            <li>
                                <a href="<?php echo e(route('actionlog.index')); ?>"
                                    class="block px-4 py-2 rounded-lg <?php echo e(Route::is('actionlog.index') ? 'menu-item-active' : 'menu-item-inactive text-white'); ?>">
                                    <span :style="`color: ${textColor}`"><?php echo e(__('Action Logs')); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if($user->can('pulse.view')): ?>
                            <li>
                                <a href="<?php echo e(route('pulse')); ?>" class="block px-4 py-2 rounded-lg menu-item-inactive"
                                    target="_blank">
                                    <span :style="`color: ${textColor}`"><?php echo e(__('Laravel Pulse')); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <?php echo ld_apply_filters('sidebar_menu_after_monitoring', '') ?>
        </ul>
    </div>

    <!-- Others Group -->
    <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
            <?php echo e(__('More')); ?>

        </h3>

        <ul class="flex flex-col gap-4 mb-6">
            <?php if($user->can('settings.edit')): ?>
            <li class="menu-item-inactive rounded-md ">
                <a href="<?php echo e(route('admin.settings.index')); ?>" type="submit"
                    class="menu-item group w-full text-left <?php echo e(Route::is('admin.settings.index') ? 'menu-item-active' : 'menu-item-inactive'); ?>">
                    <i class="bi bi-gear text-xl text-center dark:text-white/90"></i>
                    <span class="dark:text-white/90" :style="`color: ${textColor}`"><?php echo e(__('Settings')); ?></span>
                </a>
            </li>
            <?php endif; ?>

            <!-- Logout Menu Item -->
            <li class="menu-item-inactive rounded-md ">
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="menu-item group w-full text-left menu-item-inactive">
                        <i class="bi bi-box-arrow-right text-xl text-center dark:text-white/90"></i>
                        <span class=" dark:text-white/90" :style="`color: ${textColor}`">
                            <?php echo e(__('Logout')); ?>

                        </span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>

<script>
    function toggleSubmenu(submenuId) {
        const submenu = document.getElementById(submenuId);
        submenu.classList.toggle('hidden');
    }
</script>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\resources\views/backend/layouts/partials/sidebar-menu.blade.php ENDPATH**/ ?>