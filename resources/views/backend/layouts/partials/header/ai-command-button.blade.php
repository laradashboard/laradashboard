{{--
    AI Command Button - Triggers the Agentic CMS Modal
    This button appears in the header navbar and opens the AI command interface.
--}}
<div x-data="{ aiModalOpen: false }">
    <x-tooltip title="{{ __('AI Agent') }}" position="bottom">
        <button
            @click="aiModalOpen = true"
            class="hover:text-dark-900 relative flex items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white p-2 group"
            aria-label="{{ __('Open AI Agent') }}"
        >
            {{-- AI/Sparkles Icon with gradient on hover --}}
            <span class="relative">
                <iconify-icon
                    icon="lucide:sparkles"
                    width="22"
                    height="22"
                    class="transition-all duration-200 group-hover:text-purple-500"
                ></iconify-icon>
                {{-- Subtle pulse indicator when AI is ready --}}
                <span class="absolute -top-0.5 -right-0.5 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-purple-500"></span>
                </span>
            </span>
        </button>
    </x-tooltip>

    {{-- AI Command Modal --}}
    @include('components.modals.ai-command')
</div>
