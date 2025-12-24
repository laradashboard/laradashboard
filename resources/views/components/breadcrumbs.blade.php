@php
    $breadcrumbs = $breadcrumbs ?? [];
@endphp

@props([
    'disabled' => $breadcrumbs['disabled'] ?? false,
    'title' => $breadcrumbs['title'] ?? '',
    'items' => $breadcrumbs['items'] ?? [],
    'show_home' => $breadcrumbs['show_home'] ?? true,
    'show_current' => $breadcrumbs['show_current'] ?? true,
    'show_messages_after' => $breadcrumbs['show_messages_after'] ?? true,
    'back_url' => $breadcrumbs['back_url'] ?? null,
    'icon' => $breadcrumbs['icon'] ?? null,
    'action' => $breadcrumbs['action'] ?? null,
])

@php
    // Determine back URL: use explicit back_url, or last item with URL
    // Note: We only show back arrow if there's a parent page (not just home)
    $backUrl = $back_url;
    if (!$backUrl && count($items) > 0) {
        // Find the last item with a URL
        for ($i = count($items) - 1; $i >= 0; $i--) {
            if (isset($items[$i]['url'])) {
                $backUrl = $items[$i]['url'];
                break;
            }
        }
    }
@endphp

@if (!$disabled)
<div class="mb-6 w-full flex flex-nowrap items-center justify-between gap-3">
    <div class="flex items-center gap-x-2 min-w-0 flex-1">
        @if($icon)
            @if($backUrl)
                <a
                    href="{{ $backUrl }}"
                    class="inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                    title="{{ __('Go back') }}"
                >
                    <iconify-icon icon="{{ $icon }}" width="20" height="20"></iconify-icon>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                    <iconify-icon icon="{{ $icon }}" width="20" height="20"></iconify-icon>
                </span>
            @endif
            <iconify-icon icon="lucide:chevron-right" width="16" height="16" class="text-gray-400 dark:text-gray-500 shrink-0"></iconify-icon>
        @elseif($backUrl)
            <a
                href="{{ $backUrl }}"
                class="inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                title="{{ __('Go back') }}"
            >
                <iconify-icon icon="lucide:arrow-left" width="18" height="18"></iconify-icon>
            </a>
        @endif

        @if($title)
            <h2 class="text-xl font-semibold text-gray-700 dark:text-white/90 flex items-center gap-2 min-w-0">
                {!! $title_before ?? '' !!}
                <span class="truncate" title="{{ __($title) }}">{!! __($title) !!}</span>
                {!! $title_after ?? '' !!}
            </h2>
        @endif
    </div>

    {{-- Action button area (replaces the old breadcrumb navigation) --}}
    @if($action || isset($actions_before) || isset($actions_after))
        <div class="flex items-center gap-2 shrink-0">
            {{-- Actions before slot - for additional buttons/dropdowns before main action --}}
            @if(isset($actions_before))
                {!! $actions_before !!}
            @endif

            {{-- Main action button --}}
            @if($action)
                @php
                    $isPill = is_array($action) && ($action['pill'] ?? false);
                    $btnClass = $isPill
                        ? 'btn-default flex items-center gap-2 rounded-full'
                        : 'btn-primary flex items-center gap-2';
                @endphp
                @if(is_array($action) && isset($action['url']) && isset($action['label']))
                    @if(!isset($action['permission']) || auth()->user()->can($action['permission']))
                        <a href="{{ $action['url'] }}" class="{{ $btnClass }}">
                            <iconify-icon icon="{{ $action['icon'] ?? 'feather:plus' }}" height="{{ $isPill ? '14' : '16' }}"></iconify-icon>
                            {{ __($action['label']) }}
                        </a>
                    @endif
                @elseif(is_array($action) && isset($action['click']) && isset($action['label']))
                    @if(!isset($action['permission']) || auth()->user()->can($action['permission']))
                        <button @click="{{ $action['click'] }}" type="button" class="{{ $btnClass }}">
                            <iconify-icon icon="{{ $action['icon'] ?? 'feather:plus' }}" height="{{ $isPill ? '14' : '16' }}"></iconify-icon>
                            {{ __($action['label']) }}
                        </button>
                    @endif
                @else
                    {!! $action !!}
                @endif
            @endif

            {{-- Actions after slot - for additional buttons/dropdowns after main action --}}
            @if(isset($actions_after))
                {!! $actions_after !!}
            @endif
        </div>
    @endif
</div>
@endif

@if($show_messages_after)
    <x-messages />
@endif
