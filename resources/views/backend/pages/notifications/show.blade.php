<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
                        {{ $notification->name }}
                    </h3>
                    <div class="flex items-center gap-1.5">
                        <span class="badge {{ $notification->is_active ? 'badge-success': 'badge-default' }}">
                            {{ $notification->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </div>
                </div>
            </x-slot>

            <x-slot name="headerRight">
                <div class="flex gap-2">
                    <button onclick="openTestEmailModal({{ $notification->id }})" class="btn-default">
                        <iconify-icon icon="lucide:mail" class="mr-2"></iconify-icon>
                        {{ __('Send Test') }}
                    </button>

                    <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="btn-primary">
                        <iconify-icon icon="lucide:edit" class="mr-2"></iconify-icon>
                        {{ __('Edit') }}
                    </a>

                    @if($notification->is_deleteable)
                    <form method="POST" action="{{ route('admin.notifications.destroy', $notification->id) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this notification?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <iconify-icon icon="lucide:trash-2" class="mr-2"></iconify-icon>
                            {{ __('Delete') }}
                        </button>
                    </form>
                    @endif
                </div>
            </x-slot>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <x-card>
                        <div class="space-y-4">
                            <table class="w-full mb-6">
                                <tr>
                                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Name:') }}</td>
                                    <td class="text-gray-700 dark:text-gray-300 py-2">{{ $notification->name }}</td>
                                </tr>

                                @if($notification->description)
                                <tr>
                                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Description:') }}</td>
                                    <td class="text-gray-700 dark:text-gray-300 py-2">{{ $notification->description }}</td>
                                </tr>
                                @endif
                            </table>

                            @php
                                // Either show custom notification content (body_html) or fallback to the linked email template's body HTML.
                                $htmlContent = $notification->body_html ?? ($notification->emailTemplate?->body_html ?? null);
                            @endphp

                            @if($htmlContent)
                            <div>
                                <div class="mt-2">
                                    <div x-data="{ active: 'html' }" class="mb-6">
                                        <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                                            <x-tabs :tabs="[
                                                ['id' => 'html', 'label' => __('HTML Content')],
                                                ['id' => 'source', 'label' => __('Source Code')]
                                            ]" />
                                        </div>

                                        <div x-show="active === 'html'" x-cloak id="content-html">
                                            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="prose prose-sm max-w-none dark:prose-invert">
                                                    {!! $htmlContent !!}
                                                </div>
                                            </div>
                                        </div>

                                        <div x-show="active === 'source'" x-cloak id="content-source">
                                            <pre class="whitespace-pre-wrap font-mono text-xs bg-gray-50 dark:bg-gray-800 p-4 rounded-md text-gray-700 dark:text-gray-300 overflow-auto max-h-[500px]"><code>{{ $htmlContent }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($notification->receiver_ids)
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Receiver IDs') }}</h4>
                                <p class="mt-1 text-base text-gray-900 dark:text-white">{{ implode(', ', $notification->receiver_ids) }}</p>
                            </div>
                            @endif

                            @if($notification->receiver_emails)
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Receiver Emails') }}</h4>
                                <p class="mt-1 text-base text-gray-900 dark:text-white">{{ implode(', ', $notification->receiver_emails) }}</p>
                            </div>
                            @endif
                        </div>
                    </x-card>
                </div>
                <div class="lg:col-span-1">
                    <div class="space-y-6">
                        <x-card>
                            <x-slot name="header">
                                {{ __('Details') }}
                            </x-slot>

                            <table class="w-full mb-6">
                                <tr>
                                    <td class="w-1/2 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Notification Type:') }}</td>
                                    <td class="text-gray-700 dark:text-gray-300 py-2">
                                        <div class="inline-flex items-center gap-2">
                                            <iconify-icon icon="{{ $notification->getNotificationTypeIcon() }}" class="mr-2 text-primary"></iconify-icon>
                                            <span class="text-base text-gray-900 dark:text-white">{{ $notification->getNotificationTypeLabel() }}</span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="w-1/2 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Receiver Type:') }}</td>
                                    <td class="text-gray-700 dark:text-gray-300 py-2">{{ $notification->receiver_type_label }}</td>
                                </tr>

                                <tr>
                                    <td class="w-1/2 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Email Template:') }}</td>
                                    <td class="text-gray-700 dark:text-gray-300 py-2">
                                        @if($notification->email_template_id && $notification->emailTemplate)
                                            <a href="{{ route('admin.email-templates.show', $notification->email_template_id) }}" class="text-primary hover:underline">
                                                {{ $notification->emailTemplate->name }}
                                            </a>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Using custom content') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </x-card>

                        <x-card>
                            <x-slot name="header">
                                {{ __('Status') }}
                            </x-slot>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Active') }}</span>
                                    <span class="{{ $notification->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $notification->is_active ? __('Yes') : __('No') }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Track Opens') }}</span>
                                    <span class="{{ $notification->track_opens ? 'badge-success' : 'badge-danger' }}">
                                        {{ $notification->track_opens ? __('Yes') : __('No') }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Track Clicks') }}</span>
                                    <span class="{{ $notification->track_clicks ? 'badge-success' : 'badge-danger' }}">
                                        {{ $notification->track_clicks ? __('Yes') : __('No') }}
                                    </span>
                                </div>
                            </div>
                        </x-card>

                        <x-card>
                            <x-slot name="header">
                                {{ __('Metadata') }}
                            </x-slot>

                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created') }}</h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $notification->created_at->format('M d, Y H:i') }}
                                        {{ __('by') }}
                                        {{ $notification->creator ? $notification->creator->full_name : __('System') }}
                                    </p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Updated') }}</h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $notification->updated_at->format('M d, Y H:i') }}
                                        {{ __('by') }}
                                        {{ $notification->updater ? $notification->updater->full_name : __('System') }}
                                    </p>
                                </div>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <x-modals.test-email :send-test-url="route('admin.notifications.send-test', $notification->id)" />

    @push('scripts')
    <script>
        function openTestEmailModal(id) {
            window.dispatchEvent(new CustomEvent('open-test-email-modal', {
                detail: { id: id }
            }));
        }
    </script>
    @endpush
</x-layouts.backend-layout>
