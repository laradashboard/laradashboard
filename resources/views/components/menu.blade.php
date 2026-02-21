@props([
    'location' => 'primary',
    'depth' => null,
    'class' => '',
    'itemClass' => '',
    'linkClass' => '',
    'activeClass' => 'active',
    'showIcons' => true,
])

@if($hasItems())
    <nav {{ $attributes->merge(['class' => $class]) }}>
        <ul class="menu-list">
            @include('components.menu-items', [
                'items' => $depth !== null ? $filterByDepth($items) : $items,
                'depth' => 0,
                'itemClass' => $itemClass,
                'linkClass' => $linkClass,
                'activeClass' => $activeClass,
                'showIcons' => $showIcons,
                'maxDepth' => $depth,
            ])
        </ul>
    </nav>
@endif
