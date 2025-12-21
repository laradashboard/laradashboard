<div class="space-y-6">
    {{-- Database Driver --}}
    <div>
        <x-inputs.select
            name="dbDriver"
            label="{{ __('Database Engine') }}"
            wire:model.live="dbDriver"
            :options="$this->getDrivers()"
            :value="$dbDriver"
        />
    </div>

    @if ($dbDriver !== 'sqlite')
        {{-- Database Host and Port --}}
        <div class="grid grid-cols-2 gap-4">
            <x-inputs.input
                name="dbHost"
                label="{{ __('Database Host') }}"
                wire:model="dbHost"
                :value="$dbHost"
                placeholder="127.0.0.1"
            />
            <x-inputs.input
                name="dbPort"
                label="{{ __('Database Port') }}"
                wire:model="dbPort"
                :value="$dbPort"
                placeholder="{{ $dbDriver === 'mysql' ? '3306' : ($dbDriver === 'pgsql' ? '5432' : '1433') }}"
            />
        </div>
    @endif

    {{-- Database Name --}}
    <div>
        <x-inputs.input
            name="dbDatabase"
            label="{{ $dbDriver === 'sqlite' ? __('Database File Name') : __('Database Name') }}"
            wire:model="dbDatabase"
            :value="$dbDatabase"
            :placeholder="$dbDriver === 'sqlite' ? 'database.sqlite' : 'laradashboard'"
            :hint="$dbDriver === 'sqlite' ? __('The file will be created in the database directory') : __('Make sure this database exists')"
        />
    </div>

    @if ($dbDriver !== 'sqlite')
        {{-- Username and Password --}}
        <div class="grid grid-cols-2 gap-4">
            <x-inputs.input
                name="dbUsername"
                label="{{ __('Database Username') }}"
                wire:model="dbUsername"
                :value="$dbUsername"
                placeholder="root"
            />
            <x-inputs.password
                name="dbPassword"
                label="{{ __('Database Password') }}"
                wire:model="dbPassword"
                :value="$dbPassword"
                placeholder="{{ __('Enter password') }}"
            />
        </div>
    @endif

    {{-- Test Connection Button --}}
    <div class="pt-4">
        <div class="flex items-center gap-4">
            <x-buttons.button
                variant="{{ $dbTestSuccess ? 'success' : 'secondary' }}"
                wire:click="testDatabaseConnection"
                loadingTarget="testDatabaseConnection"
                loadingText="{{ __('Testing...') }}"
            >
                @if ($dbTestSuccess)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ __('Connection Verified') }}
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    {{ __('Test Connection') }}
                @endif
            </x-buttons.button>

            @if ($dbTestMessage && $dbTestSuccess)
                <span class="text-sm text-green-600 dark:text-green-400">{{ $dbTestMessage }}</span>
            @endif
        </div>
    </div>

    {{-- Info Box --}}
    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-700 dark:text-blue-300">
                <p class="font-medium mb-1">{{ __('Important') }}</p>
                <p>{{ __('After verifying the connection, clicking "Next" will run database migrations to create all necessary tables.') }}</p>
            </div>
        </div>
    </div>
</div>
