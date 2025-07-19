<div id="add-language-modal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden">
    <div class="relative p-4 w-full max-w-md">
        <!-- Modal content -->
        <div class="relative bg-white rounded-md shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-white">
                    {{ __('Add New Language') }}
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-700 rounded-md text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="add-language-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">{{ __('Close modal') }}</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-6 space-y-6">
                <form action="{{ route('admin.translations.create') }}" method="POST" id="add-language-form">
                    @csrf
                    <div class="mb-4">
                        <label for="language-code" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                            {{ __('Select Language') }}
                        </label>
                        <select id="language-code" name="language_code" class="h-11 w-full rounded-md border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" required>
                            <option value="">{{ __('Select a language') }}</option>
                            @foreach($allLanguages as $code => $languageName)
                                @if(!array_key_exists($code, $languages))
                                    <option value="{{ $code }}">{{ $languageName }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="translation-group" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                            {{ __('Translation Group') }}
                        </label>
                        <select id="translation-group" name="group" class="h-11 w-full rounded-md border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" required>
                            <option value="json" selected>{{ __('General') }}</option>
                            @foreach($groups as $key => $name)
                                @if($key !== 'json')
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <!-- Modal footer -->
            <div class="flex items-center p-4 space-x-2 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button type="submit" class="btn-primary">
                    <iconify-icon icon="lucide:plus-circle" class="mr-2"></iconify-icon>{{ __('Add Language') }}
                </button>
                <button data-modal-hide="add-language-modal" type="button" class="btn-default">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
