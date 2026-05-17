<x-card>
    <x-slot name="header">
        {{ __('Error Notifications') }}
    </x-slot>

    <div class="space-y-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Receive a daily email summarising new errors found in laravel.log. Each unique error is reported once, so a fix never produces another notification.') }}
        </p>

        <div>
            <x-inputs.toggle
                name="error_notifications_enabled"
                label="{{ __('Enable daily error notifications') }}"
                :checked="(string) config('settings.error_notifications_enabled', '1') === '1'"
                hint="{{ __('Default: enabled.') }}"
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @php
                $currentAdminEmail = auth()->user()?->email;
            @endphp
            <div>
                <x-inputs.input
                    name="error_notifications_email"
                    type="email"
                    label="{{ __('Recipient email') }}"
                    placeholder="{{ $currentAdminEmail ?: 'ops@example.com' }}"
                    value="{{ old('error_notifications_email', config('settings.error_notifications_email', '')) }}"
                    help-text="{{ $currentAdminEmail
                        ? __('Leave blank to default to your account email (:email).', ['email' => $currentAdminEmail])
                        : __('Leave blank to fall back to the site contact email, then the first admin user.') }}"
                />
            </div>

            <div>
                <x-inputs.input
                    name="error_notifications_time"
                    type="time"
                    label="{{ __('Send time (server time)') }}"
                    value="{{ old('error_notifications_time', config('settings.error_notifications_time', '09:00')) }}"
                    help-text="{{ __('24-hour format. Defaults to 09:00.') }}"
                />
            </div>
        </div>
    </div>
</x-card>
