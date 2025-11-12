<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="p-3 mx-auto md:p-4">
        <div class="grid grid-cols-4 gap-4 mt-3">
            <!-- Main Content -->
            <div class="col-span-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                                {{ $template->name }}
                            </h2>
                            <div class="flex items-center ml-3 space-x-1">
                                @if($template->is_default)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ __('Default') }}
                                    </span>
                                @endif
                                @if($template->is_active)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ __('Inactive') }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-indigo-50 dark:bg-indigo-100 text-primary dark:text-indigo-400">
                                    {{ $template->type->label() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-4 py-2">
                        <!-- Meta information -->
                        <div class="flex flex-wrap text-xs mb-2">
                            <div class="mr-4">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Created:') }}</span>
                                <span class="text-gray-900 dark:text-white ml-1">{{ $template->created_at->format('M j, Y') }}</span>
                            </div>
                            <div class="mr-4">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Updated:') }}</span>
                                <span class="text-gray-900 dark:text-white ml-1">{{ $template->updated_at->format('M j, Y') }}</span>
                            </div>
                            <div class="mr-4">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Created By:') }}</span>
                                <span class="text-gray-900 dark:text-white ml-1">{{ $template->creator->name ?? __('System') }}</span>
                            </div>
                            @if($template->description)
                                <div class="w-full mt-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Description:') }}</span>
                                    <span class="text-gray-900 dark:text-white ml-1">{{ $template->description }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Email Content Tabs -->
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <nav class="flex space-x-4" aria-label="Tabs">
                                <button onclick="switchTab('subject')" id="tab-subject" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 py-2 px-1 border-b-2 font-medium text-xs">
                                    {{ __('Subject') }}
                                </button>
                                <button onclick="switchTab('html')" id="tab-html" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-2 px-1 border-b-2 font-medium text-xs">
                                    {{ __('HTML Content') }}
                                </button>
                                @if($template->body_text)
                                <button onclick="switchTab('text')" id="tab-text" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-2 px-1 border-b-2 font-medium text-xs">
                                    {{ __('Plain Text') }}
                                </button>
                                @endif
                            </nav>
                        </div>
                            
                        <!-- Tab Content -->
                        <div class="mt-2">
                            <!-- Subject Tab -->
                            <div id="content-subject" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-sm">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $template->subject }}</p>
                            </div>
                            
                            <!-- HTML Content Tab -->
                            <div id="content-html" class="hidden">
                                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 px-3 py-1.5 border-b border-gray-200 dark:border-gray-700">
                                        <h3 class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ __('HTML Content') }}</h3>
                                        <button type="button" onclick="toggleHtmlView()" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium flex items-center">
                                            <iconify-icon icon="feather:code" class="mr-1"></iconify-icon>
                                            <span id="toggle-text">{{ __('Source') }}</span>
                                        </button>
                                    </div>
                                    <div class="p-3 max-h-[400px] overflow-auto">
                                        <div id="html-preview" class="prose dark:prose-invert max-w-none text-sm">
                                            {!! $template->body_html !!}
                                        </div>
                                        <div id="html-source" class="hidden">
                                            <pre class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-2 rounded overflow-auto whitespace-pre-wrap"><code>{{ $template->body_html }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Plain Text Tab -->
                            @if($template->body_text)
                            <div id="content-text" class="hidden">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 max-h-[400px] overflow-auto">
                                    <pre class="text-gray-900 dark:text-white whitespace-pre-wrap font-mono">{{ $template->body_text }}</pre>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Usage Statistics -->
                        <div class="mt-3">
                            <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Usage Statistics') }}</h3>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-lg p-3 flex flex-col items-center justify-center">
                                    <span class="text-indigo-500 dark:text-indigo-400">{{ __('Campaigns') }}</span>
                                    <span class="text-xl font-bold text-indigo-500 dark:text-indigo-300">{{ $template->campaigns ? $template->campaigns->count() : 0 }}</span>
                                </div>
                                <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-3 flex flex-col items-center justify-center">
                                    <span class="text-green-500 dark:text-green-400">{{ __('Sent') }}</span>
                                    <span class="text-xl font-bold text-green-700 dark:text-green-300">{{ $template->campaigns ? $template->campaigns->sum('sent_count') : 0 }}</span>
                                </div>
                                <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3 flex flex-col items-center justify-center">
                                    <span class="text-blue-500 dark:text-blue-400">{{ __('Last Used') }}</span>
                                    <span class="text-xl font-bold text-blue-700 dark:text-blue-300">
                                        {{ $template->campaigns && $template->campaigns->count() > 0 ? $template->campaigns->sortByDesc('created_at')->first()->created_at->format('M j') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Actions Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-xs font-medium text-gray-900 dark:text-white">{{ __('Actions') }}</h3>
                    </div>
                    <div class="p-2 space-y-2">
                        <a href="{{ route('admin.email-templates.preview-page', $template->uuid) }}" target="_blank" 
                           class="w-full flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                            <iconify-icon icon="feather:eye" class="mr-1"></iconify-icon>
                            {{ __('Preview Email') }}
                        </a>
                        
                        <button onclick="openTestEmailModal()" 
                           class="w-full flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                            <iconify-icon icon="feather:mail" class="mr-1"></iconify-icon>
                            {{ __('Send Test Email') }}
                        </button>
                        
                        <a href="{{ route('admin.email-templates.edit', $template->id) }}" 
                           class="w-full flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                            <iconify-icon icon="feather:edit-2" class="mr-1"></iconify-icon>
                            {{ __('Edit') }}
                        </a>

                        <form action="{{ route('admin.email-templates.destroy', $template->uuid) }}" 
                              method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                                <iconify-icon icon="feather:trash-2" class="mr-1"></iconify-icon>
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            switchTab('subject');
        });
        
        function switchTab(tabName) {
            document.querySelectorAll('[id^="content-"]').forEach(el => {
                el.classList.add('hidden');
            });
            
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.classList.remove('border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                el.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            
            const selectedTab = document.getElementById('tab-' + tabName);
            selectedTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            selectedTab.classList.add('border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
        }

        function toggleHtmlView() {
            const preview = document.getElementById('html-preview');
            const source = document.getElementById('html-source');
            const toggleText = document.getElementById('toggle-text');

            if (preview.classList.contains('hidden')) {
                preview.classList.remove('hidden');
                source.classList.add('hidden');
                toggleText.textContent = '{{ __("Show Source") }}';
            } else {
                preview.classList.add('hidden');
                source.classList.remove('hidden');
                toggleText.textContent = '{{ __("Show Preview") }}';
            }
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