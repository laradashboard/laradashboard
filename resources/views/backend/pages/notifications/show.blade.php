<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(NotificationFilterHook::NOTIFICATION_SHOW_AFTER_BREADCRUMBS, '', $notification) !!}

    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="array_merge($breadcrumbs, ['title' => $notification->name])">
            <x-slot name="title_after">
                <span class="badge {{ $notification->is_active ? 'badge-success': 'badge-default' }}">
                    {{ $notification->is_active ? __('Active') : __('Inactive') }}
                </span>
            </x-slot>
            <x-slot name="actions_before">
                <button @click="openTestEmailModal({{ $notification->id }}, 'notification')" class="btn-default">
                    <iconify-icon icon="lucide:mail" class="mr-2"></iconify-icon>
                    {{ __('Send Test') }}
                </button>
                <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="btn-primary">
                    <iconify-icon icon="feather:edit-2" class="mr-2"></iconify-icon>
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
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    <div class="space-y-6">
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
                                <td class="text-gray-700 dark:text-gray-300 py-2">{!! $notification->description !!}</td>
                            </tr>
                            @endif
                        </table>

                        @if($notification->emailTemplate)
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email Template Content') }}</h4>
                                <a href="{{ route('admin.email-templates.edit', $notification->email_template_id) }}"
                                    class="btn-primary text-sm"
                                    target="_blank">
                                    <iconify-icon icon="feather:edit-2" class="mr-1.5"></iconify-icon>
                                    {{ __('Edit Template') }}
                                </a>
                            </div>

                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center gap-2 text-sm text-blue-700 dark:text-blue-300">
                                    <iconify-icon icon="lucide:info" class="text-blue-500"></iconify-icon>
                                    {{ __('Email content is managed through the linked email template. Edit the template to modify the notification content.') }}
                                </div>
                            </div>

                            <div class="mt-2">
                                <div x-data="{ active: 'html' }" class="mb-6">
                                    <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                                        <x-tabs :tabs="[
                                            ['id' => 'html', 'label' => __('Preview')],
                                            ['id' => 'source', 'label' => __('Source Code')]
                                        ]" />
                                    </div>

                                    <div x-show="active === 'html'" x-cloak id="content-html">
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-white">
                                            <iframe id="preview-iframe"
                                                    class="w-full"
                                                    style="min-height: 400px;"
                                                    sandbox="allow-same-origin">
                                            </iframe>
                                        </div>
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const iframe = document.getElementById('preview-iframe');
                                            if (iframe) {
                                                const content = @json($previewHtml);
                                                const doc = iframe.contentDocument || iframe.contentWindow.document;
                                                doc.open();
                                                doc.write(content);
                                                doc.close();

                                                // Adjust height after content loads
                                                setTimeout(() => {
                                                    try {
                                                        const height = doc.body ? doc.body.scrollHeight : 400;
                                                        iframe.style.height = Math.min(Math.max(height + 20, 400), 800) + 'px';
                                                    } catch (e) {}
                                                }, 200);
                                            }
                                        });
                                    </script>

                                    <div x-show="active === 'source'" x-cloak id="content-source">
                                        <pre class="whitespace-pre-wrap font-mono text-xs bg-gray-50 dark:bg-gray-800 p-4 rounded-md text-gray-700 dark:text-gray-300 overflow-auto max-h-[500px]"><code>{{ $previewHtml }}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="text-center py-8">
                            <iconify-icon icon="lucide:mail-x" class="text-4xl text-gray-300 dark:text-gray-600 mb-2"></iconify-icon>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('No email template linked to this notification.') }}</p>
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
                                        <span class="text-sm text-red-500 dark:text-red-400">{{ __('No template assigned') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
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
    </div>

    <x-modals.test-email />

    {!! Hook::applyFilters(NotificationFilterHook::NOTIFICATION_SHOW_AFTER_CONTENT, '', $notification) !!}
</x-layouts.backend-layout>
