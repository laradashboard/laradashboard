@props([
    'headers' => [],
    'visibleColumnIds' => [],
    'storageKey' => '',
])

@php
    $headerIds = array_values(array_filter(array_column($headers, 'id')));
    $visibleInHeaders = array_values(array_intersect($headerIds, $visibleColumnIds));
    $initialVisible = $visibleInHeaders !== [] ? $visibleInHeaders : $headerIds;
@endphp

<div
    class="relative"
    data-datatable-column-visibility
    wire:ignore
    x-data="{
        open: false,
        storageKey: @js($storageKey),
        visible: @js($initialVisible),
        isVisible(id) {
            return this.visible.includes(id);
        },
        isLastVisible(id) {
            return this.visible.length === 1 && this.isVisible(id);
        },
        persist() {
            if (! this.storageKey) {
                return;
            }

            localStorage.setItem(this.storageKey, JSON.stringify(this.visible));
        },
        sync(columnId) {
            if (this.visible.length === 0) {
                this.visible = [columnId];
                return;
            }

            this.persist();
            $wire.setVisibleColumnIds(this.visible);
        },
        init() {
            if (this.storageKey) {
                const saved = localStorage.getItem(this.storageKey);
                if (saved) {
                    try {
                        const ids = JSON.parse(saved);
                        if (Array.isArray(ids) && ids.length) {
                            this.visible = ids;
                            $wire.setVisibleColumnIds(ids);
                        }
                    } catch (e) {
                        // Ignore malformed storage.
                    }
                }
            }

            $wire.$watch('visibleColumnIds', (value) => {
                if (Array.isArray(value) && value.length) {
                    this.visible = [...value];
                    this.persist();
                }
            });
        }
    }"
>
    <button
        @click="open = !open"
        @keydown.escape.window="open = false"
        class="btn-default flex items-center justify-center gap-2 whitespace-nowrap"
        type="button"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
        aria-controls="datatable-column-visibility-menu"
        aria-label="{{ __('Toggle column visibility') }}"
    >
        <iconify-icon icon="lucide:eye" aria-hidden="true"></iconify-icon>
        {{ __('Columns') }}
        <iconify-icon icon="lucide:chevron-down" class="transition-transform duration-200" :class="{'rotate-180': open}" aria-hidden="true"></iconify-icon>
    </button>

    <div
        id="datatable-column-visibility-menu"
        x-show="open"
        x-cloak
        @click.outside="open = false"
        x-transition
        class="absolute top-10 right-0 z-30 mt-2 w-56 overflow-y-auto rounded-md bg-white p-2 shadow dark:bg-gray-700 max-h-80"
        role="group"
        aria-label="{{ __('Visible columns') }}"
    >
        <ul class="space-y-0.5">
            @foreach ($headers as $header)
                @php
                    $columnId = $header['id'] ?? '';
                @endphp
                <li>
                    <label
                        class="flex items-center gap-2 rounded px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                        :class="isLastVisible(@js($columnId)) ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'"
                    >
                        <input
                            type="checkbox"
                            class="form-checkbox"
                            value="{{ $columnId }}"
                            x-model="visible"
                            :disabled="isLastVisible(@js($columnId))"
                            :title="isLastVisible(@js($columnId)) ? @js(__('At least one column must remain visible')) : ''"
                            @change="sync(@js($columnId))"
                        >
                        <span>{{ __($header['title'] ?? $columnId) }}</span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
</div>
