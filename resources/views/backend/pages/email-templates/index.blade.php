<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <!-- Email Templates Table -->
        <div class="space-y-6">
            <livewire:datatable.email-template-datatable lazy />
        </div>
    </div>
</x-layouts.backend-layout>
