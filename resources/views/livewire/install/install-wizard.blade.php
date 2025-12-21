<div>
    {{-- Header --}}
    <div class="text-center mb-8">
        <img src="{{ asset('images/logo/icon.png') }}" alt="Installation" class="w-10 h-10 mx-auto" />
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ __('Lara Dashboard Installation') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('Follow the steps below to set up your application') }}</p>
    </div>

    {{-- Progress Steps --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <div class="flex flex-col items-center flex-1">
                    <div class="relative flex items-center justify-center">
                        @if ($i < $currentStep)
                            {{-- Completed step --}}
                            <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        @elseif ($i === $currentStep)
                            {{-- Current step --}}
                            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                                <span class="text-white font-semibold">{{ $i }}</span>
                            </div>
                        @else
                            {{-- Future step --}}
                            <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <span class="text-gray-500 dark:text-gray-400 font-semibold">{{ $i }}</span>
                            </div>
                        @endif
                    </div>
                    <span class="text-xs mt-2 text-center {{ $i === $currentStep ? 'text-primary font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ match($i) {
                            1 => __('Requirements'),
                            2 => __('Database'),
                            3 => __('APP Key'),
                            4 => __('Admin'),
                            5 => __('Settings'),
                            6 => __('Complete'),
                            default => ''
                        } }}
                    </span>
                </div>
                @if ($i < $totalSteps)
                    <div class="flex-1 h-0.5 {{ $i < $currentStep ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }} -mt-5"></div>
                @endif
            @endfor
        </div>
    </div>

    {{-- Main Card --}}
    <x-card.card>
        <x-slot:header>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $this->getStepTitle() }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $this->getStepDescription() }}</p>
            </div>
        </x-slot:header>

        {{-- Error Message --}}
        @if ($errorMessage)
            <x-alerts.error :message="$errorMessage" class="mb-4" />
        @endif

        {{-- Success Message --}}
        @if ($successMessage)
            <x-alerts.success :message="$successMessage" class="mb-4" />
        @endif

        {{-- Step Content --}}
        <div class="min-h-[300px]">
            @switch($currentStep)
                @case(1)
                    @include('livewire.install.steps.requirements')
                    @break
                @case(2)
                    @include('livewire.install.steps.database')
                    @break
                @case(3)
                    @include('livewire.install.steps.app-key')
                    @break
                @case(4)
                    @include('livewire.install.steps.admin-user')
                    @break
                @case(5)
                    @include('livewire.install.steps.site-settings')
                    @break
                @case(6)
                    @include('livewire.install.steps.complete')
                    @break
            @endswitch
        </div>

        {{-- Navigation Footer --}}
        <x-slot:footer>
            <div class="flex justify-between w-full">
                <div>
                    @if ($currentStep > 1 && $currentStep < $totalSteps)
                        <x-buttons.button
                            variant="secondary"
                            wire:click="previousStep"
                            :disabled="$isProcessing"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            {{ __('Previous') }}
                        </x-buttons.button>
                    @endif
                </div>
                <div>
                    @if ($currentStep < $totalSteps)
                        <x-buttons.button
                            variant="primary"
                            wire:click="nextStep"
                            loadingTarget="nextStep"
                            loadingText="{{ __('Processing...') }}"
                        >
                            {{ __('Next') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </x-buttons.button>
                    @else
                        {{-- Use regular form POST to avoid session/CSRF issues when logging in --}}
                        <form action="{{ route('install.complete') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="admin_user_id" value="{{ $adminUserId }}">
                            <x-buttons.button
                                type="submit"
                                variant="success"
                            >
                                {{ __('Go to Dashboard') }}
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </x-buttons.button>
                        </form>
                    @endif
                </div>
            </div>
        </x-slot:footer>
    </x-card.card>

    {{-- Footer --}}
    <div class="text-center mt-6 text-sm text-gray-500 dark:text-gray-400">
        <p>&copy; {{ date('Y') }} Lara Dashboard. {{ __('All rights reserved.') }}</p>
    </div>
</div>
