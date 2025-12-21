<div class="space-y-6">
    {{-- Name Fields --}}
    <div class="grid grid-cols-2 gap-4">
        <x-inputs.input
            name="adminFirstName"
            label="{{ __('First Name') }}"
            wire:model="adminFirstName"
            :value="$adminFirstName"
            placeholder="{{ __('John') }}"
            required
        />
        <x-inputs.input
            name="adminLastName"
            label="{{ __('Last Name') }}"
            wire:model="adminLastName"
            :value="$adminLastName"
            placeholder="{{ __('Doe') }}"
            required
        />
    </div>

    {{-- Email --}}
    <div>
        <x-inputs.input
            type="email"
            name="adminEmail"
            label="{{ __('Email Address') }}"
            wire:model="adminEmail"
            :value="$adminEmail"
            placeholder="{{ __('admin@example.com') }}"
            required
        />
    </div>

    {{-- Username --}}
    <div>
        <x-inputs.input
            name="adminUsername"
            label="{{ __('Username') }}"
            wire:model="adminUsername"
            :value="$adminUsername"
            placeholder="{{ __('admin') }}"
            hint="{{ __('Minimum 3 characters') }}"
            required
        />
    </div>

    {{-- Password Fields --}}
    <div class="grid grid-cols-2 gap-4">
        <x-inputs.password
            name="adminPassword"
            label="{{ __('Password') }}"
            wire:model="adminPassword"
            :value="$adminPassword"
            placeholder="{{ __('Enter password') }}"
            hint="{{ __('Minimum 8 characters') }}"
            :autogenerate="true"
            required
        />
        <x-inputs.password
            name="adminPasswordConfirmation"
            label="{{ __('Confirm Password') }}"
            wire:model="adminPasswordConfirmation"
            :value="$adminPasswordConfirmation"
            placeholder="{{ __('Confirm password') }}"
            required
        />
    </div>

    {{-- Info Box --}}
    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-700 dark:text-blue-300">
                <p class="font-medium mb-1">{{ __('Administrator Account') }}</p>
                <p>{{ __('This account will have full access to all features in the admin panel. You can create additional users after installation.') }}</p>
            </div>
        </div>
    </div>
</div>
