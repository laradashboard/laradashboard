@php
    $currentLocale = app()->getLocale();
    $lang = get_languages()[$currentLocale] ?? [
        'code' => strtoupper($currentLocale),
        'name' => strtoupper($currentLocale),
        'icon' => '/images/flags/default.svg',
    ];
@endphp

<button id="dropdownLocalesButton" data-dropdown-toggle="dropdownLocales" data-dropdown-placement="bottom"
    class="hover:text-dark-900 relative flex h-11 px-3 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
    type="button">
    <img src="{{ $lang['icon'] }}" alt="{{ $lang['name'] }} flag" height="20" width="20"
        class="mr-2" />
    {{ $lang['code'] }}

    <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
        viewBox="0 0 10 6">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="m1 1 4 4 4-4" />
    </svg>
</button>

<div id="dropdownLocales" class="z-10 hidden bg-white rounded-lg shadow-sm dark:bg-gray-700 max-h-[50vh] overflow-y-auto">
    <ul class="text-gray-700 dark:text-gray-200" aria-labelledby="dropdownLocalesButton">
        @foreach (get_languages() as $code => $lang)
            <li>
                <a href="{{ route('locale.switch', $code) }}"
                    class="flex px-2 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 pl-3 pr-6">
                    <img src="{{ $lang['icon'] }}" alt="{{ $lang['name'] }} flag" height="20"
                        width="20" class="mr-2" />
                    {{ $lang['code'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>