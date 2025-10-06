<div x-data="{ open: false }" class="relative inline-block">
    <button
        @click="open = !open"
        type="button"
        class="px-3 py-1 inline-flex text-xs leading-5 font-medium rounded-full cursor-pointer
            @if ($model->{$actionField} == 'open') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
            @elseif($model->{$actionField} == 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
            @elseif($model->{$actionField} == 'waiting') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
            @elseif($model->{$actionField} == 'resolved') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
            @elseif($model->{$actionField} == 'closed') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
            @elseif($model->{$actionField} == true || $model->{$actionField} === 'completed' || $model->{$actionField} === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
            @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif
            flex items-center gap-1"
    >
        {{ $options[$model->{$actionField}] ?? ucfirst(str_replace('_', ' ', $model->{$actionField})) }}
        <iconify-icon
            icon="heroicons:chevron-down"
            class="w-3 h-3 ml-1"
            :class="{ 'rotate-180': open }"
        ></iconify-icon>
        <span class="sr-only">{{ __("Change Status") }}</span>
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100" 
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-10 mt-2 w-60 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg"
        style="display: none;"
    >
        @foreach($options as $key => $label)
            <button
                wire:click="changeStatusTo('{{ $key }}')"
                @click="open = false"
                class="block w-full text-left px-4 py-2 text-sm
                    {{ $model->{$actionField} == $key ? 'font-bold bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }}
                    @if ($key == 'open') text-green-700 dark:text-green-400
                    @elseif($key == 'in_progress') text-blue-700 dark:text-blue-400
                    @elseif($key == 'waiting') text-yellow-700 dark:text-yellow-400
                    @elseif($key == 'resolved') text-purple-700 dark:text-purple-400
                    @elseif($key == 'closed') text-gray-700 dark:text-gray-400
                    @elseif($key == true || $key === 'completed' || $key === 'active') text-green-700 dark:text-green-400
                    @else text-yellow-700 dark:text-yellow-400 @endif"
                type="button"
                @if($model->{$actionField} == $key) disabled @endif
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <span
        wire:loading
        wire:target="changeStatusTo"
        class="ml-2 inline-flex items-center"
    >
        <span class="h-3 w-3 bg-blue-600 rounded-full animate-ping opacity-75"></span>
        <span class="sr-only">{{ __("Processing...") }}</span>
    </span>
</div>
