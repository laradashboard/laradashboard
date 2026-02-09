@props([
    'modelType' => null,
    'modelNamespace' => null,
    'optionalRequired' => [],
    'sampleRoute' => null,
    'sampleText' => __('Download our sample CSV file to see the correct format and try importing.'),
])

<div class="space-y-6">
    <livewire:components.import-form
        :modelType="$modelType"
        :modelNamespace="$modelNamespace"
        :optionalRequired="$optionalRequired"
    />

    <x-card>
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-primary dark:text-blue-100 mb-1">
                    {{ __('Need a sample file?') }}
                </h3>
                <p class="text-sm text-gray-400">
                    {{ $sampleText }}
                </p>
            </div>

            @if($sampleRoute)
                <a href="{{ $sampleRoute }}"
                    class="btn btn-primary flex items-center gap-2">
                    <iconify-icon icon="feather:download" height="16" width="16"></iconify-icon>
                    {{ __('Download Sample CSV') }}
                </a>
            @endif
        </div>
    </x-card>
</div>
