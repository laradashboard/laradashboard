<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <h3 class="text-base font-medium text-gray-700 dark:text-white/90">{{ __('Email Template Details') }}</h3>
                        <div class="flex items-center gap-1.5">
                            @if($template->is_default)
                                <span class="badge bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ __('Default') }}
                                </span>
                            @endif
                            @if($template->is_active)
                                <span class="badge bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    {{ __('Active') }}
                                </span>
                            @else
                                <span class="badge bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    {{ __('Inactive') }}
                                </span>
                            @endif
                            <span class="badge">
                                {{ $template->type->label() }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.email-templates.preview-page', $template->uuid) }}" target="_blank" class="btn-default">
                            <iconify-icon icon="lucide:eye" class="mr-2"></iconify-icon>
                            {{ __('Preview') }}
                        </a>
                        <button onclick="openTestEmailModal()" class="btn-default">
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

            <!-- Template Name and Meta Information -->
            <div class="mb-6">
                <h4 class="text-lg font-medium text-gray-700 dark:text-white/90 mb-3">{{ $template->name }}</h4>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <div class="flex items-center">
                        <iconify-icon icon="lucide:user" class="mr-1"></iconify-icon>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Created By:') }} {{ $template->creator->name ?? __('System') }}</span>
                    </div>
                    <div class="flex items-center">
                        <iconify-icon icon="lucide:calendar" class="mr-1"></iconify-icon>
                        {{ __('Created:') }} {{ $template->created_at->format('M d, Y h:i A') }}
                    </div>
                    @if($template->created_at != $template->updated_at)
                        <div class="flex items-center">
                            <iconify-icon icon="lucide:clock" class="mr-1"></iconify-icon>
                            {{ __('Updated:') }} {{ $template->updated_at->format('M d, Y h:i A') }}
                        </div>
                    @endif
                </div>
                @if($template->description)
                    <div class="mt-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-md text-gray-700 dark:text-gray-300">
                        {{ $template->description }}
                    </div>
                @endif
            </div>

            <!-- Subject -->
            <div class="mb-6">
                <h4 class="text-lg font-medium text-gray-700 dark:text-white/90 mb-2">{{ __('Subject') }}</h4>
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-md text-gray-700 dark:text-gray-300">
                    {{ $template->subject }}
                </div>
            </div>

            <!-- Email Content -->
            <div class="mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button onclick="switchTab('html')" id="tab-html" class="border-b-2 border-primary py-2 px-1 text-sm font-medium text-primary">
                            {{ __('HTML Content') }}
                        </button>
                        @if($template->body_text)
                        <button onclick="switchTab('text')" id="tab-text" class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                            {{ __('Plain Text') }}
                        </button>
                        @endif
                        <button onclick="switchTab('source')" id="tab-source" class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                            {{ __('Source Code') }}
                        </button>
                    </nav>
                </div>

                <!-- HTML Preview Tab -->
                <div id="content-html">
                    <div class="prose max-w-none dark:prose-invert prose-headings:font-medium prose-headings:text-gray-700 dark:prose-headings:text-white/90 prose-p:text-gray-700 dark:prose-p:text-gray-300">
                        {!! $template->body_html !!}
                    </div>
                </div>

                <!-- Plain Text Tab -->
                @if($template->body_text)
                <div id="content-text" class="hidden">
                    <pre class="whitespace-pre-wrap font-mono text-sm bg-gray-50 dark:bg-gray-800 p-4 rounded-md text-gray-700 dark:text-gray-300">{{ $template->body_text }}</pre>
                </div>
                @endif

                <!-- Source Code Tab -->
                <div id="content-source" class="hidden">
                    <pre class="whitespace-pre-wrap font-mono text-xs bg-gray-50 dark:bg-gray-800 p-4 rounded-md text-gray-700 dark:text-gray-300 overflow-auto max-h-[500px]"><code>{{ $template->body_html }}</code></pre>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="mb-6">
                <h4 class="text-lg font-medium text-gray-700 dark:text-white/90 mb-3">{{ __('Usage Statistics') }}</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-md p-4 text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Campaigns') }}</div>
                        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $template->campaigns ? $template->campaigns->count() : 0 }}</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/30 rounded-md p-4 text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Sent') }}</div>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $template->campaigns ? $template->campaigns->sum('sent_count') : 0 }}</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-md p-4 text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Last Used') }}</div>
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $template->campaigns && $template->campaigns->count() > 0 ? $template->campaigns->sortByDesc('created_at')->first()->created_at->format('M j') : '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </x-card>
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
        function switchTab(tabName) {
            document.querySelectorAll('[id^="content-"]').forEach(el => {
                el.classList.add('hidden');
            });
            
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.classList.remove('border-primary', 'text-primary');
                el.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'dark:text-gray-400', 'dark:hover:text-gray-300');
            });
            
            const selectedTab = document.getElementById('tab-' + tabName);
            selectedTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'dark:text-gray-400', 'dark:hover:text-gray-300');
            selectedTab.classList.add('border-primary', 'text-primary');
        }

        function openTestEmailModal() {
            document.getElementById('testEmailModal').classList.remove('hidden');
            document.getElementById('testEmailModal').classList.add('flex');
        }

        function closeTestEmailModal() {
            document.getElementById('testEmailModal').classList.add('hidden');
            document.getElementById('testEmailModal').classList.remove('flex');
            document.getElementById('testEmail').value = '';
        }

        function sendTestEmail() {
            const email = document.getElementById('testEmail').value;
            const sendBtn = document.getElementById('sendTestBtn');
            
            if (!email) {
                alert('{{ __("Please enter an email address") }}');
                return;
            }

            sendBtn.disabled = true;
            sendBtn.textContent = '{{ __("Sending...") }}';

            fetch('{{ route("admin.email-templates.send-test", $template->uuid) }}', {
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
                    // Refresh the page after successful email send
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