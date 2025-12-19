<div class="relative group">
    <a 
        href="{{ route('admin.email-templates.show', ['email_template' => $emailTemplate->id]) }}"
        class="block w-20 h-20 bg-white dark:bg-gray-900 rounded border-2 border-gray-200 dark:border-gray-700 overflow-hidden cursor-pointer hover:border-primary hover:shadow-lg transition-all relative"
    >
        @if($emailTemplate->body_html)
            <!-- Real HTML Preview (scaled down) -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none email-template-preview">
                <div class="origin-top-left" style="transform: scale(0.13); width: 100%;">
                    <div class="bg-white">
                        {!! $emailTemplate->renderContent() !!}
                    </div>
                </div>
            </div>
            <!-- Overlay to prevent interaction -->
            <div class="absolute inset-0 bg-transparent"></div>
        @else
            <!-- Fallback icon if no HTML content -->
            <div class="flex items-center justify-center h-full">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
    </a>
    
    <!-- Hover tooltip with larger preview -->
    <div class="absolute left-0 top-full mt-2 hidden group-hover:block z-50 pointer-events-none">
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-lg shadow-2xl overflow-hidden" style="width: 200px; height: 300px;">
            @if($emailTemplate->body_html)
                <div class="w-full h-full overflow-hidden">
                    <div class="origin-top-left" style="transform: scale(0.3); width: 333.33%; height: 333.33%;">
                        <div class="bg-white" style="min-height: 100vh;">
                            {!! $emailTemplate->renderContent() !!}
                        </div>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                    {{ __('No preview available') }}
                </div>
            @endif
        </div>
        <div class="mt-1 bg-gray-900 text-white text-xs rounded py-1 px-2 text-center">
            {{ __('Click to view full template') }}
        </div>
    </div>
</div>
