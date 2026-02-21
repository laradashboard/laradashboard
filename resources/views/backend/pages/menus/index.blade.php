<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="grid gap-6">
        @if($menus->isEmpty())
            <x-card>
                <div class="text-center py-12">
                    <iconify-icon icon="lucide:menu" class="text-6xl text-gray-400 dark:text-gray-600 mb-4"></iconify-icon>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('No menus yet') }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">{{ __('Create your first navigation menu to get started.') }}</p>
                    @can('create', \App\Models\Menu::class)
                        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
                            <iconify-icon icon="lucide:plus" class="mr-2"></iconify-icon>
                            {{ __('Create Menu') }}
                        </a>
                    @endcan
                </div>
            </x-card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                @foreach($menus as $menu)
                    @php
                        $deleteTitle = __('Delete Menu');
                        $deleteContent = __('Are you sure you want to delete this menu? This will also delete all menu items.');
                        $cancelText = __('No, cancel');
                        $confirmText = __('Yes, delete');
                    @endphp
                    <x-card class="group hover:shadow-lg transition-all duration-200 hover:border-primary/30 !flex !flex-col" bodyClass="!py-4 flex-1">
                        {{-- Header --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary">
                                    <iconify-icon icon="lucide:menu" class="text-xl"></iconify-icon>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                        {{ $menu->name }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $locations[$menu->location] ?? $menu->location }}
                                    </p>
                                </div>
                            </div>
                            @if($menu->status === 'active')
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    {{ __('Active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    {{ __('Inactive') }}
                                </span>
                            @endif
                        </div>

                        {{-- Description --}}
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                            {{ $menu->description ?? __('No description provided.') }}
                        </p>

                        {{-- Stats --}}
                        <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <iconify-icon icon="lucide:list" class="text-gray-400"></iconify-icon>
                                <span>{{ $menu->items_count }} {{ trans_choice('item|items', $menu->items_count) }}</span>
                            </div>
                        </div>

                        {{-- Footer with actions --}}
                        <x-slot name="footer">
                            <div class="flex items-center justify-end gap-2 w-full">
                                @can('update', $menu)
                                    <a href="{{ route('admin.menus.builder', $menu->id) }}" class="btn btn-sm btn-primary">
                                        <iconify-icon icon="lucide:settings-2" class="mr-1"></iconify-icon>
                                        {{ __('Manage') }}
                                    </a>
                                @endcan

                                @can('delete', $menu)
                                    <div x-data="{ deleteModalOpen: false }">
                                        <button
                                            type="button"
                                            @click="deleteModalOpen = true"
                                            class="btn btn-sm btn-default text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                            title="{{ __('Delete') }}"
                                        >
                                            <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                        </button>

                                        <x-modals.confirm-delete
                                            id="delete-menu-{{ $menu->id }}"
                                            :title="$deleteTitle"
                                            :content="$deleteContent"
                                            formId="delete-menu-form-{{ $menu->id }}"
                                            :formAction="route('admin.menus.destroy', $menu->id)"
                                            modalTrigger="deleteModalOpen"
                                            :cancelButtonText="$cancelText"
                                            :confirmButtonText="$confirmText"
                                        />
                                    </div>
                                @endcan
                            </div>
                        </x-slot>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.backend-layout>
