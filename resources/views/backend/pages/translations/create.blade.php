<div id="add-language-modal" @keydown.escape.window="addLanguageModalOpen = false">
    <!-- Overlay Background -->
    <div x-show="addLanguageModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="addLanguageModalOpen = false"
         class="fixed inset-0 bg-gray-900/30 backdrop-blur-sm z-40"
         style="display: none;">
    </div>

    <!-- Right Side Drawer -->
    <div x-show="addLanguageModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         @click.stop
         class="fixed top-0 right-0 bottom-0 w-full sm:w-[400px] max-w-full z-50 flex flex-col bg-white dark:bg-gray-800 shadow-xl border-l border-gray-200 dark:border-gray-700"
         style="display: none;">

        <!-- Header -->
        <div class="px-6 py-4 flex justify-between items-center border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('Add New Language') }}
            </h3>
            <button type="button" @click="addLanguageModalOpen = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6">
            <form
                action="{{ route('admin.translations.create') }}"
                method="POST"
                id="add-language-form"
            >
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="language-code" class="form-label">
                            {{ __('Select Language') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="language-code" name="language_code" class="form-control" required>
                            <option value="">{{ __('Select a language') }}</option>
                            @foreach($allLanguages as $code => $languageName)
                                @if(!array_key_exists($code, $languages))
                                    <option value="{{ $code }}">{{ $languageName }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="translation-group" class="form-label">
                            {{ __('Translation Group') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="translation-group" name="group" class="form-control" required>
                            <option value="json" selected>{{ __('General') }}</option>
                            @foreach($groups as $key => $name)
                                @if($key !== 'json')
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
            <div class="flex justify-between gap-3">
                <button type="button" class="btn-default" @click="addLanguageModalOpen = false">
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="btn-primary" @click="document.getElementById('add-language-form').submit()">
                    <iconify-icon icon="lucide:plus-circle" class="mr-2"></iconify-icon>{{ __('Add Language') }}
                </button>
            </div>
        </div>
    </div>
</div>
