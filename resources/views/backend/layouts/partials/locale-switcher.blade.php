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

<button id="dropdownLocalesButton" data-dropdown-toggle="dropdownLocales" data-dropdown-placement="bottom"
    class="{{ $buttonClass }}"
    type="button">
    @php
        $iconPath = public_path(ltrim($lang['icon'], '/'));
        $iconSrc = file_exists($iconPath) ? asset($lang['icon']) : '/images/flags/default.svg';
    @endphp
    <iconify-icon icon="prime:language" width="{{ $iconSize }}" height="{{ $iconSize }}" class="{{ $iconClass }}"></iconify-icon>
    {{-- <img src="{{ $iconSrc }}" alt="{{ $lang['name'] }} flag" height="20" width="20" class="mr-2" /> --}}
    {{-- {{ $lang['name'] }} --}}
    {{-- {{ $lang['code'] }} --}}
    {{-- <img src="{{ $iconSrc }}" alt="{{ $lang['name'] }} flag" height="20" width="20"/> --}}
    {{-- {{ $lang['code'] }} --}}
</button>

<div id="dropdownLocales" class="z-10 absolute right-0 hidden bg-white rounded-md shadow-sm dark:bg-gray-700 max-h-[300px] overflow-y-auto w-[200px]">
    <ul class="text-gray-700 dark:text-gray-200" aria-labelledby="dropdownLocalesButton">
        @foreach (get_languages() as $code => $lang)
            <li>
                <a href="{{ route('locale.switch', $code) }}"
                    class="flex px-2 py-2 cursor-pointer text-sm hover:bg-gray-100 dark:hover:bg-gray-600 pl-3 pr-6">
                    @php
                        $iconPath = public_path(ltrim($lang['icon'], '/'));
                        $iconSrc = file_exists($iconPath) ? $lang['icon'] : '/images/flags/default.svg';
                    @endphp
                    <img src="{{ $iconSrc }}" alt="{{ $lang['name'] }} flag" height="20"
                        width="20" class="mr-2" />
                        {{ $lang['name'] }}
                    {{-- ({{ $lang['code'] }}) --}}
                    <iconify-icon icon="lucide:check" width="16" height="16"
                        class="ml-auto {{ $code === $currentLocale ? 'block' : 'hidden' }}"></iconify-icon>
                </a>
            </li>
        @endforeach
    </ul>
</div>

