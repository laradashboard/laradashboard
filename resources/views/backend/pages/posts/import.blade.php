<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-import-page
        modelType="Post"
        modelNamespace="App\Models"
        :optionalRequired="[]"
        :sampleRoute="route('admin.posts.import.sample', $postType)"
        sampleText="{{ __('Download our sample CSV file to see the correct format and try importing sample posts.') }}"
    />
</x-layouts.backend-layout>
