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
        $rotateClass = $showSubmenu ? 'rotate-180' : '';
    @endphp

    <li class="menu-item-{{ $item->id }}" style="{!! $item->itemStyles !!}">
        <button :style="`color: ${textColor}`" class="menu-item group w-full text-left {{ $isActive }}" type="button" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.menu-item-arrow').classList.toggle('rotate-180')">
            @if (!empty($item->icon))
                <iconify-icon icon="{{ $item->icon }}" class="menu-item-icon" width="18" height="18"></iconify-icon>
            @elseif (!empty($item->iconClass))
                <iconify-icon icon="lucide:circle" class="menu-item-icon" width="18" height="18"></iconify-icon>
            @endif
            <span class="menu-item-text">{!! $item->label !!}</span>
            <iconify-icon icon="lucide:chevron-down" class="menu-item-arrow transition-transform duration-300 {{ $rotateClass }} w-4 h-4"></iconify-icon>
        </button>
        <ul id="{{ $submenuId }}" class="submenu space-y-1 mt-1 overflow-hidden {{ $showSubmenu ? '' : 'hidden' }}">
            @foreach($item->children as $child)
                @include('backend.layouts.partials.menu-item', ['item' => $child])
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
    {!! ld_apply_filters('sidebar_menu_item_after_' . strtolower($item->id), '') !!}
@endif
