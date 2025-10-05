<div x-data="{ open: false }" class="relative inline-block">
    <button
        @click="open = !open"
        type="button"
        class="badge
            {{ $status == true || $status === 'completed' || $status === 'active' ? 'badge-success' : 
              ($status === 'in_progress' ? 'badge-primary' : 'badge-warning') }}
            flex items-center gap-1"
    >
        {{ $statuses[$status] ?? __("Unknown") }}
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
        @foreach($statuses as $key => $label)
            <button
                wire:click="changeStatusTo('{{ $key }}')"
                @click="open = false"
                class="block w-full text-left px-4 py-2 text-sm
                    {{ $status == $key ? 'font-bold bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }}
                    {{ $key == true || $key === 'completed' || $key === 'active' ? 'text-green-700 dark:text-green-400' : 
                      ($key === 'in_progress' ? 'text-blue-700 dark:text-blue-400' : 'text-yellow-700 dark:text-yellow-400') }}"
                type="button"
                @if($status == $key) disabled @endif
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <span
        wire:loading
        wire:target="changeStatusTo"
        class="ml-2 text-gray-500 dark:text-gray-400 text-xs"
    >
        {{ __("Processing...") }}
    </span>
</div>
