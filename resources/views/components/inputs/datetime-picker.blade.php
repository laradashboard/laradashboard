@props([
    'id' => 'datetime-picker',
    'name' => 'datetime',
    'label' => 'Date and Time',
    'value' => '',
    'required' => false,
    'placeholder' => 'Select date and time',
    'minDate' => null,
    'maxDate' => null,
    'enableTime' => true,
    'dateFormat' => 'Y-m-d H:i',
    'altFormat' => 'F j, Y at h:i K',
    'showAltFormat' => true,
    'class' => '',
    'helpText' => '',
])

<div x-data="{
    init() {
        const options = {
            enableTime: {{ $enableTime ? 'true' : 'false' }},
            dateFormat: '{{ $dateFormat }}',
            altInput: {{ $showAltFormat ? 'true' : 'false' }},
            altFormat: '{{ $altFormat }}',
            time_24hr: false,
            defaultDate: '{{ $value }}',
            minDate: {{ $minDate ? '\'' . $minDate . '\'' : 'null' }},
            maxDate: {{ $maxDate ? '\'' . $maxDate . '\'' : 'null' }},
            disableMobile: true,
            static: true,
            position: 'auto',
            locale: {
                firstDayOfWeek: 1
            },
            onChange: function(selectedDates, dateStr, instance) {
                // Dispatch an input event to ensure Alpine.js and other listeners are notified
                instance.element.dispatchEvent(new Event('input', { bubbles: true }));
            },
            // Fix for the form validation issue with unnamed inputs
            onReady: function(selectedDates, dateStr, instance) {
                // Add names to hour and minute inputs to prevent validation errors
                const hourInput = instance.hourElement;
                const minuteInput = instance.minuteElement;

                if (hourInput) {
                    hourInput.name = '{{ $name }}_hour';
                    hourInput.setAttribute('form', 'none'); // Prevent it from being included in form submission
                }

                if (minuteInput) {
                    minuteInput.name = '{{ $name }}_minute';
                    minuteInput.setAttribute('form', 'none'); // Prevent it from being included in form submission
                }
            }
        };

        flatpickr(this.$refs.datetimePicker, options);
    }
}">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <iconify-icon icon="lucide:calendar" class="text-gray-400 dark:text-gray-500 z-1"></iconify-icon>
        </div>
        <input x-ref="datetimePicker" type="text" id="{{ $id }}" name="{{ $name }}"
            value="{{ $value ?: now()->format($dateFormat) }}" placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => 'form-control !ps-10' . $class]) }} />
    </div>

    @if ($helpText)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">{{ $helpText }}</p>
    @endif
</div>
