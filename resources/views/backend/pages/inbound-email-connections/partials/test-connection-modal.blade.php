<div x-data="{
    open: false,
    isLoading: false,
    connectionId: null,
    connectionName: '',
    result: null,
    init() {
        window.addEventListener('open-test-inbound-modal', (event) => {
            this.connectionId = event.detail.id;
            this.connectionName = event.detail.name;
            this.result = null;
            this.open = true;
        });
    },
    async testConnection() {
        this.isLoading = true;
        this.result = null;

        try {
            const response = await fetch(`{{ route('admin.inbound-email-connections.index') }}/${this.connectionId}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });

            const data = await response.json();
            this.result = data;
        } catch (error) {
            console.error('Error:', error);
            this.result = {
                success: false,
                message: '{{ __('An error occurred while testing the connection') }}'
            };
        } finally {
            this.isLoading = false;
        }
    },
    closeModal() {
        this.open = false;
        this.result = null;
    }
}">
    <x-modal id="test-inbound-connection-modal">
        <x-slot name="trigger"></x-slot>

        <div class="p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                <iconify-icon icon="lucide:plug-zap" class="text-xl text-purple-600 dark:text-purple-400"></iconify-icon>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Test IMAP Connection') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="connectionName"></p>
            </div>
        </div>

        <!-- Test Result -->
        <template x-if="result">
            <div :class="result.success
                ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
                : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'"
                class="border rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <iconify-icon
                        :icon="result.success ? 'lucide:check-circle' : 'lucide:x-circle'"
                        :class="result.success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                        class="text-xl flex-shrink-0 mt-0.5">
                    </iconify-icon>
                    <div>
                        <p :class="result.success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'"
                           class="font-medium" x-text="result.success ? '{{ __('Connection Successful') }}' : '{{ __('Connection Failed') }}'"></p>
                        <p :class="result.success ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'"
                           class="text-sm mt-1" x-text="result.message"></p>
                    </div>
                </div>
            </div>
        </template>

        <!-- No Result Yet -->
        <template x-if="!result && !isLoading">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-6">
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    {{ __('Click the button below to test the IMAP connection. This will verify that the credentials are correct and the server is reachable.') }}
                </p>
            </div>
        </template>

        <!-- Loading State -->
        <template x-if="isLoading">
            <div class="flex items-center justify-center py-8">
                <div class="text-center">
                    <iconify-icon icon="lucide:loader-2" class="text-4xl text-primary animate-spin"></iconify-icon>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Testing connection...') }}</p>
                </div>
            </div>
        </template>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" @click="closeModal()" class="btn btn-secondary">
                {{ __('Close') }}
            </button>
            <button type="button" @click="testConnection()" class="btn btn-primary" :disabled="isLoading">
                <iconify-icon icon="lucide:plug-zap" class="mr-2"></iconify-icon>
                {{ __('Test Connection') }}
            </button>
        </div>
    </div>
    </x-modal>
</div>
