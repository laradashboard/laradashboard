@props([
    'name',
    'value' => '',
])

<input type="hidden"
       name="{{ $name }}"
       value="{{ old($name, $value) }}"
       {{ $attributes }}>