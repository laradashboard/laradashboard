{{-- Usage Example --}}

{{-- In your blade file (e.g., users/import.blade.php) --}}
@livewire('components.importer', ['modelClass' => \App\Models\User::class])

{{-- Or for CRM Contact --}}
@livewire('components.importer', ['modelClass' => \Modules\Crm\Models\Contact::class])

{{-- That's it! Just one line. --}}
