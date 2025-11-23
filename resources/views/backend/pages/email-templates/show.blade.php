<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">
                            {{ $template->name }}
                        </h3>
                        <div class="flex items-center gap-1.5">
                            <span class="badge {{ $template->is_active ? 'badge-success': 'badge-default' }}">
                                {{ $template->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="window.dispatchEvent(new CustomEvent('open-test-email-modal'))" class="btn-default">
                            <iconify-icon icon="lucide:mail" class="mr-2"></iconify-icon>
                            {{ __('Send Test') }}
                        </button>
                        <a href="{{ route('admin.email-templates.edit', $template->id) }}" class="btn-primary">
                            <iconify-icon icon="lucide:pencil" class="mr-2"></iconify-icon>
                            {{ __('Edit') }}
                        </a>
                    </div>
                </div>
            </x-slot>

            <table class="w-full mb-6">
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Created:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2">
                        {{ $template->created_at->format('M d, Y h:i A') }}
                        {{ __('by') }}
                        {{ $template->creator->full_name ?? __('System') }}
                    </td>
                </tr>
                @if($template->created_at != $template->updated_at)
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Updated:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2">{{ $template->updated_at->format('M d, Y h:i A') }}</td>
                </tr>
                @endif
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Template Type:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2"><span class="badge">{{ $template->type->label() }}</span></td>
                </tr>
                @if($template->description)
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Internal Description:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2">{{ $template->description }}</td>
                </tr>
                @endif
                <tr>
                    <td class="w-1/4 font-medium text-gray-700 dark:text-white/90 py-2">{{ __('Subject:') }}</td>
                    <td class="text-gray-700 dark:text-gray-300 py-2">{{ $template->subject }}</td>
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
                        {!! $template->body_html !!}
                    </div>
                </div>

                <div x-show="active === 'source'" x-cloak id="content-source">
                    <pre class="whitespace-pre-wrap font-mono text-xs bg-gray-50 dark:bg-gray-800 p-4 rounded-md text-gray-700 dark:text-gray-300 overflow-auto max-h-[500px]"><code>{{ $template->body_html }}</code></pre>
                </div>
            </div>
        </x-card>
    </div>

    <x-modals.test-email :send-test-url="route('admin.email-templates.send-test', $template->id)" />
</x-layouts.backend-layout>