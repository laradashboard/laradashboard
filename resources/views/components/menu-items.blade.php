@foreach($items as $item)
    @php
        $hasChildren = $item->hasChildren();
        $isActive = $item->isActive();
        $itemClasses = collect([
            'menu-item',
            $itemClass,
            $hasChildren ? 'has-children' : '',
            $isActive ? $activeClass : '',
            $item->css_classes ?? '',
        ])->filter()->implode(' ');
        $linkClasses = collect([
            'menu-link',
            $linkClass,
            $isActive ? $activeClass : '',
        ])->filter()->implode(' ');
    @endphp

    <li class="{{ $itemClasses }}" data-menu-item-id="{{ $item->id }}">
        <a
            href="{{ $item->getUrl() }}"
            class="{{ $linkClasses }}"
            @if($item->target_blank) target="_blank" rel="noopener noreferrer" @endif
        >
            @if($showIcons && $item->icon)
                <iconify-icon icon="{{ $item->icon }}" class="menu-icon"></iconify-icon>
            @endif
            <span class="menu-label">{{ $item->label }}</span>
            @if($hasChildren)
                <iconify-icon icon="lucide:chevron-down" class="menu-arrow"></iconify-icon>
            @endif
        </a>

        @if($hasChildren && ($maxDepth === null || $depth + 1 < $maxDepth))
            <ul class="sub-menu">
                @include('components.menu-items', [
                    'items' => $item->getChildren(),
                    'depth' => $depth + 1,
                    'itemClass' => $itemClass,
                    'linkClass' => $linkClass,
                    'activeClass' => $activeClass,
                    'showIcons' => $showIcons,
                    'maxDepth' => $maxDepth,
                ])
            </ul>
        @endif
    </li>
@endforeach
