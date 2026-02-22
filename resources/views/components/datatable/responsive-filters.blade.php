@props([
    'filters' => [],
    'enableLivewire' => true,
    'hasActiveFilters' => false,
    'maxVisible' => 4,
])

@php
    $filterCount = count($filters);
    $activeFilterCount = collect($filters)->filter(fn($f) => !empty($f['selected']))->count();
    $visibleFilters = array_slice($filters, 0, $maxVisible);
    $hiddenFilters = array_slice($filters, $maxVisible);
    $hiddenCount = count($hiddenFilters);
    $hiddenActiveCount = collect($hiddenFilters)->filter(fn($f) => !empty($f['selected']))->count();
@endphp

<div class="flex items-center gap-2 flex-wrap w-full" style="justify-content: end;">
    @if(method_exists($this, 'renderBeforeFilters'))
        {{ $this->renderBeforeFilters() }}
    @endif

    <!-- Clear Filters Button -->
    @if($hasActiveFilters)
        <button
            type="button"
            wire:click="clearFilters"
            class="text-sm text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 flex items-center gap-1 transition-colors duration-200"
            title="{{ __('Clear all filters') }}"
        >
            <iconify-icon icon="lucide:x-circle" class="text-base"></iconify-icon>
            {{ __('Clear') }}
        </button>
    @endif

    <!-- Desktop: Visible Filter Dropdowns -->
    <div class="hidden md:flex items-center gap-2">
        @foreach($visibleFilters as $filter)
            <div class="flex items-center justify-center relative" x-data="{ open: false }">
                <button
                    @click="open = !open"
                    class="btn-default flex items-center justify-center gap-2 whitespace-nowrap {{ !empty($filter['selected']) ? 'ring-2 ring-primary/50 bg-primary/5' : '' }}"
                    type="button"
                >
                    @if($filter['icon'] ?? false)
                        <iconify-icon icon="{{ $filter['icon'] }}"></iconify-icon>
                    @endif
                    {{ $filter['filterLabel'] }}
                    @if(!empty($filter['selected']))
                        <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-primary"></span>
                    @endif
                    <iconify-icon icon="lucide:chevron-down" class="transition-transform duration-200" :class="{'rotate-180': open}"></iconify-icon>
                </button>

                <div
                    x-show="open"
                    @click.outside="open = false"
                    x-transition
                    class="absolute top-10 right-0 mt-2 w-56 rounded-md shadow bg-white dark:bg-gray-700 z-20 p-3 overflow-y-auto max-h-80"
                >
                    <ul class="space-y-2">
                        <li
                            class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded {{ empty($filter['selected']) ? 'bg-gray-200 dark:bg-gray-600 font-bold' : '' }}"
                            @if($enableLivewire)
                                wire:click="$set('{{ $filter['id'] }}', ''); $dispatch('resetPage')"
                            @endif
                            @click="open = false"
                        >
                            {{ $filter['allLabel'] ?? __('All') }}
                        </li>
                        @foreach ($filter['options'] as $key => $value)
                            @php
                                $isLabelValuePair = is_array($value) && isset($value['label']);
                                $optionValue = $isLabelValuePair ? $value['value'] : $key;
                                $optionLabel = $isLabelValuePair ? $value['label'] : $value;
                            @endphp
                            <li
                                class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded {{ $filter['selected'] == $optionValue ? 'bg-gray-200 dark:bg-gray-600 font-bold' : '' }}"
                                @if($enableLivewire)
                                    wire:click="$set('{{ $filter['id'] }}', '{{ $optionValue }}'); $dispatch('resetPage')"
                                @else
                                    onclick="window.location.href = '{{ $filter['route'] ?? '' }}?{{ $filter['id'] }}={{ $optionValue }}';"
                                @endif
                                @click="open = false"
                            >
                                {!! ucfirst($optionLabel) !!}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach

        <!-- Desktop: More Filters Dropdown (for hidden filters) -->
        @if($hiddenCount > 0)
            <div class="relative" x-data="{ moreOpen: false }">
                <button
                    @click="moreOpen = !moreOpen"
                    class="btn-default flex items-center justify-center gap-2 whitespace-nowrap {{ $hiddenActiveCount > 0 ? 'ring-2 ring-primary/50 bg-primary/5' : '' }}"
                    type="button"
                >
                    <iconify-icon icon="lucide:sliders-horizontal"></iconify-icon>
                    <span>{{ __('More') }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $hiddenCount }})</span>
                    @if($hiddenActiveCount > 0)
                        <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-primary"></span>
                    @endif
                    <iconify-icon icon="lucide:chevron-down" class="transition-transform duration-200" :class="{'rotate-180': moreOpen}"></iconify-icon>
                </button>

                <div
                    x-show="moreOpen"
                    @click.outside="moreOpen = false"
                    x-transition
                    class="absolute top-10 right-0 mt-2 w-80 rounded-md shadow-lg bg-white dark:bg-gray-700 z-30 p-4 max-h-[70vh] overflow-y-auto"
                >
                    <div class="space-y-4">
                        @foreach($hiddenFilters as $filter)
                            @php
                                $filterIcon = $filter['icon'] ?? null;
                            @endphp
                            <div>
                                <label class="form-label flex items-center gap-1.5 mb-1.5">
                                    @if($filterIcon)
                                        <iconify-icon icon="{{ $filterIcon }}" class="text-sm"></iconify-icon>
                                    @endif
                                    {{ $filter['filterLabel'] }}
                                    @if(!empty($filter['selected']))
                                        <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-primary"></span>
                                    @endif
                                </label>
                                <select
                                    class="form-control w-full"
                                    @if($enableLivewire)
                                        wire:model.live="{{ $filter['id'] }}"
                                    @else
                                        onchange="window.location.href = '{{ $filter['route'] ?? '' }}?{{ $filter['id'] }}=' + this.value;"
                                    @endif
                                >
                                    <option value="">{{ $filter['allLabel'] ?? __('All') }}</option>
                                    @foreach ($filter['options'] as $key => $value)
                                        @php
                                            $isLabelValuePair = is_array($value) && isset($value['label']);
                                            $optionValue = $isLabelValuePair ? $value['value'] : $key;
                                            $optionLabel = $isLabelValuePair ? $value['label'] : $value;
                                        @endphp
                                        <option
                                            value="{{ $optionValue }}"
                                            {{ $filter['selected'] == $optionValue ? 'selected' : '' }}
                                        >
                                            {!! ucfirst($optionLabel) !!}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Mobile: Full-Screen Filter Panel -->
    <div class="md:hidden w-full" x-data="{ mobileFiltersOpen: false }">

        <!-- Trigger button -->
        <button
            @click="mobileFiltersOpen = true"
            class="btn-default flex items-center justify-center gap-2 w-full"
            type="button"
            :aria-expanded="mobileFiltersOpen"
            aria-controls="mobile-filter-panel"
        >
            <iconify-icon icon="lucide:filter"></iconify-icon>
            <span>{{ __('Filters') }}</span>
            @if($activeFilterCount > 0)
                <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-xs font-medium rounded-full bg-primary text-white">
                    {{ $activeFilterCount }}
                </span>
            @endif
        </button>

        <!-- Backdrop -->
        <div
            x-show="mobileFiltersOpen"
            x-transition.opacity
            x-cloak
            @click="mobileFiltersOpen = false"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
            aria-hidden="true"
        ></div>

        <!-- Full-screen panel (slides up from bottom) -->
        <div
            id="mobile-filter-panel"
            x-show="mobileFiltersOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-y-full"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-full"
            class="fixed inset-x-0 bottom-0 z-50 flex flex-col bg-white dark:bg-gray-800 rounded-t-2xl shadow-2xl max-h-[85vh]"
            role="dialog"
            aria-modal="true"
            aria-label="{{ __('Filters') }}"
        >
            <!-- Drag handle -->
            <div class="flex justify-center pt-3 pb-1 shrink-0">
                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></div>
            </div>

            <!-- Panel header -->
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700 shrink-0">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <iconify-icon icon="lucide:filter" class="text-primary"></iconify-icon>
                    {{ __('Filters') }}
                    @if($activeFilterCount > 0)
                        <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-xs font-medium rounded-full bg-primary text-white">
                            {{ $activeFilterCount }}
                        </span>
                    @endif
                </h3>
                <div class="flex items-center gap-3">
                    @if($hasActiveFilters)
                        <button
                            type="button"
                            wire:click="clearFilters"
                            @click="mobileFiltersOpen = false"
                            class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 flex items-center gap-1 transition-colors"
                        >
                            <iconify-icon icon="lucide:x-circle"></iconify-icon>
                            {{ __('Clear all') }}
                        </button>
                    @endif
                    <button
                        @click="mobileFiltersOpen = false"
                        type="button"
                        class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors"
                        aria-label="{{ __('Close filters') }}"
                    >
                        <iconify-icon icon="lucide:x" width="20" height="20"></iconify-icon>
                    </button>
                </div>
            </div>

            <!-- Scrollable filter list -->
            <div class="flex-1 overflow-y-auto px-5 py-4">
                <div class="space-y-5">
                    @foreach($filters as $filter)
                        @php $mobileFilterIcon = $filter['icon'] ?? null; @endphp
                        <div>
                            <label class="form-label flex items-center gap-1.5 mb-1.5">
                                @if($mobileFilterIcon)
                                    <iconify-icon icon="{{ $mobileFilterIcon }}" class="text-sm"></iconify-icon>
                                @endif
                                {{ $filter['filterLabel'] }}
                                @if(!empty($filter['selected']))
                                    <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-primary"></span>
                                @endif
                            </label>
                            <select
                                class="form-control w-full"
                                @if($enableLivewire)
                                    wire:model.live="{{ $filter['id'] }}"
                                @else
                                    onchange="window.location.href = '{{ $filter['route'] ?? '' }}?{{ $filter['id'] }}=' + this.value;"
                                @endif
                            >
                                <option value="">{{ $filter['allLabel'] ?? __('All') }}</option>
                                @foreach ($filter['options'] as $key => $value)
                                    @php
                                        $isLabelValuePair = is_array($value) && isset($value['label']);
                                        $optionValue = $isLabelValuePair ? $value['value'] : $key;
                                        $optionLabel = $isLabelValuePair ? $value['label'] : $value;
                                    @endphp
                                    <option
                                        value="{{ $optionValue }}"
                                        {{ $filter['selected'] == $optionValue ? 'selected' : '' }}
                                    >
                                        {!! ucfirst($optionLabel) !!}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Sticky footer: Done button -->
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 shrink-0">
                <button
                    @click="mobileFiltersOpen = false"
                    type="button"
                    class="btn-primary w-full flex items-center justify-center gap-2"
                >
                    <iconify-icon icon="lucide:check" width="16" height="16"></iconify-icon>
                    {{ __('Done') }}
                </button>
            </div>
        </div>
    </div>
</div>
