<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(EmailFilterHook::EMAIL_TEMPLATE_SHOW_AFTER_BREADCRUMBS, '', $emailTemplate) !!}

    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="array_merge($breadcrumbs, ['title' => $emailTemplate->name])">
            <x-slot name="title_after">
                <span class="badge {{ $emailTemplate->is_active ? 'badge-success': 'badge-default' }}">
                    {{ $emailTemplate->is_active ? __('Active') : __('Inactive') }}
                </span>
            </x-slot>
            <x-slot name="actions_before">
                <button onclick="openTestEmailModal('{{ $emailTemplate->id }}', 'email-template')" class="btn-default">
                    <iconify-icon icon="lucide:mail" class="mr-2"></iconify-icon>
                    {{ __('Send Test') }}
                </button>
                <button onclick='openDuplicateEmailTemplateModal("{{ $emailTemplate->id }}", "{{ route("admin.email-templates.duplicate", $emailTemplate->id) }}")' class="btn-default">
                    <iconify-icon icon="lucide:copy" class="mr-2"></iconify-icon>
                    {{ __('Duplicate') }}
                </button>
                <a href="{{ route('admin.email-templates.edit', $emailTemplate->id) }}" class="btn-primary">
                    <iconify-icon icon="feather:edit-2" class="mr-2"></iconify-icon>
                    {{ __('Edit') }}
                </a>
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    <div class="space-y-6">
        <x-card>
            <table class="w-full mb-6">
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Created:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2">
                        {{ $emailTemplate->created_at->format('M d, Y h:i A') }}
                        {{ __('by') }}
                        {{ $emailTemplate->creator->full_name ?? __('System') }}
                    </td>
                </tr>
                @if($emailTemplate->created_at != $emailTemplate->updated_at)
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Updated:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2">{{ $emailTemplate->updated_at->format('M d, Y h:i A') }}</td>
                </tr>
                @endif
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Template Type:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2"><span class="badge">{{ $emailTemplate->type_label }}</span></td>
                </tr>
                @if($emailTemplate->description)
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Internal Description:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2">{{ $emailTemplate->description }}</td>
                </tr>
                @endif
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Subject:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2">{{ $emailTemplate->subject }}</td>
                </tr>
            </table>

            <!-- Email Content -->
            <div x-data="{ active: 'html' }" class="mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                    <x-tabs :tabs="[
                        ['id' => 'html', 'label' => __('HTML Content')],
                        ['id' => 'source', 'label' => __('Source Code')]
                    ]" />
                </div>

                <div x-show="active === 'html'" x-cloak id="content-html">
                    <div class="prose max-w-none dark:prose-invert prose-headings:font-medium prose-headings:text-gray-700 dark:prose-headings:text-white/90 prose-p:text-gray-700 dark:prose-p:text-gray-300">
                        {!! $emailTemplate->body_html !!}
                    </div>
                </div>

                <div x-show="active === 'source'" x-cloak id="content-source">
                    <pre class="whitespace-pre-wrap font-mono text-xs bg-gray-50 dark:bg-gray-800 p-4 rounded-md text-gray-700 dark:text-gray-300 overflow-auto max-h-[500px]"><code>{{ $emailTemplate->body_html }}</code></pre>
                </div>
            </div>
        </x-card>
    </div>

    <x-modals.test-email />
    <x-modals.duplicate-email-template :duplicate-url="route('admin.email-templates.duplicate', $emailTemplate->id)" />

    {!! Hook::applyFilters(EmailFilterHook::EMAIL_TEMPLATE_SHOW_AFTER_CONTENT, '', $emailTemplate) !!}
</x-layouts.backend-layout>