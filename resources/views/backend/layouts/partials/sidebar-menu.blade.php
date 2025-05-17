@php
    $menuService = app(\App\Services\MenuService\SidebarMenuService::class);
    $menuGroups = $menuService->getMenu();
@endphp

<nav
    x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        textColor: '',
        submenus: {},
        toggleSubmenu(id) {
            this.submenus[id] = !this.submenus[id];
        },
        init() {
            this.updateColor();
            const observer = new MutationObserver(() => this.updateColor());
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },
        updateColor() {
            this.isDark = document.documentElement.classList.contains('dark');
            this.textColor = this.isDark 
                ? '{{ config('settings.sidebar_text_dark') }}' 
                : '{{ config('settings.sidebar_text_lite') }}';
        }
    }"
    x-init="init()"
    class="transition-all duration-300 ease-in-out"
>
    @foreach($menuGroups as $groupName => $groupItems)
        {!! ld_apply_filters('sidebar_menu_group_before_' . strtolower($groupName), '') !!}
        <div>
            {!! ld_apply_filters('sidebar_menu_group_heading_before_' . strtolower($groupName), '') !!}
            <h3 class="mb-4 text-xs leading-[20px] text-gray-400 px-5">
                {{ __($groupName) }}
            </h3>
            {!! ld_apply_filters('sidebar_menu_group_heading_after_' . strtolower($groupName), '') !!}
            <ul class="flex flex-col mb-6">
                {!! ld_apply_filters('sidebar_menu_before_all_' . strtolower($groupName), '') !!}
                {!! $menuService->render($groupItems) !!}
          
                {!! ld_apply_filters('sidebar_menu_after_all_' . strtolower($groupName), '') !!}
            </ul>
        </div>
        {!! ld_apply_filters('sidebar_menu_group_after_' . strtolower($groupName), '') !!}
    @endforeach
</nav>
