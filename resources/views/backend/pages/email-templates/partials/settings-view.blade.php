<x-card>
    <x-slot name="header">
        {{ __('Settings') }}
    </x-slot>
    <x-slot name="headerRight">
        <button type="button" onclick="openDrawer('email-settings-drawer')" class="btn-primary">
            <iconify-icon icon="lucide:edit" class="mr-2"></iconify-icon>
            {{ __('Edit Settings') }}
        </button>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-4">
        <!-- Default Email Configuration -->
        <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('From Email') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white truncate"
                title="{{ config('settings.email_from_email', config('mail.from.address', '-')) }}">
                {{ config('settings.email_from_email', config('mail.from.address', '-')) }}
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('From Name') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white truncate"
                title="{{ config('settings.email_from_name', config('mail.from.name', config('app.name', '-'))) }}">
                {{ config('settings.email_from_name', config('mail.from.name', config('app.name', '-'))) }}
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Reply-To Email') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white truncate"
                title="{{ config('settings.email_reply_to_email', '-') }}">
                {{ config('settings.email_reply_to_email') ?: '-' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Reply-To Name') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white truncate"
                title="{{ config('settings.email_reply_to_name', '-') }}">
                {{ config('settings.email_reply_to_name') ?: '-' }}
            </dd>
        </div>

        <!-- UTM Parameters -->
        <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('UTM Source') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white truncate"
                title="{{ config('settings.email_utm_source_default', 'email_campaign') }}">
                {{ config('settings.email_utm_source_default', 'email_campaign') }}
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('UTM Medium') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white truncate"
                title="{{ config('settings.email_utm_medium_default', 'email') }}">
                {{ config('settings.email_utm_medium_default', 'email') }}
            </dd>
        </div>

        <!-- Rate Limiting -->
        <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Emails per Hour') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                {{ number_format(config('settings.email_rate_limit_per_hour', 1000)) }}
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Delay Between Emails') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                {{ config('settings.email_delay_seconds', 0) }}s
            </dd>
        </div>
    </div>
</x-card>
