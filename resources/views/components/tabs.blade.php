@props(['tabs' => [], 'active' => null])

<nav class="-mb-px flex space-x-8" aria-label="{{ __('Tabs') }}">
    @foreach($tabs as $tab)
        @php
            $id = $tab['id'] ?? ($tab['name'] ?? null);
            $label = $tab['label'] ?? ($tab['title'] ?? 'Tab');
        @endphp

        <button
            x-on:click="active='{{ $id }}'"
            id="tab-{{ $id }}"
            :class="active === '{{ $id }}' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
            class="border-b-2 py-2 px-1 text-sm font-medium">
            {{ $label }}
        </button>
    @endforeach
</nav>
