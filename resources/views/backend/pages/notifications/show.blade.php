<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $notification->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.notifications.preview-page', $notification->id) }}" target="_blank" class="btn btn-secondary">
                    <iconify-icon icon="lucide:eye" class="mr-2"></iconify-icon>
                    {{ __('Preview') }}
                </a>
                <button onclick="openTestEmailModal({{ $notification->id }})" class="btn btn-secondary">
                    <iconify-icon icon="lucide:mail" class="mr-2"></iconify-icon>
                    {{ __('Send Test') }}
                </button>
                <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="btn btn-primary">
                    <iconify-icon icon="lucide:edit" class="mr-2"></iconify-icon>
                    {{ __('Edit') }}
                </a>
                <form method="POST" action="{{ route('admin.notifications.destroy', $notification->id) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this notification?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <iconify-icon icon="lucide:trash-2" class="mr-2"></iconify-icon>
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <x-card>
                    <x-slot name="header">
                        {{ __('Notification Details') }}
                    </x-slot>

                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Name') }}</h4>
                            <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $notification->name }}</p>
                        </div>

                        @if($notification->description)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</h4>
                            <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $notification->description }}</p>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Notification Type') }}</h4>
                                <div class="mt-1 flex items-center">
                                    <iconify-icon icon="{{ $notification->notification_type->icon() }}" class="mr-2 text-primary"></iconify-icon>
                                    <span class="text-base text-gray-900 dark:text-white">{{ $notification->notification_type->label() }}</span>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Receiver Type') }}</h4>
                                <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $notification->receiver_type->label() }}</p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Email Template') }}</h4>
                            <div class="mt-1">
                                @if($notification->email_template_id && $notification->emailTemplate)
                                    <a href="{{ route('admin.email-templates.show', $notification->email_template_id) }}" class="text-primary hover:underline">
                                        {{ $notification->emailTemplate->name }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Using custom content') }}</span>
                                @endif
                            </div>
                        </div>

                        @if($notification->body_html)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('Email Content') }}</h4>
                            <div class="mt-2 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('HTML Content:') }}</p>
                                <div class="prose prose-sm max-w-none dark:prose-invert">
                                    {!! $notification->body_html !!}
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

            <div class="space-y-6">
                <x-card>
                    <x-slot name="header">
                        {{ __('Status') }}
                    </x-slot>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Active') }}</span>
                            @if($notification->is_active)
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">{{ __('Yes') }}</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-900 dark:text-gray-200">{{ __('No') }}</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Track Opens') }}</span>
                            @if($notification->track_opens)
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">{{ __('Yes') }}</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-900 dark:text-gray-200">{{ __('No') }}</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Track Clicks') }}</span>
                            @if($notification->track_clicks)
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">{{ __('Yes') }}</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-900 dark:text-gray-200">{{ __('No') }}</span>
                            @endif
                        </div>
                    </div>
                </x-card>

                @if($notification->from_email || $notification->reply_to_email)
                <x-card>
                    <x-slot name="header">
                        {{ __('Email Settings') }}
                    </x-slot>

                    <div class="space-y-4">
                        @if($notification->from_email)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('From Email') }}</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $notification->from_email }}</p>
                        </div>
                        @endif

                        @if($notification->from_name)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('From Name') }}</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $notification->from_name }}</p>
                        </div>
                        @endif

                        @if($notification->reply_to_email)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Reply-To Email') }}</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $notification->reply_to_email }}</p>
                        </div>
                        @endif

                        @if($notification->reply_to_name)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Reply-To Name') }}</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $notification->reply_to_name }}</p>
                        </div>
                        @endif
                    </div>
                </x-card>
                @endif

                <x-card>
                    <x-slot name="header">
                        {{ __('Metadata') }}
                    </x-slot>

                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created') }}</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $notification->created_at->format('M d, Y H:i') }}</p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Updated') }}</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $notification->updated_at->format('M d, Y H:i') }}</p>
                        </div>

                        @if($notification->creator)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created By') }}</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $notification->creator->name }}</p>
                        </div>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    <!-- Test Email Modal Component -->
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
