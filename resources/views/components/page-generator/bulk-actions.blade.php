@props([
    'actions' => [],
])

<div class="relative flex items-center justify-center" x-data="{ open: false }" {{ $attributes }}>
    <button @click="open = !open" 
            class="btn-secondary flex items-center justify-center gap-2 text-sm" 
            type="button">
        <iconify-icon icon="lucide:more-vertical"></iconify-icon>
        <span>{{ __('Bulk Actions') }} (<span x-text="selectedItems.length"></span>)</span>
        <iconify-icon icon="lucide:chevron-down"></iconify-icon>
    </button>
    
    <div x-show="open" 
         @click.outside="open = false" 
         x-transition
         class="absolute right-0 top-10 mt-2 w-48 rounded-md shadow bg-white dark:bg-gray-700 z-10 p-2">
        <ul class="space-y-2">
            @foreach($actions as $action)
                <li class="cursor-pointer flex items-center gap-1 text-sm {{ $action['class'] ?? 'text-gray-700 dark:text-gray-300' }} hover:bg-gray-100 dark:hover:bg-gray-600 px-2 py-1.5 rounded transition-colors duration-300"
                    @if(isset($action['confirm']) && $action['confirm'])
                        @click="open = false; if(confirm('{{ $action['confirmMessage'] ?? __('Are you sure?') }}')) { 
                            document.getElementById('bulk-action-form-{{ $action['name'] }}').submit();
                        }"
                    @else
                        @click="open = false; document.getElementById('bulk-action-form-{{ $action['name'] }}').submit();"
                    @endif
                >
                    @if(isset($action['icon']))
                        <iconify-icon icon="{{ $action['icon'] }}"></iconify-icon>
                    @endif
                    {{ $action['label'] }}
                </li>
                
                <form id="bulk-action-form-{{ $action['name'] }}" 
                      action="{{ $action['route'] }}" 
                      method="POST" 
                      class="hidden">
                    @csrf
                    @if(isset($action['method']))
                        @method($action['method'])
                    @endif
                    
                    <template x-for="id in selectedItems" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    
                    @if(isset($action['fields']))
                        @foreach($action['fields'] as $field => $value)
                            <input type="hidden" name="{{ $field }}" value="{{ $value }}">
                        @endforeach
                    @endif
                </form>
            @endforeach
        </ul>
    </div>
</div>