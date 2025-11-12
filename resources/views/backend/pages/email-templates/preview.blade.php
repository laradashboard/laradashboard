<x-layouts.backend-layout :breadcrumbs="$breadcrumbs ?? ['title' => __('Email Preview')]">
    <x-card>
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ $template->name }} - {{ __('Email Preview') }}
            </h3>
        </x-slot>

        <div class="space-y-4">
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <strong class="text-gray-700 dark:text-gray-300">{{ __('Subject') }}:</strong>
                <span class="ml-2 text-gray-900 dark:text-white">{{ $rendered['subject'] }}</span>
            </div>

            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('html')" class="preview-tab active border-b-2 border-primary py-2 px-1 text-sm font-medium text-primary">
                        {{ __('HTML Preview') }}
                    </button>
                    <button onclick="showTab('text')" class="preview-tab border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                        {{ __('Plain Text') }}
                    </button>
                </nav>
            </div>

            <div id="html-tab" class="preview-tab-content active">
                <div class="prose max-w-none dark:prose-invert">
                    {!! $rendered['body_html'] !!}
                </div>
            </div>

            <div id="text-tab" class="preview-tab-content hidden">
                <pre class="whitespace-pre-wrap font-mono text-sm bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">{{ $rendered['body_text'] }}</pre>
            </div>
        </div>
    </x-card>

    <script>
        function showTab(tab) {
            document.querySelectorAll('.preview-tab').forEach(t => {
                t.classList.remove('active', 'border-primary', 'text-primary');
                t.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            document.querySelectorAll('.preview-tab-content').forEach(c => {
                c.classList.add('hidden');
                c.classList.remove('active');
            });
            
            event.target.classList.add('active', 'border-primary', 'text-primary');
            event.target.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById(tab + '-tab').classList.remove('hidden');
            document.getElementById(tab + '-tab').classList.add('active');
        }
    </script>
</x-layouts.backend-layout>