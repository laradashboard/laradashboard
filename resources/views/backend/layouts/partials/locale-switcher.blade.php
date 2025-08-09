@php
    $currentLocale = app()->getLocale();
    $lang = get_languages()[$currentLocale] ?? [
        'code' => strtoupper($currentLocale),
        'name' => strtoupper($currentLocale),
        'icon' => '/images/flags/default.svg',
    ];

    $buttonClass = $buttonClass ?? 'hover:text-dark-900 relative flex p-2 items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white';

    $iconClass = $iconClass ?? 'text-gray-700 transition-colors hover:text-gray-800 dark:text-gray-300 dark:hover:text-white';

    $iconSize = $iconSize ?? '24';
@endphp

<div x-data="{ 
    open: false,
    close() {
        this.open = false;
    }
}" 
@click.away="close()" 
@keydown.escape.window="close()" 
class="relative">
    
    <button 
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="true"
        class="{{ $buttonClass }}"
        type="button">
        <iconify-icon icon="prime:language" width="{{ $iconSize }}" height="{{ $iconSize }}" class="{{ $iconClass }}"></iconify-icon>
    </button>

    <div 
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        x-trap.inert.noscroll="open"
        class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 max-h-[200px] overflow-y-auto"
        role="menu" 
        aria-orientation="vertical" 
        tabindex="-1">
        
        <div class="py-1" role="none">
            @foreach (get_languages() as $code => $language)
                <a href="{{ route('locale.switch', $code) }}"
                   @click="close()"
                   class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white {{ $code === $currentLocale ? 'bg-gray-50 dark:bg-gray-700' : '' }}"
                   role="menuitem" 
                   tabindex="-1">
                    
                    @php
                        $iconPath = public_path(ltrim($language['icon'], '/'));
                        $iconSrc = file_exists($iconPath) ? $language['icon'] : '/images/flags/default.svg';
                    @endphp
                    
                    <img src="{{ $iconSrc }}" 
                         alt="{{ $language['name'] }} flag" 
                         class="mr-3 h-5 w-5 flex-shrink-0 rounded-sm" />
                    
                    <span class="flex-1">{{ $language['name'] }}</span>
                    
                    @if($code === $currentLocale)
                        <iconify-icon icon="lucide:check" 
                                     class="ml-3 h-4 w-4 text-primary" 
                                     aria-hidden="true"></iconify-icon>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

