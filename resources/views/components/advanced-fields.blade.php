@props([
    'postMeta' => []
])

<div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
     x-data="{ open: false }">
    <button type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between p-5 text-left">
        <h3 class="text-lg font-medium text-gray-700 dark:text-white">{{ __('Advanced Fields') }}</h3>
        <svg class="h-5 w-5 transform transition-transform duration-200 dark:text-gray-300"
             :class="{ 'rotate-180': open }"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-y-95"
         x-transition:enter-end="opacity-100 transform scale-y-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-y-100"
         x-transition:leave-end="opacity-0 transform scale-y-95"
         class="border-t border-gray-100 dark:border-gray-800">
        <div class="p-5">
            @php
                $metaJson = json_encode($postMeta, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                $fieldTypes = ld_apply_filters('advanced_fields_types', [
                    ['value' => 'input', 'label' => __('Text Input')],
                    ['value' => 'textarea', 'label' => __('Textarea')],
                    ['value' => 'number', 'label' => __('Number')],
                    ['value' => 'email', 'label' => __('Email')],
                    ['value' => 'url', 'label' => __('URL')],
                    ['value' => 'date', 'label' => __('Date')],
                    ['value' => 'checkbox', 'label' => __('Checkbox')],
                ]);
            @endphp

            <div x-data="advancedFields({{ $metaJson }})"
                 x-init="init()"
                 class="space-y-6">
                <!-- Fields container -->
                <div x-show="initialized && fields.length > 0" class="space-y-3">
                    <template x-for="(field, index) in fields" :key="`field-${index}`">
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-md">
                            <div class="flex-1 space-y-3">
                                <!-- Row 1: Meta Key and Type -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <!-- Meta Key -->
                                    <div>
                                        <input type="text"
                                               x-model="field.key"
                                               :name="`meta_keys[${index}]`"
                                               placeholder="{{ __('Meta Key') }}"
                                               class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    </div>

                                    <!-- Field Type -->
                                    <div>
                                        <select x-model="field.type"
                                               :name="`meta_types[${index}]`"
                                               class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                            @foreach($fieldTypes as $type)
                                                <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Row 2: Meta Value (Dynamic based on type) -->
                                <div>
                                    <!-- Text Input Type -->
                                    <template x-if="!field.type || field.type === 'input' || field.type === 'text'">
                                        <input type="text"
                                               x-model="field.value"
                                               :name="`meta_values[${index}]`"
                                               :placeholder="field.default_value || '{{ __('Meta Value') }}'"
                                               class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    </template>

                                    <!-- Textarea Type -->
                                    <template x-if="field.type === 'textarea'">
                                        <textarea x-model="field.value"
                                                 :name="`meta_values[${index}]`"
                                                 :placeholder="field.default_value || '{{ __('Meta Value') }}'"
                                                 rows="3"
                                                 class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                                    </template>

                                    <!-- Number Type -->
                                    <template x-if="field.type === 'number'">
                                        <input type="number"
                                               x-model="field.value"
                                               :name="`meta_values[${index}]`"
                                               :placeholder="field.default_value || '{{ __('Meta Value') }}'"
                                               class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    </template>

                                    <!-- Email Type -->
                                    <template x-if="field.type === 'email'">
                                        <input type="email"
                                               x-model="field.value"
                                               :name="`meta_values[${index}]`"
                                               :placeholder="field.default_value || '{{ __('Meta Value') }}'"
                                               class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    </template>

                                    <!-- URL Type -->
                                    <template x-if="field.type === 'url'">
                                        <input type="url"
                                               x-model="field.value"
                                               :name="`meta_values[${index}]`"
                                               :placeholder="field.default_value || '{{ __('Meta Value') }}'"
                                               class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    </template>

                                    <!-- Date Type -->
                                    <template x-if="field.type === 'date'">
                                        <input type="date"
                                               x-model="field.value"
                                               :name="`meta_values[${index}]`"
                                               class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    </template>

                                    <!-- Checkbox Type -->
                                    <template x-if="field.type === 'checkbox'">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox"
                                                   x-model="field.value"
                                                   :name="`meta_values[${index}]`"
                                                   value="1"
                                                   class="form-checkbox h-4 w-4 text-brand-500 border-gray-300 rounded focus:ring-brand-400 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-brand-500">
                                            <label class="text-sm text-gray-700 dark:text-gray-300">{{ __('Check to enable') }}</label>
                                        </div>
                                    </template>

                                    <!-- Select Type -->
                                    <template x-if="field.type === 'select'">
                                        <div class="space-y-2">
                                            <select x-model="field.value"
                                                   :name="`meta_values[${index}]`"
                                                   class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                                <option value="">{{ __('Select an option') }}</option>
                                                <template x-for="option in (field.options || [])" :key="option">
                                                    <option :value="option" x-text="option"></option>
                                                </template>
                                            </select>
                                            <input type="text"
                                                   x-model="field.optionsText"
                                                   placeholder="{{ __('Options (comma separated): Option 1, Option 2, Option 3') }}"
                                                   @input="field.options = $el.value.split(',').map(opt => opt.trim()).filter(opt => opt)"
                                                   class="w-full rounded-md border border-gray-300 bg-transparent px-3 py-2 text-xs text-gray-600 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        </div>
                                    </template>

                                    <!-- Hidden fields for default_value -->
                                    <input type="hidden" :name="`meta_default_values[${index}]`" x-model="field.default_value">
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 pt-2">
                                <!-- Add Button -->
                                <button type="button"
                                        @click="addField()"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-500 text-white hover:bg-brand-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>

                                <!-- Remove Button -->
                                <button type="button"
                                        @click="removeField(index)"
                                        x-show="fields.length > 1"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Add First Field Button (when no fields exist) -->
                <div x-show="initialized && fields.length === 0" class="text-center py-6">
                    <button type="button"
                            @click="addField()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('Add Meta Field') }}
                    </button>
                </div>

                <!-- Loading state -->
                <div x-show="!initialized" class="text-center py-6">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500 mx-auto"></div>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Loading...') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function advancedFields(initialMeta = {}) {
    return {
        fields: [],
        initialized: false,

        init() {
            // Convert initial meta object to array format
            if (Object.keys(initialMeta).length > 0) {
                this.fields = Object.entries(initialMeta).map(([key, data]) => {
                    if (typeof data === 'object' && data !== null && data.value !== undefined) {
                        return {
                            key: key,
                            value: data.value || '',
                            type: data.type || 'input',
                            default_value: data.default_value || '',
                            options: data.options || [],
                            optionsText: Array.isArray(data.options) ? data.options.join(', ') : ''
                        };
                    } else {
                        return {
                            key: key,
                            value: data || '',
                            type: 'input',
                            default_value: '',
                            options: [],
                            optionsText: ''
                        };
                    }
                });
            } else {
                // Start with one empty field
                this.addField();
            }

            this.initialized = true;
        },

        addField() {
            this.fields.push({
                key: '',
                value: '',
                type: 'input',
                default_value: '',
                options: [],
                optionsText: ''
            });
        },

        removeField(index) {
            if (this.fields.length > 1) {
                this.fields.splice(index, 1);
            }
        }
    };
}
</script>
@endpush
