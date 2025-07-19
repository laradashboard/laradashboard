@props([
    'name',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'class' => '',
    'id' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="w-full flex flex-col gap-1">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif

    <div x-data="{ showPassword: false }" class="relative">
        <input
            :type="showPassword ? 'text' : 'password'"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            class="form-control {{ $class }}" />

        <button type="button" @click="showPassword = !showPassword" class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-300 flex items-center justify-center w-6 h-6">
            <iconify-icon x-show="!showPassword" icon="lucide:eye" width="20" height="20" class="text-[#98A2B3]"></iconify-icon>
            <iconify-icon x-show="showPassword" icon="lucide:eye-off" width="20" height="20" class="text-[#98A2B3]" style="display: none;"></iconify-icon>
        </button>
    </div>
</div>
