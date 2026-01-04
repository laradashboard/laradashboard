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

    <!-- Mobile: All Filters in Single Dropdown -->
    <div class="md:hidden relative w-full" x-data="{ mobileFiltersOpen: false }">
        <button
            @click="mobileFiltersOpen = !mobileFiltersOpen"
            class="btn-default flex items-center justify-center gap-2 w-full md:w-auto"
            type="button"
        >
            <iconify-icon icon="lucide:filter"></iconify-icon>
            <span>{{ __('Filters') }}</span>
            @if($activeFilterCount > 0)
                <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-xs font-medium rounded-full bg-primary text-white">
                    {{ $activeFilterCount }}
                </span>
            @endif
            <iconify-icon icon="lucide:chevron-down" class="transition-transform duration-200" :class="{'rotate-180': mobileFiltersOpen}"></iconify-icon>
        </button>

        <div
            x-show="mobileFiltersOpen"
            @click.outside="mobileFiltersOpen = false"
            x-transition
            class="absolute top-10 right-0 mt-2 w-80 rounded-md shadow-lg bg-white dark:bg-gray-700 z-30 p-4 max-h-[70vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-sm font-medium text-gray-700 dark:text-white">{{ __('Filters') }}</h3>
                @if($hasActiveFilters)
                    <button
                        type="button"
                        wire:click="clearFilters"
                        @click="mobileFiltersOpen = false"
                        class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 flex items-center gap-1"
                    >
                        <iconify-icon icon="lucide:x-circle" class="text-sm"></iconify-icon>
                        {{ __('Clear all') }}
                    </button>
                @endif
            </div>

            <div class="space-y-4">
                @foreach($filters as $filter)
                    @php
                        $mobileFilterIcon = $filter['icon'] ?? null;
                    @endphp
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
    </div>
</div>
