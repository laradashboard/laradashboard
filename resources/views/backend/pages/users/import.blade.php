<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-import-page
        modelType="User"
        modelNamespace="App\Models"
        :optionalRequired="[]"
        :sampleRoute="route('admin.users.import.sample')"
        sampleText="{{ __('Download our sample CSV file to see the correct format and try importing sample users.') }}"
    />
</x-layouts.backend-layout>
