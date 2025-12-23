@php
    $headerClass = isset($headerDescription) ? ('flex-col items-start ' . ($headerClass ?? '')) : ($headerClass ?? '');
@endphp

<div
    x-data="{ loading: @js($skeleton ?? false) }"
    class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] {{ $class ?? '' }}"
>
    <template x-if="loading">
        <x-card.card-skeleton />
    </template>
    <template x-if="!loading">
        <div>
            @isset($header)
                <div class="py-4 px-4 md:px-8 space-y-6 sm:p-4 border-b border-gray-200 dark:border-gray-800 font-semibold flex justify-between items-center {{ $headerClass }}">
                    <div class="w-full flex justify-between mb-0 items-center {{ isset($headerTitleClass) ? $headerTitleClass : '' }}">
                        {!! $header !!}

                        @isset($headerRight)
                            <div class="{{ $headerRightClass ?? '' }}">
                                {!! $headerRight !!}
                            </div>
                        @endisset
                    </div>

                    @isset($headerDescription)
                        <p class="mt-2 text-sm text-gray-500 font-normal dark:text-gray-400 {{ $headerDescriptionClass ?? '' }}">
                            {!! $headerDescription !!}
                        </p>
                    @endisset
                </div>
            @endisset

            <div class="py-8 md:px-8 space-y-6 p-4 {{ isset($footer) ? 'border-b border-gray-200 dark:border-gray-800' : '' }} {{ $bodyClass ?? '' }}">
                {{ $slot }}
            </div>

            @isset($footer)
            <div class="py-4 md:px-8 space-y-6 p-4 flex justify-between items-center {{ $footerClass ?? '' }}">
                {{ $footer }}
            </div>
            @endisset
        </div>
    </template>
</div>