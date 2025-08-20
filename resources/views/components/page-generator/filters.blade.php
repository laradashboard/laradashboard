@props([
    'filters' => [],
])

<div class="flex items-center justify-center relative" x-data="{ open: false }">
    <button @click="open = !open" class="btn-secondary flex items-center justify-center gap-2" type="button">
        <iconify-icon icon="lucide:sliders"></iconify-icon>
        {{ __('Filters') }}
        <iconify-icon icon="lucide:chevron-down"></iconify-icon>
    </button>
    
    <div x-show="open" 
         @click.outside="open = false" 
         x-transition
         class="absolute top-10 right-0 mt-2 w-72 rounded-md shadow bg-white dark:bg-gray-700 z-10 p-4">
        
        <form method="GET" action="{{ request()->url() }}">
            @foreach(request()->except(array_column($filters, 'name')) as $key => $value)
                @if(!in_array($key, ['page']))
                    @if(is_array($value))
                        @foreach($value as $arrayValue)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $arrayValue }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endif
            @endforeach
            
            <div class="space-y-4">
                @foreach($filters as $filter)
                    <div>
                        <label class="form-label" for="{{ $filter['name'] }}">
                            {{ $filter['label'] ?? $filter['name'] }}
                        </label>
                        
                        @if($filter['type'] === 'select')
                            <select name="{{ $filter['name'] }}" class="form-control" id="{{ $filter['name'] }}">
                                <option value="">{{ __('All') }}</option>
                                @foreach($filter['options'] as $value => $label)
                                    <option value="{{ $value }}"
                                            {{ request($filter['name']) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($filter['type'] === 'date')
                            <input type="date" 
                                   name="{{ $filter['name'] }}" 
                                   value="{{ request($filter['name']) }}"
                                   class="form-input w-full">
                        @elseif($filter['type'] === 'daterange')
                            <div class="flex gap-2">
                                <input type="date" 
                                       name="{{ $filter['name'] }}_from" 
                                       value="{{ request($filter['name'] . '_from') }}"
                                       class="form-input w-full">
                                <input type="date" 
                                       name="{{ $filter['name'] }}_to" 
                                       value="{{ request($filter['name'] . '_to') }}"
                                       class="form-input w-full">
                            </div>
                        @else
                            <input type="text" 
                                   name="{{ $filter['name'] }}" 
                                   value="{{ request($filter['name']) }}"
                                   class="form-input w-full">
                        @endif
                    </div>
                @endforeach
                
                <div class="flex gap-2 pt-2">
                    <button type="submit" 
                            class="btn-primary flex-1">
                        {{ __('Apply Filters') }}
                    </button>
                    
                    <a href="{{ request()->url() }}" 
                       class="btn-secondary flex-1 text-center">
                        {{ __('Clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>