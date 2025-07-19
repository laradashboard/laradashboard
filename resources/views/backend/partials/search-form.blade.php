<form action="{{ url()->current() }}" method="GET" class="flex items-center">
    @foreach(request()->except('search') as $key => $value)
        @if(is_array($value))
            @foreach($value as $subKey => $subValue)
                <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="relative">
        <span
            class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2"
        >
            <svg
                class="fill-gray-500 dark:fill-gray-400"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"
                    fill=""
                ></path>
            </svg>
        </span>
        <input
            id="search-input"
            name="search"
            value="{{ request('search') }}"
            type="text"
            placeholder="{{ $placeholder }}"
            class="form-control !h-11 !py-2.5 !pl-12 !pr-14"
        />

        <button
            id="search-button"
            class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-md border border-gray-200 bg-gray-50 px-[7px] py-[4.5px] text-xs -tracking-[0.2px] text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
            aria-label="{{ __('Search') }}"
            type="submit"
            title="{{ __('Search') }}"
        >
            <span> ⌘ </span>
            <span> K </span>
        </button>
    </div>
</form>
