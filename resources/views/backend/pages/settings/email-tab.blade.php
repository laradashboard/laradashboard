<x-card>
    <x-slot name="header">
        {{ __('Email Settings') }}
    </x-slot>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- From Email -->
            <div>
                <x-inputs.input name="email_from_email" label="{{ __('Default From Email') }}" type="email"
                    value="{{ old('email_from_email', config('settings.email_from_email', config('mail.from.address', ''))) }}"
                    placeholder="noreply@example.com" required
                    help-text="{{ __('Default sender email address for campaigns when not specified in template.') }}" />
            </div>

            <!-- From Name -->
            <div>
                <x-inputs.input name="email_from_name" label="{{ __('Default From Name') }}"
                    value="{{ old('email_from_name', config('settings.email_from_name', config('mail.from.name', config('app.name', '')))) }}"
                    placeholder="Your Company Name" required
                    help-text="{{ __('Default sender name for campaigns when not specified in template.') }}" />
            </div>

            <!-- Reply To Email -->
            <div>
                <x-inputs.input name="email_reply_to_email" label="{{ __('Default Reply-To Email') }}" type="email"
                    value="{{ old('email_reply_to_email', config('settings.email_reply_to_email', '')) }}"
                    placeholder="support@example.com"
                    help-text="{{ __('Default reply-to address. Leave empty to use from email.') }}" />
            </div>

            <!-- Reply To Name -->
            <div>
                <x-inputs.input name="email_reply_to_name" label="{{ __('Default Reply-To Name') }}"
                    value="{{ old('email_reply_to_name', config('settings.email_reply_to_name', '')) }}"
                    placeholder="Support Team"
                    help-text="{{ __('Default reply-to name. Leave empty to use from name.') }}" />
            </div>
        </div>

        <!-- UTM Parameters -->
        <div class="py-6">
            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">
                {{ __('UTM Parameters') }}
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Default UTM Source -->
                <div>
                    <x-inputs.input name="email_utm_source_default" label="{{ __('Default UTM Source') }}"
                        value="{{ old('email_utm_source_default', config('settings.email_utm_source_default', 'email_campaign')) }}"
                        placeholder="email_campaign"
                        help-text="{{ __('Default source parameter for campaign tracking.') }}" />
                </div>

                <!-- Default UTM Medium -->
                <div>
                    <x-inputs.input name="email_utm_medium_default" label="{{ __('Default UTM Medium') }}"
                        value="{{ old('email_utm_medium_default', config('settings.email_utm_medium_default', 'email')) }}"
                        placeholder="email" help-text="{{ __('Default medium parameter for campaign tracking.') }}" />
                </div>
            </div>
        </div>
    </div>
</x-card>
