<div class="flex items-center gap-3">
    <a href="{{ route('admin.modules.show', $module->name) }}">
        <x-module-logo :module="$module" size="md" />
    </a>

    <div>
        <a
            href="{{ route('admin.modules.show', $module->name) }}"
            class="font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
        >
            {{ $module->title }}
        </a>
        @if($module->author || $module->documentation_url)
            <div class="flex flex-col items-start gap-1 mt-2">
                @if($module->author)
                    <a
                        href="{{ $module->author_url ?? '#' }}"
                        target="{{ $module->author_url ? '_blank' : '_self' }}"
                        rel="noopener"
                        class="flex gap-2 text-xs text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 {{ !$module->author_url ? 'pointer-events-none' : '' }}"
                        @if(!$module->author_url) onclick="return false;" @endif
                        title="{{ __('Author') }}"
                    >
                        <iconify-icon icon="lucide:external-link" class="text-sm"></iconify-icon>
                        {{ $module->author }}
                    </a>
                @endif
                @if($module->documentation_url)
                    <a
                        href="{{ $module->documentation_url }}"
                        target="_blank"
                        rel="noopener"
                        class="flex gap-2 items-center text-xs text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400"
                        title="{{ __('Documentation') }}"
                    >
                        <iconify-icon icon="lucide:book-open" class="text-sm"></iconify-icon>
                        <span>{{ __('Docs') }}</span>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
