{{--
    AI Command Modal - Agentic CMS Interface
    A beautiful command interface for users to interact with the AI system.
--}}
<template x-teleport="body">
    <div
        x-cloak
        x-show="aiModalOpen"
        x-transition.opacity.duration.200ms
        x-trap.inert.noscroll="aiModalOpen"
        x-on:keydown.esc.window="aiModalOpen = false; $dispatch('ai-modal-close')"
        x-on:click.self="aiModalOpen = false; $dispatch('ai-modal-close')"
        class="fixed inset-0 z-50 flex items-start justify-center pt-16 bg-black/30 backdrop-blur-sm p-4 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        aria-labelledby="ai-command-title"
    >
        <div
            x-data="aiCommandModal()"
            x-show="aiModalOpen"
            x-transition:enter="transition ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-2xl rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-blue-600 text-white">
                        <iconify-icon icon="lucide:sparkles" width="20" height="20"></iconify-icon>
                    </div>
                    <div>
                        <h3 id="ai-command-title" class="font-semibold text-gray-900 dark:text-white">
                            {{ __('AI Agent') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('What would you like to create today?') }}
                        </p>
                    </div>
                </div>
                <button
                    x-on:click="aiModalOpen = false; $dispatch('ai-modal-close')"
                    class="flex p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors dark:hover:bg-gray-700 dark:hover:text-gray-300"
                    aria-label="{{ __('Close') }}"
                >
                    <iconify-icon icon="lucide:x" width="20" height="20"></iconify-icon>
                </button>
            </div>

            {{-- Command Input --}}
            <div class="p-6">
                {{-- Not Configured Warning --}}
                <template x-if="!isConfigured && !loading">
                    <div class="mb-4 p-4 rounded-lg bg-amber-50 border border-amber-200 dark:bg-amber-900/20 dark:border-amber-700">
                        <div class="flex items-start gap-3">
                            <iconify-icon icon="lucide:alert-triangle" width="20" height="20" class="text-amber-600 dark:text-amber-400 mt-0.5"></iconify-icon>
                            <div>
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                    {{ __('AI not configured') }}
                                </p>
                                <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                    {{ __('Please configure your AI API key in') }}
                                    <a href="{{ route('admin.settings.index') }}?tab=integrations" class="underline font-medium">{{ __('Settings') }}</a>.
                                </p>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Command Form --}}
                <form @submit.prevent="executeCommand">
                    <div class="relative">
                        <textarea
                            x-model="command"
                            x-ref="commandInput"
                            @keydown.enter.meta.prevent="executeCommand"
                            @keydown.enter.ctrl.prevent="executeCommand"
                            :disabled="processing || !isConfigured"
                            rows="3"
                            class="form-control-textarea pr-24"
                            :class="{ 'border-red-400 dark:border-red-500 ring-2 ring-red-200 dark:ring-red-800': isRecording }"
                            :placeholder="isConfigured ? '{{ __('Describe what you want to create or do...') }}' : '{{ __('AI is not configured') }}'"
                        ></textarea>

                        {{-- Action buttons --}}
                        <div class="absolute right-3 bottom-3 flex items-center gap-2">
                            {{-- Voice button with inline listening indicator --}}
                            <template x-if="voiceSupported">
                                <div class="flex items-center gap-2">
                                    {{-- Listening text (shown when recording) --}}
                                    <span
                                        x-show="isRecording"
                                        x-cloak
                                        class="flex items-center gap-1.5 text-xs font-medium text-red-500 animate-pulse"
                                    >
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                        </span>
                                        {{ __('Listening...') }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="toggleVoiceRecording"
                                        :disabled="processing || !isConfigured"
                                        class="p-2 flex rounded-lg transition-all shadow-md"
                                        :class="isRecording
                                            ? 'bg-red-500 text-white hover:bg-red-600 animate-pulse'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                                        :title="isRecording ? '{{ __('Stop recording') }}' : '{{ __('Voice input') }}'"
                                    >
                                        <iconify-icon x-show="!isRecording" icon="lucide:mic" width="18" height="18"></iconify-icon>
                                        <iconify-icon x-show="isRecording" x-cloak icon="lucide:mic-off" width="18" height="18"></iconify-icon>
                                    </button>
                                </div>
                            </template>

                            {{-- Send button --}}
                            <button
                                type="submit"
                                :disabled="!command.trim() || processing || !isConfigured || isRecording"
                                class="p-2 flex bg-gradient-to-r from-purple-500 to-blue-600 text-white rounded-lg hover:from-purple-600 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md"
                                :class="{ 'animate-pulse': processing }"
                            >
                                <iconify-icon x-show="!processing" icon="lucide:send" width="18" height="18"></iconify-icon>
                                <iconify-icon x-show="processing" x-cloak icon="lucide:loader-2" width="18" height="18" class="animate-spin"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    {{-- Voice error message --}}
                    <template x-if="voiceError && !isRecording">
                        <div class="mt-2 flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
                            <iconify-icon icon="lucide:alert-circle" width="14" height="14"></iconify-icon>
                            <span x-text="voiceError"></span>
                            <button type="button" @click="voiceError = null" class="ml-auto text-red-500 hover:text-red-700">
                                <iconify-icon icon="lucide:x" width="12" height="12"></iconify-icon>
                            </button>
                        </div>
                    </template>

                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400" x-show="!isRecording">
                        <p>
                            <kbd class="px-1.5 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-700 rounded">{{ __('Cmd/Ctrl + Enter') }}</kbd> {{ __('to send') }}
                        </p>
                        <template x-if="voiceSupported">
                            <p class="flex items-center gap-1">
                                <iconify-icon icon="lucide:mic" width="12" height="12"></iconify-icon>
                                <kbd class="px-1.5 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-700 rounded">⌘⇧V</kbd>
                                {{ __('for voice') }}
                            </p>
                        </template>
                    </div>
                </form>

                {{-- Progress Section (While Processing) --}}
                <template x-if="processing">
                    <div class="mt-6 p-4 rounded-lg bg-gradient-to-br from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 border border-purple-200 dark:border-purple-700">
                        {{-- Current Step --}}
                        <div class="flex items-center gap-3 mb-4">
                            <div class="relative">
                                <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-800 flex items-center justify-center">
                                    <iconify-icon icon="lucide:loader-2" width="18" height="18" class="text-purple-600 dark:text-purple-300 animate-spin"></iconify-icon>
                                </div>
                                <div class="absolute -bottom-1 -right-1 -mt-2.5 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800 animate-pulse"></div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-purple-900 dark:text-purple-100" x-text="currentStep || '{{ __('Processing...') }}'"></p>
                                <p class="text-xs text-purple-600 dark:text-purple-400">{{ __('Please wait, this may take a moment') }}</p>
                            </div>
                        </div>

                        {{-- Completed Steps --}}
                        <template x-if="progressSteps.length > 0">
                            <div class="space-y-2 pt-3 border-t border-purple-200 dark:border-purple-700">
                                <template x-for="(step, index) in progressSteps" :key="index">
                                    <div class="flex items-center gap-2 text-sm">
                                        <iconify-icon icon="lucide:check-circle-2" width="16" height="16" class="text-green-500 shrink-0"></iconify-icon>
                                        <span class="text-gray-700 dark:text-gray-300" x-text="step.step"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Available Actions Section (Only when no result) --}}
                <template x-if="!processing && !result && actions.length > 0">
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('I can help you with:') }}
                            </p>
                        </div>

                        {{-- Action Cards --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="action in actions" :key="action.name">
                                <button
                                    type="button"
                                    @click="selectAction(action)"
                                    class="group flex items-start gap-3 p-4 text-left rounded-lg border border-gray-200 bg-white hover:border-purple-300 hover:bg-purple-50/50 transition-all dark:border-gray-600 dark:bg-gray-700/50 dark:hover:border-purple-500 dark:hover:bg-purple-900/20"
                                >
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                                         :class="getActionIconClass(action.name)">
                                        <iconify-icon :icon="getActionIcon(action.name)" width="20" height="20"></iconify-icon>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white text-sm group-hover:text-purple-700 dark:group-hover:text-purple-300 transition-colors" x-text="getActionTitle(action.name)"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2" x-text="action.description"></p>
                                    </div>
                                    <iconify-icon icon="lucide:chevron-right" width="16" height="16" class="text-gray-400 group-hover:text-purple-500 transition-colors mt-1"></iconify-icon>
                                </button>
                            </template>
                        </div>

                        {{-- Quick Examples --}}
                        <div class="mt-5 pt-5 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                                {{ __('Quick Examples') }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="example in quickExamples" :key="example">
                                    <button
                                        type="button"
                                        @click="command = example"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-gray-600 bg-gray-100 hover:bg-purple-100 hover:text-purple-700 rounded-full transition-colors dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-purple-900/30 dark:hover:text-purple-300"
                                    >
                                        <iconify-icon icon="lucide:message-square" width="12" height="12"></iconify-icon>
                                        <span x-text="example"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Result Section --}}
            <template x-if="result">
                <div class="border-t border-gray-200 dark:border-gray-700 p-6">
                    {{-- Status Header --}}
                    <div class="flex items-center gap-3 mb-4">
                        <template x-if="result.status === 'success'">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                <iconify-icon icon="lucide:check" width="20" height="20"></iconify-icon>
                            </div>
                        </template>
                        <template x-if="result.status === 'partial'">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                                <iconify-icon icon="lucide:alert-circle" width="20" height="20"></iconify-icon>
                            </div>
                        </template>
                        <template x-if="result.status === 'failed'">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                <iconify-icon icon="lucide:x" width="20" height="20"></iconify-icon>
                            </div>
                        </template>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white" x-text="message"></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="result.status === 'success' ? '{{ __('Task completed successfully') }}' : (result.status === 'partial' ? '{{ __('Partially completed') }}' : '{{ __('Task failed') }}')"></p>
                        </div>
                    </div>

                    {{-- Completed Steps --}}
                    <template x-if="result.completed_steps && result.completed_steps.length > 0">
                        <div class="mb-4 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">{{ __('What was done') }}</p>
                            <ul class="space-y-2">
                                <template x-for="step in result.completed_steps" :key="step">
                                    <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <iconify-icon icon="lucide:check-circle-2" width="16" height="16" class="text-green-500 mt-0.5 flex-shrink-0"></iconify-icon>
                                        <span x-text="step"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    {{-- Action Buttons --}}
                    <template x-if="result.actions && Object.keys(result.actions).length > 0">
                        <div class="space-y-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Next steps') }}</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="[label, url] in Object.entries(result.actions)" :key="url">
                                    <a
                                        :href="url"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-purple-500 to-blue-600 rounded-lg hover:from-purple-600 hover:to-blue-700 transition-all shadow-md hover:shadow-lg"
                                    >
                                        <iconify-icon icon="lucide:arrow-right" width="16" height="16"></iconify-icon>
                                        <span x-text="label"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- New Command Button --}}
                    <div class="mt-5 pt-5 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <button
                            type="button"
                            @click="resetForm"
                            class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400 font-medium transition-colors"
                        >
                            <iconify-icon icon="lucide:plus" width="16" height="16"></iconify-icon>
                            {{ __('Start new task') }}
                        </button>
                        <button
                            type="button"
                            @click="aiModalOpen = false; $dispatch('ai-modal-close')"
                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        >
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </template>

            {{-- Error Message --}}
            <template x-if="error && !result">
                <div class="border-t border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start gap-3 p-4 rounded-lg bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700">
                        <iconify-icon icon="lucide:alert-circle" width="20" height="20" class="text-red-600 dark:text-red-400 mt-0.5"></iconify-icon>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200" x-text="error"></p>
                            <button
                                type="button"
                                @click="error = null"
                                class="mt-2 text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium"
                            >
                                {{ __('Try again') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Footer --}}
            <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-6 py-3 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full" :class="isConfigured ? 'bg-green-500' : 'bg-amber-500'"></div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <template x-if="isConfigured">
                            <span>
                                {{ __('Connected to') }}
                                <span class="font-medium" x-text="provider === 'openai' ? 'OpenAI' : (provider === 'claude' ? 'Claude' : 'AI')"></span>
                            </span>
                        </template>
                        <template x-if="!isConfigured">
                            <span>{{ __('Not connected') }}</span>
                        </template>
                    </p>
                </div>
                <button
                    type="button"
                    @click="showCapabilities = !showCapabilities"
                    class="inline-flex items-center gap-1.5 text-xs text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 font-medium"
                >
                    <iconify-icon icon="lucide:info" width="14" height="14"></iconify-icon>
                    <span x-text="actions.length + ' {{ __('capabilities') }}'"></span>
                </button>
            </div>

            {{-- Capabilities Tooltip/Dropdown --}}
            <template x-if="showCapabilities">
                <div
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute bottom-16 right-6 w-64 p-3 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-10"
                    @click.away="showCapabilities = false"
                >
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        {{ __('Available capabilities') }}
                    </p>
                    <ul class="space-y-1.5">
                        <template x-for="action in actions" :key="action.name">
                            <li class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <iconify-icon :icon="getActionIcon(action.name)" width="14" height="14" class="text-purple-500"></iconify-icon>
                                <span x-text="getActionTitle(action.name)"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
function aiCommandModal() {
    return {
        command: '',
        processing: false,
        loading: true,
        error: null,
        result: null,
        message: '',
        actions: [],
        isConfigured: false,
        provider: 'openai',
        showCapabilities: false,

        // Progress tracking for streaming
        progressSteps: [],
        currentStep: '',

        // Voice input state
        isRecording: false,
        voiceSupported: false,
        voiceError: null,
        recognition: null,
        interimTranscript: '',
        pendingVoiceActivation: false, // Track if voice should start after config loads

        // User-friendly quick examples
        quickExamples: [
            '{{ __("Write about healthy lifestyle tips") }}',
            '{{ __("Create a welcome message") }}',
            '{{ __("Draft an about us page") }}',
        ],

        init() {
            this.loadStatus();
            this.initVoiceRecognition();

            // Listen for voice activation event from keyboard shortcut
            window.addEventListener('ai-voice-activate', () => {
                this.pendingVoiceActivation = true;
                this.$nextTick(() => this.tryStartPendingVoice());
            });

            // Listen for modal close event to stop voice recording
            window.addEventListener('ai-modal-close', () => {
                this.stopVoiceRecording();
                this.pendingVoiceActivation = false;
            });

            // Focus input when modal opens
            this.$watch('$root.aiModalOpen', (open) => {
                if (open) {
                    this.showCapabilities = false;
                    this.progressSteps = [];
                    this.currentStep = '';
                    this.$nextTick(() => {
                        if (!this.pendingVoiceActivation) {
                            this.$refs.commandInput?.focus();
                        }
                    });
                } else {
                    this.stopVoiceRecording();
                    this.pendingVoiceActivation = false;
                }
            });

            // Watch for config to load, then start voice if pending
            this.$watch('isConfigured', (configured) => {
                if (configured && this.pendingVoiceActivation) {
                    this.$nextTick(() => this.tryStartPendingVoice());
                }
            });
        },

        // Try to start voice recording if conditions are met
        tryStartPendingVoice() {
            if (this.pendingVoiceActivation && this.voiceSupported && this.isConfigured) {
                this.pendingVoiceActivation = false;
                this.startVoiceRecording();
            }
        },

        // Initialize Web Speech API
        initVoiceRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

            if (!SpeechRecognition) {
                this.voiceSupported = false;
                return;
            }

            this.voiceSupported = true;
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = true;
            this.recognition.interimResults = true;
            this.recognition.lang = document.documentElement.lang || 'en-US';

            this.recognition.onstart = () => {
                this.isRecording = true;
                this.voiceError = null;
                this.interimTranscript = '';
            };

            this.recognition.onresult = (event) => {
                let finalTranscript = '';
                let interim = '';

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalTranscript += transcript;
                    } else {
                        interim += transcript;
                    }
                }

                // Append final transcript to command
                if (finalTranscript) {
                    this.command = (this.command + ' ' + finalTranscript).trim();
                    this.interimTranscript = '';
                } else {
                    this.interimTranscript = interim;
                }
            };

            this.recognition.onerror = (event) => {
                this.isRecording = false;
                if (event.error === 'not-allowed') {
                    this.voiceError = '{{ __("Microphone access denied. Please allow microphone access.") }}';
                } else if (event.error === 'no-speech') {
                    this.voiceError = '{{ __("No speech detected. Please try again.") }}';
                } else if (event.error !== 'aborted') {
                    this.voiceError = '{{ __("Voice recognition error. Please try again.") }}';
                }
            };

            this.recognition.onend = () => {
                this.isRecording = false;
                this.interimTranscript = '';
            };
        },

        // Toggle voice recording
        toggleVoiceRecording() {
            if (!this.voiceSupported || !this.isConfigured || this.processing) return;

            if (this.isRecording) {
                this.stopVoiceRecording();
            } else {
                this.startVoiceRecording();
            }
        },

        startVoiceRecording() {
            if (!this.recognition) return;

            this.voiceError = null;
            try {
                this.recognition.start();
            } catch (e) {
                // Already started, ignore
            }
        },

        stopVoiceRecording() {
            if (!this.recognition) return;

            try {
                this.recognition.stop();
            } catch (e) {
                // Already stopped, ignore
            }
            this.isRecording = false;
            this.interimTranscript = '';
        },

        async loadStatus() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("admin.ai.command.status") }}');
                const data = await response.json();

                if (data.success) {
                    this.isConfigured = data.data.configured;
                    this.provider = data.data.provider;
                    this.actions = data.data.actions || [];
                }
            } catch (e) {
                console.error('Failed to load AI status:', e);
            } finally {
                this.loading = false;
            }
        },

        // Get user-friendly action title
        getActionTitle(actionName) {
            const titles = {
                'posts.create': '{{ __("Create Content") }}',
                'posts.generate_seo': '{{ __("Optimize for SEO") }}',
                'pages.create': '{{ __("Create Page") }}',
                'content.improve': '{{ __("Improve Content") }}',
                'content.translate': '{{ __("Translate") }}',
            };
            return titles[actionName] || this.formatActionName(actionName);
        },

        // Format action name as fallback (improved for underscores)
        formatActionName(name) {
            return name.split(/[._]/).map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(' ');
        },

        // Get icon for action
        getActionIcon(actionName) {
            const icons = {
                'posts.create': 'lucide:file-text',
                'posts.generate_seo': 'lucide:search',
                'pages.create': 'lucide:layout',
                'content.improve': 'lucide:wand-2',
                'content.translate': 'lucide:languages',
            };
            return icons[actionName] || 'lucide:sparkles';
        },

        // Get icon background class
        getActionIconClass(actionName) {
            const classes = {
                'posts.create': 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                'posts.generate_seo': 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
                'pages.create': 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
                'content.improve': 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
                'content.translate': 'bg-pink-100 text-pink-600 dark:bg-pink-900/30 dark:text-pink-400',
            };
            return classes[actionName] || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
        },

        // Pre-fill command when action is selected
        selectAction(action) {
            const prompts = {
                'posts.create': '{{ __("Write a blog post about ") }}',
                'posts.generate_seo': '{{ __("Improve SEO for my latest post") }}',
                'pages.create': '{{ __("Create a page for ") }}',
                'content.improve': '{{ __("Improve the content of ") }}',
                'content.translate': '{{ __("Translate my content to ") }}',
            };
            this.command = prompts[action.name] || '';
            this.$refs.commandInput?.focus();
        },

        async executeCommand() {
            if (!this.command.trim() || this.processing || !this.isConfigured) return;

            this.processing = true;
            this.error = null;
            this.result = null;
            this.progressSteps = [];
            this.currentStep = '{{ __("Starting...") }}';

            try {
                const response = await fetch('{{ route("admin.ai.command.process-stream") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'text/event-stream',
                    },
                    body: JSON.stringify({ command: this.command }),
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                // Process SSE stream
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';

                const processBuffer = (text) => {
                    const lines = text.split('\n');
                    let currentEvent = null;

                    for (const line of lines) {
                        if (line.startsWith('event: ')) {
                            currentEvent = line.substring(7).trim();
                        } else if (line.startsWith('data: ') && currentEvent) {
                            try {
                                const data = JSON.parse(line.substring(6));
                                this.handleStreamEvent(currentEvent, data);
                            } catch (e) {
                                console.warn('Failed to parse SSE data:', e);
                            }
                            currentEvent = null;
                        }
                    }
                };

                while (true) {
                    const { done, value } = await reader.read();

                    if (value) {
                        buffer += decoder.decode(value, { stream: !done });
                    }

                    if (done) {
                        // Process any remaining data in buffer
                        if (buffer.trim()) {
                            processBuffer(buffer);
                        }
                        break;
                    }

                    // Process complete events (separated by double newlines)
                    const parts = buffer.split('\n\n');
                    buffer = parts.pop() || '';

                    for (const part of parts) {
                        if (part.trim()) {
                            processBuffer(part);
                        }
                    }
                }
            } catch (e) {
                console.error('AI command error:', e);
                this.error = '{{ __("Failed to process command. Please try again.") }}';
            } finally {
                this.processing = false;
                this.currentStep = '';
            }
        },

        handleStreamEvent(event, data) {
            switch (event) {
                case 'progress':
                    this.currentStep = data.step;
                    // Add to progress steps if completed
                    if (data.status === 'completed') {
                        this.progressSteps.push({
                            step: data.step,
                            status: 'completed'
                        });
                    }
                    break;

                case 'complete':
                    if (data.success) {
                        this.result = data.data;
                        this.message = data.message;
                    } else {
                        this.error = data.message || '{{ __("Something went wrong. Please try again.") }}';
                    }
                    break;

                case 'error':
                    this.error = data.message || '{{ __("An error occurred.") }}';
                    break;
            }
        },

        resetForm() {
            this.command = '';
            this.result = null;
            this.error = null;
            this.message = '';
            this.progressSteps = [];
            this.currentStep = '';
            this.$nextTick(() => {
                this.$refs.commandInput?.focus();
            });
        }
    };
}
</script>
