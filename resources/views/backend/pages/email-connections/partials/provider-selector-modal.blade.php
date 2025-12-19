<div x-data="providerSelectorModal()" x-init="init()">
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="open && closeModal()"
        @click.self="closeModal()"
        class="fixed inset-0 flex items-center justify-center bg-black/20 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        style="display: none; z-index: 10000;"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex max-w-2xl w-full flex-col gap-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('New Connection') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Pick an email provider to ensure your emails are delivered securely and reliably.') }}
                    </p>
                </div>
                <button
                    type="button"
                    @click="closeModal()"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 pb-6 max-h-[60vh] overflow-y-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($providers as $provider)
                        <button
                            type="button"
                            @click="selectProvider('{{ $provider['key'] }}')"
                            class="flex items-center gap-4 p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-primary dark:hover:border-primary transition-colors text-left group"
                        >
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 group-hover:bg-primary/10">
                                <iconify-icon icon="{{ $provider['icon'] }}" class="text-2xl text-gray-600 dark:text-gray-300 group-hover:text-primary"></iconify-icon>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-primary">
                                    {{ $provider['name'] }}
                                </h4>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                    {{ $provider['description'] }}
                                </p>
                            </div>
                            <iconify-icon icon="lucide:chevron-right" class="text-gray-400 group-hover:text-primary"></iconify-icon>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('providerSelectorModal', () => ({
            open: false,

            init() {
                window.addEventListener('open-provider-selector', () => {
                    this.open = true;
                });
                window.addEventListener('close-provider-selector', () => {
                    this.open = false;
                });
            },

            closeModal() {
                this.open = false;
            },

            selectProvider(providerType) {
                selectProvider(providerType);
            }
        }));
    });
</script>
@endpush
@endonce
