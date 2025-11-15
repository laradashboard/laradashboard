<x-layouts.backend-layout :breadcrumbs="$breadcrumbs ?? ['title' => __('Email Preview')]">
    <div class="space-y-6">
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">{{ __('Email Preview') }}</h3>
                        <span class="badge">{{ $template->name }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openTestEmailModal()" class="btn-default">
                            <iconify-icon icon="lucide:mail" class="mr-2"></iconify-icon>
                            {{ __('Send Test') }}
                        </button>
                        <a href="{{ route('admin.email-templates.show', $template->id) }}" class="btn-default">
                            <iconify-icon icon="lucide:arrow-left" class="mr-2"></iconify-icon>
                            {{ __('Back to Template') }}
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
                <div class="prose max-w-none dark:prose-invert prose-headings:font-medium prose-headings:text-gray-700 dark:prose-headings:text-white/90 prose-p:text-gray-700 dark:prose-p:text-gray-300">
                    {!! $rendered['body_html'] !!}
                </div>
            </div>
        </x-card>
    </div>

    <!-- Test Email Modal Component -->
    <x-modals.test-email :send-test-url="route('admin.email-templates.send-test', $template->id)" />

    <script>
        function openTestEmailModal() {
            window.dispatchEvent(new CustomEvent('open-test-email-modal'));
        }
    </script>
</x-layouts.backend-layout>