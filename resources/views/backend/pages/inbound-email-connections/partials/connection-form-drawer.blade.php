<x-drawer
    drawerId="inbound-connection-form-drawer"
    title="{{ __('Inbound Email Connection') }}"
    width="max-w-xl"
>
    <x-slot name="trigger"></x-slot>

    <div x-data="{
        isLoading: false,
        errors: {},
        get store() {
            return window.getInboundConnectionStore();
        },
        get isEditing() {
            return this.store.editingId !== null;
        },
        get formTitle() {
            return this.isEditing ? '{{ __('Edit Connection') }}' : '{{ __('New Connection') }}';
        },
        init() {
            window.addEventListener('open-inbound-connection-form', () => {
                this.errors = {};
                window.openDrawer('inbound-connection-form-drawer');
            });
        },
        async submitForm() {
            this.isLoading = true;
            this.errors = {};

            const url = this.isEditing
                ? `{{ route('admin.inbound-email-connections.index') }}/${this.store.editingId}`
                : '{{ route('admin.inbound-email-connections.store') }}';

            const method = this.isEditing ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(this.store.formData)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    window.dispatchEvent(new CustomEvent('close-drawer'));
                    this.store.reset();
                    window.location.reload();
                } else if (data.errors) {
                    this.errors = data.errors;
                } else {
                    alert(data.message || '{{ __('An error occurred') }}');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __('An error occurred') }}');
            } finally {
                this.isLoading = false;
            }
        },
        closeDrawer() {
            window.dispatchEvent(new CustomEvent('close-drawer'));
            this.store.reset();
        }
    }">
        <form @submit.prevent="submitForm" class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white" x-text="formTitle"></h3>

            <!-- Connection Name -->
            <div>
                <label for="name" class="form-label">{{ __('Connection Name') }} <span class="text-red-500">*</span></label>
                <input type="text" id="name" x-model="store.formData.name" class="form-control" placeholder="{{ __('e.g., Support Inbox') }}" required>
                <template x-if="errors.name">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                </template>
            </div>

            <!-- IMAP Server Settings -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-4">
                <h4 class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
                    <iconify-icon icon="lucide:server" class="text-gray-500"></iconify-icon>
                    {{ __('IMAP Server Settings') }}
                </h4>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="imap_host" class="form-label">{{ __('IMAP Host') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="imap_host" x-model="store.formData.imap_host" class="form-control" placeholder="{{ __('e.g., imap.gmail.com') }}" required>
                        <template x-if="errors.imap_host">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.imap_host[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label for="imap_port" class="form-label">{{ __('Port') }} <span class="text-red-500">*</span></label>
                        <input type="number" id="imap_port" x-model="store.formData.imap_port" class="form-control" required>
                        <template x-if="errors.imap_port">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.imap_port[0]"></p>
                        </template>
                    </div>
                </div>

                <div>
                    <label for="imap_encryption" class="form-label">{{ __('Encryption') }} <span class="text-red-500">*</span></label>
                    <select id="imap_encryption" x-model="store.formData.imap_encryption" class="form-control">
                        <option value="ssl">SSL (Port 993)</option>
                        <option value="tls">TLS (Port 143)</option>
                        <option value="none">{{ __('None') }}</option>
                    </select>
                </div>

                <div>
                    <label for="imap_username" class="form-label">{{ __('Username/Email') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="imap_username" x-model="store.formData.imap_username" class="form-control" placeholder="{{ __('e.g., support@yourcompany.com') }}" required>
                    <template x-if="errors.imap_username">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.imap_username[0]"></p>
                    </template>
                </div>

                <div>
                    <label for="imap_password" class="form-label">{{ __('Password') }} <span class="text-red-500" x-show="!isEditing">*</span></label>
                    <input type="password" id="imap_password" x-model="store.formData.imap_password" class="form-control" autocomplete="new-password" :placeholder="isEditing ? '{{ __('Leave blank to keep current') }}' : ''" :required="!isEditing">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('For Gmail, use an') }}
                        <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">{{ __('App Password') }}</a>
                        {{ __('instead of your regular password.') }}
                    </p>
                    <template x-if="errors.imap_password">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.imap_password[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="imap_folder" class="form-label">{{ __('Folder') }}</label>
                        <input type="text" id="imap_folder" x-model="store.formData.imap_folder" class="form-control" placeholder="INBOX">
                    </div>

                    <div class="flex items-end">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="store.formData.imap_validate_cert" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Validate SSL Certificate') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Processing Options -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-4">
                <h4 class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
                    <iconify-icon icon="lucide:settings" class="text-gray-500"></iconify-icon>
                    {{ __('Processing Options') }}
                </h4>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="fetch_limit" class="form-label">{{ __('Fetch Limit') }}</label>
                        <input type="number" id="fetch_limit" x-model="store.formData.fetch_limit" class="form-control" min="1" max="500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Max emails per batch') }}</p>
                    </div>

                    <div>
                        <label for="polling_interval" class="form-label">{{ __('Polling Interval') }}</label>
                        <input type="number" id="polling_interval" x-model="store.formData.polling_interval" class="form-control" min="1" max="1440">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Minutes between checks') }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="store.formData.mark_as_read" class="rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Mark emails as read after processing') }}</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="store.formData.delete_after_processing" class="rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Delete emails after processing') }}</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="store.formData.is_active" class="rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Connection is active') }}</span>
                    </label>
                </div>
            </div>

            <!-- Outbound Connection (Optional) -->
            @if($outboundConnections->count() > 0)
            <div>
                <label for="email_connection_id" class="form-label">{{ __('Link to Outbound Connection (Optional)') }}</label>
                <select id="email_connection_id" x-model="store.formData.email_connection_id" class="form-control">
                    <option value="">{{ __('None') }}</option>
                    @foreach($outboundConnections as $outbound)
                        <option value="{{ $outbound->id }}">{{ $outbound->name }} ({{ $outbound->from_email }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Used for sending reply notifications') }}</p>
            </div>
            @endif

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="closeDrawer()" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="btn btn-primary" :disabled="isLoading">
                    <span x-show="isLoading" class="mr-2">
                        <iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon>
                    </span>
                    <span x-text="isEditing ? '{{ __('Update Connection') }}' : '{{ __('Create Connection') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</x-drawer>
