<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <x-slot name="breadcrumbsData">
        <div
            x-data="{ exportModalOpen: false }"
            @exportmodalopen.window="exportModalOpen = $event.detail"
        >
            <x-breadcrumbs :breadcrumbs="$breadcrumbs">
                <x-slot name="title_after">
                    @if (request('status'))
                        <span class="badge">{{ ucfirst(request('status')) }}</span>
                    @endif
                    @if (request('category'))
                        <span class="badge">{{ __('Category: :category', ['category' => request('category')]) }}</span>
                    @endif
                    @if (request('tag'))
                        <span class="badge">{{ __('Tag: :tag', ['tag' => request('tag')]) }}</span>
                    @endif
                </x-slot>
                <x-slot name="actions_before">
                    @can('post.create')
                        <x-actions-dropdown
                            :label="__('Actions')"
                            icon="lucide:arrow-up-from-line"
                            position="right"
                        >
                            <x-actions-dropdown-item
                                icon="lucide:upload"
                                :label="__('Import')"
                                :description="__('Import posts from a CSV file')"
                                :href="route('admin.posts.import.form', $postType)"
                            />
                            <x-actions-dropdown-item
                                icon="lucide:download"
                                :label="__('Export')"
                                :description="__('Export posts as a CSV file')"
                                click="$dispatch('exportmodalopen', true); isOpen = false"
                            />
                        </x-actions-dropdown>
                    @endcan
                </x-slot>
            </x-breadcrumbs>

            {!! Hook::applyFilters(PostFilterHook::POSTS_AFTER_BREADCRUMBS, '', $postType) !!}

            @livewire('datatable.post-datatable', ['postType' => $postType ,'lazy' => true])

            {!! Hook::applyFilters(PostFilterHook::POSTS_AFTER_TABLE, '', $postType) !!}

            <x-modals.export
                id="export-modal"
                title="{{ __('Export :postType', ['postType' => $postTypeModel->label]) }}"
                modalTrigger="exportModalOpen"
                modelType="Post"
                modelNamespace="App\Models"
                :availableColumns="[]"
                :filtersItems="[]"
            />
        </div>
    </x-slot>
</x-layouts.backend-layout>