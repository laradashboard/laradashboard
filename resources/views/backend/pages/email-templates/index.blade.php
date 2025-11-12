<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <!-- Email Templates Table -->
        <div class="space-y-6">
            <livewire:datatable.email-template-datatable lazy />
        </div>
    </div>

    <!-- Test Email Modal -->
    <div id="testEmailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/20 p-4 backdrop-blur-md">
        <div class="flex max-w-md flex-col gap-4 overflow-hidden rounded-md border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-700">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
                <h3 class="font-semibold tracking-wide text-gray-700 dark:text-white">
                    {{ __('Send Test Email') }}
                </h3>
                <button onclick="closeTestEmailModal()" class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-4">
                <form id="testEmailForm">
                    <div class="mb-4">
                        <label for="testEmail" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Email Address') }}
                        </label>
                        <input type="email" id="testEmail" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white" 
                               placeholder="test@example.com">
                    </div>
                </form>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
                <button type="button" onclick="closeTestEmailModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    {{ __('Cancel') }}
                </button>
                <button type="button" onclick="sendTestEmail()" id="sendTestBtn"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {{ __('Send Test Email') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentTemplateUuid = null;

        function openTestEmailModal(uuid) {
            currentTemplateUuid = uuid;
            document.getElementById('testEmailModal').classList.remove('hidden');
            document.getElementById('testEmailModal').classList.add('flex');
        }

        function closeTestEmailModal() {
            document.getElementById('testEmailModal').classList.add('hidden');
            document.getElementById('testEmailModal').classList.remove('flex');
            document.getElementById('testEmail').value = '';
            currentTemplateUuid = null;
        }

        function sendTestEmail() {
            const email = document.getElementById('testEmail').value;
            const sendBtn = document.getElementById('sendTestBtn');
            
            if (!email) {
                alert('{{ __("Please enter an email address") }}');
                return;
            }

            if (!currentTemplateUuid) {
                alert('{{ __("Template not selected") }}');
                return;
            }

            sendBtn.disabled = true;
            sendBtn.textContent = '{{ __("Sending...") }}';

            fetch(`/admin/email-templates/${currentTemplateUuid}/send-test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    closeTestEmailModal();
                    window.location.reload();
                } else {
                    alert('{{ __("Failed to send test email") }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred while sending the test email") }}');
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.textContent = '{{ __("Send Test Email") }}';
            });
        }
    </script>
</x-layouts.backend-layout>
