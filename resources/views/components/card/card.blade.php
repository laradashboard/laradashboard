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
                <div class="py-4 md:px-8 space-y-6 sm:p-4 border-b border-gray-200 dark:border-gray-8 font-semibold flex justify-between items-center {{ $headerClass ?? '' }}">
                    {{ $header }}
                </div>
            @endisset

            <div class="py-4 md:px-8 space-y-6 sm:p-4 {{ isset($footer) ? 'border-b border-gray-200 dark:border-gray-800' : '' }} {{ $bodyClass ?? '' }}">
                {{ $slot }}
            </div>

            @isset($footer)
            <div class="py-4 md:px-8 space-y-6 sm:p-4 flex justify-between items-center {{ $footerClass ?? '' }}">
                {{ $footer }}
            </div>
            @endisset
        </div>
    </template>
</div>