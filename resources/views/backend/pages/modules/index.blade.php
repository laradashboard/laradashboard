@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] . ' | ' . config('app.name') }}
@endsection

@section('admin-content')

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6"
    x-data="{ showUploadArea: false }"
    x-init="showUploadArea = {{ count($modules) > 0 ? 'false' : 'true' }}"
    x-cloak
    x-on:keydown.escape.window="showUploadArea = false"
    x-on:click.away="showUploadArea = false"
>
    <x-breadcrumbs :breadcrumbs="$breadcrumbs">
        <x-slot name="title_after">
            @if(count($modules) > 0)
                <button
                    @click="showUploadArea = !showUploadArea"
                    class="ml-4 btn-primary btn-upload-module"
                >
                    <iconify-icon icon="lucide:upload-cloud" class="mr-2"></iconify-icon>
                    {{ __('Upload Module') }}
                </button>

                <x-popover position="bottom" width="w-[300px]">
                    <x-slot name="trigger">
                        <iconify-icon icon="lucide:info" class="text-lg ml-3" title="{{ __('Module Requirements') }}"></iconify-icon>
                    </x-slot>

                    <div class="w-[300px] p-4 font-normal">
                        <h3 class="font-medium text-gray-700 dark:text-white mb-2">{{ __('Module Requirements') }}</h3>
                        <p class="mb-2">{{ __('You can upload custom modules to extend functionality.') }}</p>
                        <ul class="list-disc pl-5 space-y-1 text-sm">
                            <li>{{ __('Modules must be in .zip format') }}</li>
                            <li>{{ __('Each module should have a valid module.json file') }}</li>
                            <li>
                                {{ __('Must follow guidelines.') }}&nbsp;
                                <a href="https://laradashboard.com/docs/how-to-create-a-module-in-lara-dashboard/" class="text-primary hover:underline" target="_blank">
                                    {{ __('Learn more') }}
                                    <iconify-icon icon="lucide:external-link" class="text-sm"></iconify-icon>
                                </a>
                            </li>
                        </ul>
                        @if(config('app.demo_mode', false))
                        <div class="bg-yellow-50 text-yellow-700 rounded-md mt-4 p-3">
                            <iconify-icon icon="lucide:alert-triangle"></iconify-icon> &nbsp;
                            {{ __('Note: Module uploads are disabled in demo mode.') }}
                        </div>
                        @endif
                    </div>
                </x-popover>
            @endif
        </x-slot>
    </x-breadcrumbs>

    {!! ld_apply_filters('modules_after_breadcrumbs', '') !!}

    @if (!empty($modules))
    <div x-show="showUploadArea" class="mb-6 p-6 border-2 border-dashed border-gray-300 rounded-md bg-gray-50 dark:bg-gray-800 dark:border-gray-600"
            @dragover.prevent
            @drop.prevent="$refs.uploadModule.files = $event.dataTransfer.files; $refs.uploadModule.dispatchEvent(new Event('change'))">
        <p class="text-center text-gray-600 dark:text-gray-300">
            {{ __('Drag and drop your module file here, or') }}
            <button
                @click="$refs.uploadModule.click()"
                class="text-primary underline hover:text-primary"
            >
                {{ __('browse') }}
            </button>
            {{ __('to select a file.') }}
        </p>
        <form action="{{ route('admin.modules.store') }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" name="module" accept=".zip" x-ref="uploadModule" @change="$event.target.form.submit()">
        </form>
    </div>
    @endif

    <div class="space-y-6">
        @if (empty($modules))
        <div class="flex flex-col items-center justify-center h-64 bg-gray-100 dark:bg-gray-800 rounded-md border-2 border-dashed border-gray-300"
                @dragover.prevent
                @drop.prevent="$refs.uploadModule.files = $event.dataTransfer.files; $refs.uploadModule.dispatchEvent(new Event('change'))">
            <svg class="w-16 h-16 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <p class="mt-4 text-gray-600 dark:text-gray-300">{{ __('Drag and drop your module file here, or') }}</p>
            <button
                @click="$refs.uploadModule.click()"
                class="mt-4 btn-primary"
            >
                <iconify-icon icon="lucide:upload-cloud" class="mr-2"></iconify-icon>
                {{ __('Upload') }}
            </button>
            <form action="{{ route('admin.modules.store') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" name="module" accept=".zip" x-ref="uploadModule" @change="$event.target.form.submit()">
            </form>
        </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($modules as $module)
                    <div tabindex="0" class="rounded-md border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-brand-800 transition-all duration-200 hover:shadow-md">
                        <div class="flex justify-between" x-data="{ deleteModalOpen: false, errorModalOpen: false, errorMessage: '' }">
                            <div class="py-3">
                                <h2>
                                    <i class="bi {{ $module->icon }} text-3xl text-gray-500 dark:text-gray-300"></i>
                                </h2>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-white">
                                    {{ $module->title }}
                                </h3>
                            </div>


                            <button id="dropdownMenuIconButton" data-dropdown-toggle="dropdownMore-{{ $module->name }}" class="inline-flex items-right h-9 p-2 text-sm font-medium text-center text-gray-700 bg-white rounded-md hover:bg-gray-100 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-600" type="button">
                                <iconify-icon icon="lucide:more-vertical"></iconify-icon>
                            </button>

                            <div id="dropdownMore-{{ $module->name }}" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-md shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600">
                                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownMenuIconButton">
                                    <li>
                                        <div>
                                            <button
                                                x-on:click="deleteModalOpen = true"
                                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full px-2 text-left"
                                            >
                                                {{ __('Delete') }}
                                            </button>
                                        </div>
                                    </li>
                                    <li>
                                        <button
                                            onclick="toggleModuleStatus('{{ $module->name }}', event)"
                                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full px-2 text-left"
                                        >
                                            {{ $module->status ? __('Disable') : __('Enable') }}
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <x-modals.confirm-delete
                                id="delete-modal-{{ $module->name }}"
                                title="{{ __('Delete Module') }}"
                                content="{{ __('Are you sure you want to delete this module?') }}"
                                formId="delete-form-{{ $module->name }}"
                                formAction="{{ route('admin.modules.delete', $module->name) }}"
                                modalTrigger="deleteModalOpen"
                                cancelButtonText="{{ __('No, Cancel') }}"
                                confirmButtonText="{{ __('Yes, Confirm') }}"
                            />

                            <x-modals.error-message
                                id="error-modal-{{ $module->name }}"
                                title="{{ __('Operation Failed') }}"
                                modalTrigger="errorModalOpen"
                            />
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $module->description }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            {{ __('Tags:') }}
                            @foreach ($module->tags as $tag)
                                <span class="inline-block px-2 py-1 text-xs font-medium text-white bg-gray-400 rounded-full mr-1 mb-1">{{ $tag }}</span>
                            @endforeach
                        </p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm font-medium {{ $module->status ? 'text-green-500' : 'text-red-500' }}">
                                {{ $module->status ? __('Enabled') : __('Disabled') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            {{ $modules->links() }}
        </div>
    </div>
</div>


<script>
    function toggleModuleStatus(moduleName, event) {
        const moduleElement = event.target.closest('[x-data]');
        const Alpine = window.Alpine;

        fetch(`/admin/modules/toggle-status/${moduleName}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const button = event.target;
                button.textContent = data.status ? '{{ __("Disable") }}' : '{{ __("Enable") }}';

                // Refresh the page to show updated status
                window.location.reload();
            } else {
                // Show error modal instead of alert
                if (moduleElement && Alpine) {
                    const component = Alpine.$data(moduleElement);
                    component.errorMessage = data.message || '{{ __("An error occurred while processing your request.") }}';
                    component.errorModalOpen = true;
                }
            }
        })
        .catch(error => {
            // Handle network errors
            if (moduleElement && Alpine) {
                const component = Alpine.$data(moduleElement);
                component.errorMessage = '{{ __("Network error. Please check your connection and try again.") }}';
                component.errorModalOpen = true;
            }
        });
    }
</script>

@endsection
