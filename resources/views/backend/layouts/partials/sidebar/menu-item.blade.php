@php
    /** @var \App\Services\MenuService\AdminMenuItem $item */
@endphp

@if (isset($item->htmlData))
    <div class="menu-item-html" style="{!! $item->itemStyles !!}">
        {!! $item->htmlData !!}
    </div>
@elseif (!empty($item->children))
    @php
        $submenuId = $item->id ?? \Str::slug($item->label) . '-submenu';
        $isActive = $item->active ? 'menu-item-active' : '';
        $showSubmenu = app(\App\Services\MenuService\AdminMenuService::class)->shouldExpandSubmenu($item);
        $chevronIcon = $showSubmenu ? 'lucide:chevron-up' : 'lucide:chevron-right';
        $firstChildRoute = !empty($item->children) && isset($item->children[0]->route) ? $item->children[0]->route : null;
        
        // Check if current URL matches any child route to prevent unnecessary redirects.
        $currentUrl = request()->url();
        $isOnChildPage = false;
        if (!empty($item->children)) {
            foreach ($item->children as $child) {
                if (isset($child->route) && $child->route === $currentUrl) {
                    $isOnChildPage = true;
                    break;
                }
            }
        }
    @endphp

    <li class="menu-item-{{ $item->id }}" style="{!! $item->itemStyles !!}">
        <button :style="`color: ${textColor}`" class="menu-item group w-full text-left {{ $isActive }}" type="button" 
                onclick="handleMenuItemClick(this, '{{ $submenuId }}', '{{ $firstChildRoute }}', {{ $showSubmenu ? 'true' : 'false' }}, {{ $isOnChildPage ? 'true' : 'false' }})">
            @if (!empty($item->icon))
                <iconify-icon icon="{{ $item->icon }}" class="menu-item-icon" width="18" height="18"></iconify-icon>
            @elseif (!empty($item->iconClass))
                <iconify-icon icon="lucide:circle" class="menu-item-icon" width="18" height="18"></iconify-icon>
            @endif
            <span class="menu-item-text">{!! $item->label !!}</span>
            <iconify-icon icon="{{ $chevronIcon }}" class="menu-item-arrow transition-all duration-300 w-4 h-4" style="transform: {{ $showSubmenu ? 'rotate(180deg)' : 'rotate(0deg)' }}"></iconify-icon>
        </button>
        <ul id="{{ $submenuId }}" class="submenu space-y-1 mt-1 overflow-hidden {{ $showSubmenu ? 'submenu-expanded' : 'submenu-collapsed' }}">
            @foreach($item->children as $child)
                @include('backend.layouts.partials.sidebar.menu-item', ['item' => $child])
            @endforeach
        </ul>
    </li>
@else
    @php
        $isActive = $item->active ? 'menu-item-active' : 'menu-item-inactive';
        $target = !empty($item->target) ? ' target="' . e($item->target) . '"' : '';
    @endphp

    <li class="menu-item-{{ $item->id }}" style="{!! $item->itemStyles !!}">
        <a :style="`color: ${textColor}`" href="{{ $item->route ?? '#' }}" class="menu-item group {{ $isActive }}" {!! $target !!}>
            @if (!empty($item->icon))
                <iconify-icon icon="{{ $item->icon }}" class="menu-item-icon" width="18" height="18"></iconify-icon>
            @elseif (!empty($item->iconClass))
                <iconify-icon icon="lucide:circle" class="menu-item-icon" width="18" height="18"></iconify-icon>
            @endif
            <span class="menu-item-text">{!! $item->label !!}</span>
        </a>
    </li>
@endif

@if(isset($item->id))
    {!! Hook::applyFilters(AdminFilterHook::SIDEBAR_MENU_ITEM_AFTER->value . strtolower($item->id), '') !!}
@endif
