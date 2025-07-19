@php
    $breadcrumbs = $breadcrumbs ?? [];
@endphp

@props([
    'disabled' => $breadcrumbs['disabled'] ?? false,
    'title' => $breadcrumbs['title'] ?? '',
    'items' => $breadcrumbs['items'] ?? [],
    'show_home' => $breadcrumbs['show_home'] ?? true,
    'show_current' => $breadcrumbs['show_current'] ?? true,
    'title_after' => $breadcrumbs['title_after'] ?? '',
    'show_messages_after' => $breadcrumbs['show_messages_after'] ?? true,
])

@if (!$disabled)
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    @if(!empty($title))
    <h2 class="text-xl font-semibold text-gray-700 dark:text-white/90">
        {{ __($title) }}

        {!! $title_after !!}
    </h2>
    @endif

    @if(count($items) || ($show_home || $show_current))
    <nav>
        <ol class="flex items-center gap-1.5 pe-2">
            @if($show_home)
                <li>
                    <a
                        class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                        href="{{ route('admin.dashboard') }}"
                    >
                        {{ __("Home") }}
                        <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                    </a>
                </li>
            @endif

            @foreach($items as $item)
                <li>
                    <a
                        class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                        href="{{ $item['url'] }}"
                    >
                        {{ __($item['label']) }}
                        <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                    </a>
                </li>
            @endforeach

            @if($show_current)
                <li class="text-sm text-gray-700 dark:text-white/90">
                    {{ __($title) }}
                </li>
            @endif
        </ol>
    </nav>
    @endif
</div>
@endif

@if($show_messages_after)
    <x-messages />
@endif
