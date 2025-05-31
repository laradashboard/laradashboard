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
        $isActive = $item->active ? 'menu-item-active' : 'menu-item-inactive';
        $showSubmenu = app(\App\Services\MenuService\AdminMenuService::class)->shouldExpandSubmenu($item);
        $rotateClass = $showSubmenu ? 'rotate-180' : '';
    @endphp

    <li class="hover:menu-item-active menu-item-{{ $item->id }}" style="{!! $item->itemStyles !!}">
        <button :style="`color: ${textColor}`" class="menu-item group w-full text-left {{ $isActive }}" type="button" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.menu-item-arrow').classList.toggle('rotate-180')">
            @if (!empty($item->icon))
                <img src="{{ asset('images/icons/' . $item->icon) }}" alt="{!! $item->label !!}" class="menu-item-icon dark:invert w-5">
            @elseif (!empty($item->iconClass))
                <i class="{{ $item->iconClass }} menu-item-icon"></i>
            @endif
            <span class="menu-item-text">{!! $item->label !!}</span>
            <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300 {{ $rotateClass }}">
        </button>
        <ul id="{{ $submenuId }}" class="submenu pl-12 mt-2 overflow-hidden {{ $showSubmenu ? '' : 'hidden' }}">
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

    <li class="hover:menu-item-active menu-item-{{ $item->id }}" style="{!! $item->itemStyles !!}">
        <a :style="`color: ${textColor}`" href="{{ $item->route ?? '#' }}" class="menu-item group {{ $isActive }}" {!! $target !!}>
            @if (!empty($item->icon))
                <img src="{{ asset('images/icons/' . $item->icon) }}" alt="{!! $item->label !!}" class="menu-item-icon dark:invert">
            @elseif (!empty($item->iconClass))
                <i class="{{ $item->iconClass }} menu-item-icon"></i>
            @endif
            <span class="menu-item-text">{!! $item->label !!}</span>
        </a>
    </li>
@endif

@if(isset($item->id))
    {!! ld_apply_filters('sidebar_menu_item_after_' . strtolower($item->id), '') !!}
@endif
