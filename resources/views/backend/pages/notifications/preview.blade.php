<x-layouts.backend-layout :breadcrumbs="$breadcrumbs ?? ['title' => __('Notification Preview')]">
    <div class="space-y-6">
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">{{ __('Notification Preview') }}</h3>
                        <span class="badge">{{ $notification->name }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openTestEmailModal()" class="btn-default">
                            <iconify-icon icon="lucide:mail" class="mr-2"></iconify-icon>
                            {{ __('Send Test') }}
                        </button>
                        <a href="{{ route('admin.notifications.show', $notification->id) }}" class="btn-default">
                            <iconify-icon icon="lucide:arrow-left" class="mr-2"></iconify-icon>
                            {{ __('Back to Notification') }}
                        </a>
                    </div>
                </div>
            </x-slot>

            <!-- Subject -->
            <div class="mb-6">
                <h4 class="text-lg font-medium text-gray-700 dark:text-white/90 mb-2">{{ __('Subject') }}</h4>
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-md text-gray-700 dark:text-gray-300">
                    {{ $rendered['subject'] }}
                </div>
            </div>

            <!-- Email Content -->
            <div class="mb-6">
                <h4 class="text-lg font-medium text-gray-700 dark:text-white/90 mb-4">{{ __('HTML Preview') }}</h4>
                @if($rendered['body_html'])
                    <div class="prose max-w-none dark:prose-invert prose-headings:font-medium prose-headings:text-gray-700 dark:prose-headings:text-white/90 prose-p:text-gray-700 dark:prose-p:text-gray-300">
                        {!! $rendered['body_html'] !!}
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <iconify-icon icon="lucide:file-x" class="text-4xl mb-2"></iconify-icon>
                        <p>{{ __('No HTML content available') }}</p>
                    </div>
                @endif
            </div>

            <!-- Notification Info -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-medium text-gray-700 dark:text-white/90 mb-3">{{ __('Notification Details') }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Type:') }}</span>
                        <span class="ml-2 text-gray-700 dark:text-gray-300">{{ $notification->notification_type->label() }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Receiver Type:') }}</span>
                        <span class="ml-2 text-gray-700 dark:text-gray-300">{{ $notification->receiver_type->label() }}</span>
                    </div>
                    @if($notification->emailTemplate)
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Template:') }}</span>
                        <a href="{{ route('admin.email-templates.show', $notification->email_template_id) }}" class="ml-2 text-primary hover:underline">
                            {{ $notification->emailTemplate->name }}
                        </a>
                    </div>
                    @else
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Template:') }}</span>
                        <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('Custom Content') }}</span>
                    </div>
                    @endif
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Status:') }}</span>
                        <span class="ml-2">
                            @if($notification->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ __('Active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    {{ __('Inactive') }}
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Test Email Modal Component -->
    <x-modals.test-email :send-test-url="route('admin.notifications.send-test', $notification->id)" />

    <script>
        function openTestEmailModal() {
            window.dispatchEvent(new CustomEvent('open-test-email-modal'));
        }
    </script>
</x-layouts.backend-layout>
