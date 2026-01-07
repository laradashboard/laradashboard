<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    {!! Hook::applyFilters(ModuleFilterHook::MODULE_SHOW_AFTER_BREADCRUMBS, '', $module) !!}

    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs">
            <x-slot name="title_before">
                <iconify-icon icon="{{ $module->icon }}" class="text-2xl text-gray-500 dark:text-gray-300"></iconify-icon>
            </x-slot>
            <x-slot name="title_after">
                <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">v{{ $module->version }}</span>
            </x-slot>
            <x-slot name="actions_after">
                <div class="flex gap-2" x-data="{ isToggling: false }">
                    @if($module->documentation_url)
                        <a
                            href="{{ $module->documentation_url }}"
                            target="_blank"
                            rel="noopener"
                            class="btn-default"
                        >
                            <iconify-icon icon="lucide:book-open" class="mr-2"></iconify-icon>
                            {{ __('Documentation') }}
                        </a>
                    @endif

                    <button
                        type="button"
                        @click="
                            if (isToggling) return;
                            isToggling = true;
                            fetch('{{ route('admin.modules.toggle-status', $module->name) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.reload();
                                } else {
                                    alert(data.message || '{{ __('An error occurred') }}');
                                    isToggling = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('{{ __('An error occurred while updating module status.') }}');
                                isToggling = false;
                            });
                        "
                        :disabled="isToggling"
                        class="{{ $module->status ? 'btn-warning' : 'btn-success' }}"
                    >
                        <iconify-icon x-show="!isToggling" icon="{{ $module->status ? 'lucide:power-off' : 'lucide:power' }}" class="mr-2"></iconify-icon>
                        <iconify-icon x-show="isToggling" icon="lucide:loader-2" class="mr-2 animate-spin"></iconify-icon>
                        <span x-text="isToggling ? '{{ __('Processing...') }}' : '{{ $module->status ? __('Disable') : __('Enable') }}'"></span>
                    </button>
                </div>
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Banner Image --}}
                @if($module->hasBannerImage())
                    <div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800">
                        <img
                            src="{{ $module->getBannerUrl() }}"
                            alt="{{ $module->title }} Banner"
                            class="w-full h-auto object-cover max-h-64"
                        />
                    </div>
                @endif

                <x-card.card bodyClass="!p-5">
                    <x-slot:header>{{ __('Module Details') }}</x-slot:header>

                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                        {!! $module->description !!}
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Module Name') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 font-mono">{{ $module->name }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Version') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $module->version }}</p>
                        </div>

                        @if($module->category)
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Category') }}</label>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($module->category) }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Priority') }}</label>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $module->priority }}</p>
                        </div>

                        @if($module->author)
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Author') }}</label>
                                <p class="flex gap-4 mt-1 text-sm text-gray-700 dark:text-gray-300">
                                    @if($module->author_url)
                                        <a href="{{ $module->author_url }}" target="_blank" rel="noopener" class="text-primary-600 hover:underline">
                                            {{ $module->author }}
                                        </a>
                                    @else
                                        {{ $module->author }}
                                    @endif

                                    @if($module->author_url)
                                        <a href="{{ $module->author_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                                            <iconify-icon icon="lucide:external-link" class="text-base"></iconify-icon>
                                            {{ __('Author Website') }}
                                        </a>
                                    @endif
                                </p>


                            </div>
                        @endif
                    </div>
                </x-card.card>

                {!! Hook::applyFilters(ModuleFilterHook::MODULE_SHOW_AFTER_MAIN_CONTENT, '', $module) !!}
            </div>

            {{-- Sidebar (Right - 1 column) --}}
            <div class="lg:col-span-1 space-y-6">
                {!! Hook::applyFilters(ModuleFilterHook::MODULE_SHOW_SIDEBAR_BEFORE, '', $module) !!}
                {{-- Status Card --}}
                <x-card.card bodyClass="!p-4 !space-y-4">
                    <x-slot:header>{{ __('Status') }}</x-slot:header>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Module Status') }}</label>
                        <div class="mt-1">
                            @if($module->status)
                                <span class="badge badge-success">
                                    <iconify-icon icon="lucide:check-circle" class="mr-1"></iconify-icon>
                                    {{ __('Enabled') }}
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    <iconify-icon icon="lucide:x-circle" class="mr-1"></iconify-icon>
                                    {{ __('Disabled') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Icon') }}</label>
                        <div class="mt-1 flex items-center gap-2">
                            <iconify-icon icon="{{ $module->icon }}" class="text-xl text-gray-500 dark:text-gray-400"></iconify-icon>
                            <span class="text-sm text-gray-700 dark:text-gray-300 font-mono">{{ $module->icon }}</span>
                        </div>
                    </div>
                </x-card.card>

                {{-- Tags Card --}}
                @if(!empty($module->tags))
                    <x-card.card bodyClass="!p-4">
                        <x-slot:header>{{ __('Tags') }}</x-slot:header>

                        <div class="flex flex-wrap gap-1.5">
                            @foreach($module->tags as $tag)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </x-card.card>
                @endif

                {!! Hook::applyFilters(ModuleFilterHook::MODULE_SHOW_SIDEBAR_AFTER, '', $module) !!}
            </div>
        </div>

        @can('delete', $module)
            <x-card.card class="border-red-200 dark:border-red-900/50">
                <x-slot:header>
                    <span class="text-red-600 dark:text-red-400">{{ __('Danger Zone') }}</span>
                </x-slot:header>

                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Delete this module') }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Once you delete a module, there is no going back. All module files will be removed.') }}</p>
                    </div>

                    <div x-data="{ deleteModalOpen: false }">
                        <button
                            @click="deleteModalOpen = true"
                            type="button"
                            class="btn-danger"
                        >
                            <iconify-icon icon="lucide:trash-2" class="mr-2"></iconify-icon>
                            {{ __('Delete Module') }}
                        </button>

                        <x-modals.confirm-delete
                            id="delete-modal-{{ $module->name }}"
                            :title="__('Delete Module')"
                            :content="__('Are you sure you want to delete the module :name? This action cannot be undone and will remove all module files.', ['name' => $module->title])"
                            :formAction="route('admin.modules.delete', $module->name)"
                            modalTrigger="deleteModalOpen"
                            :cancelButtonText="__('No, Cancel')"
                            :confirmButtonText="__('Yes, Delete')"
                        />
                    </div>
                </div>
            </x-card.card>
        @endcan
    </div>

    {!! Hook::applyFilters(ModuleFilterHook::MODULE_SHOW_AFTER_CONTENT, '', $module) !!}
</x-layouts.backend-layout>
