<header id="appHeader"

x-data="{
    menuToggle: false,
    textColor: '',
    isDark: document.documentElement.classList.contains('dark'),
    init() {
        this.updateBg();
        this.updateColor();
        const observer = new MutationObserver(() => {
            this.updateBg();
            this.updateColor();
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    },
    updateBg() {
        this.isDark = document.documentElement.classList.contains('dark');
        const liteBg = '{{ config('settings.navbar_bg_lite') }}';
        const darkBg = '{{ config('settings.navbar_bg_dark') }}';
        this.$el.style.backgroundColor = this.isDark ? darkBg : liteBg;
    },
    updateColor() {
        this.isDark = document.documentElement.classList.contains('dark');
        this.textColor = this.isDark
            ? '{{ config('settings.navbar_text_dark') }}'
            : '{{ config('settings.navbar_text_lite') }}';
    }
}"
x-init="init()"
    class="sticky top-0 flex w-full border-gray-200 lg:border-b dark:border-gray-800 transition-all duration-300 z-9">
    <div class="flex grow flex-col items-center justify-between lg:flex-row lg:px-6">
        <div
            class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-2 sm:gap-4 lg:justify-normal lg:border-b-0 lg:px-0 dark:border-gray-800">
            <button
                :class="sidebarToggle ? 'lg:bg-transparent dark:lg:bg-transparent bg-gray-100 dark:bg-gray-800' : ''"
                class="z-99999 flex h-10 w-10 items-center justify-center rounded-md border-gray-200 text-gray-700 lg:h-11 lg:w-11 dark:border-gray-800 dark:text-gray-300 transition-all duration-300"
                id="sidebar-toggle-button"
                @click.stop="sidebarToggle = !sidebarToggle; localStorage.setItem('sidebarToggle', sidebarToggle);">
                <iconify-icon
                    :icon="sidebarToggle ? 'mdi:menu-close' : 'mdi:menu-open'"
                    width="26" height="26" class="hidden md:inline-block"></iconify-icon>
                <iconify-icon
                    :icon="sidebarToggle ? 'feather:menu' : 'feather:menu'"
                    width="26" height="26" class="md:hidden"></iconify-icon>
            </button>

            <div class="flex gap-2 lg:hidden">
                @include('backend.layouts.partials.header.right-menu')
            </div>
        </div>

        <!-- Adjusted Mobile Menu -->
        <div :class="menuToggle ? 'flex' : 'hidden'"
            class="hidden w-full items-center justify-between gap-4 px-5 py-1 shadow-theme-md lg:flex lg:justify-end lg:px-0 lg:shadow-none">
            @include('backend.layouts.partials.header.right-menu')
        </div>
    </div>
</header>
<!-- End Header -->
