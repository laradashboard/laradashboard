@php
    $menuService = app(\App\Services\MenuService\AdminMenuService::class);
    $menuGroups = $menuService->getMenu();
@endphp

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
        },
        openDrawer(drawerId) {
            if (typeof window.openDrawer === 'function') {
                window.openDrawer(drawerId);
            }
        }
    }"
    x-init="init()"
    class="transition-all duration-300 ease-in-out px-4"
>
    @foreach($menuGroups as $groupName => $groupItems)
        {!! Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU_GROUP_BEFORE->value . Str::slug($groupName), '') !!}
        <div>
            {!! Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU_GROUP_HEADING_BEFORE->value . Str::slug($groupName), '') !!}
            <h3 class="menu-group-heading mb-4 text-xs uppercase leading-[20px] text-gray-500 font-medium dark:text-gray-300 px-5">
                {{ __($groupName) }}
            </h3>
            {!! Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU_GROUP_HEADING_AFTER->value . Str::slug($groupName), '') !!}
            <ul class="flex flex-col mb-6 space-y-1">
                {!! Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU_BEFORE_ALL->value . Str::slug($groupName), '') !!}
                {!! $menuService->render($groupItems) !!}
                {!! Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU_AFTER_ALL->value . Str::slug($groupName), '') !!}
            </ul>
        </div>
        {!! Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU_GROUP_AFTER->value . Str::slug($groupName), '') !!}
    @endforeach
</nav>
