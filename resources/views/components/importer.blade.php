<div class="space-y-4">
    <x-card>
        <x-slot name="header">{{ __('Import') }}</x-slot>

        <div class="p-4">
            <input type="file" wire:model.live="file" accept=".csv,.xlsx,.xls" class="form-control">
        </div>

        @if(count($fileHeaders) > 0)
            <div class="p-4 border-t">
                <h3 class="font-semibold mb-3">{{ __('Map Columns') }}</h3>
                
                @foreach($this->getModelColumns() as $column)
                    <div class="mb-3">
                        <label class="form-label">{{ ucfirst($column) }}</label>
                        <select wire:model="columnMappings.{{ $column }}" class="form-control">
                            <option value="">{{ __('Select') }}</option>
                            @foreach($fileHeaders as $header)
                                <option value="{{ $header }}">{{ $header }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

                <button wire:click="import" class="btn btn-primary mt-4">
                    {{ __('Import') }}
                </button>
            </div>
        @endif

        @if($imported > 0)
            <div class="p-4 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300">
                {{ __('Imported :count rows', ['count' => $imported]) }}
            </div>
        @endif

        @if(count($errors) > 0)
            <div class="p-4 bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300">
                <h4 class="font-semibold mb-2">{{ __('Errors') }}</h4>
                <ul class="list-disc ml-4">
                    @foreach($errors as $row => $error)
                        <li>{{ __('Row :row', ['row' => $row + 2]) }}: {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-card>
</div>
