@extends('backend.layouts.app')
@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6" x-data="{ selectedCourses: [], selectAll: false, bulkDeleteModalOpen: false }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Courses</h1>
        
        @can('create courses')
        <a href="{{ route('courses.create') }}" class="btn-primary flex items-center gap-2">
            <iconify-icon icon="feather:plus" height="16"></iconify-icon>
            Create Course
        </a>
        @endcan
    </div>

    <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="table min-w-full">
                <thead class="table-thead">
                    <tr class="table-tr">
                        <th width="5%" class="table-thead-th">
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    x-model="selectAll"
                                    @click="
                                        selectAll = !selectAll;
                                        selectedCourses = selectAll ?
                                            [...document.querySelectorAll('.course-checkbox')].map(cb => cb.value) :
                                            [];
                                    "
                                >
                            </div>
                        </th>
                        <th width="40%" class="table-thead-th">
                            <div class="flex items-center">
                                {{ __('Title') }}
                                <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'title' ? '-title' : 'title']) }}" class="ml-1">
                                    @if(request()->sort === 'title')
                                        <iconify-icon icon="lucide:sort-asc" class="text-primary"></iconify-icon>
                                    @elseif(request()->sort === '-title')
                                        <iconify-icon icon="lucide:sort-desc" class="text-primary"></iconify-icon>
                                    @else
                                        <iconify-icon icon="lucide:arrow-up-down" class="text-gray-400"></iconify-icon>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th width="15%" class="table-thead-th">
                            <div class="flex items-center">
                                {{ __('Price') }}
                                <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'price' ? '-price' : 'price']) }}" class="ml-1">
                                    @if(request()->sort === 'price')
                                        <iconify-icon icon="lucide:sort-asc" class="text-primary"></iconify-icon>
                                    @elseif(request()->sort === '-price')
                                        <iconify-icon icon="lucide:sort-desc" class="text-primary"></iconify-icon>
                                    @else
                                        <iconify-icon icon="lucide:arrow-up-down" class="text-gray-400"></iconify-icon>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th width="15%" class="table-thead-th">
                            <div class="flex items-center">
                                {{ __('Lessons') }}
                                <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort === 'lesson_count' ? '-lesson_count' : 'lesson_count']) }}" class="ml-1">
                                    @if(request()->sort === 'lesson_count')
                                        <iconify-icon icon="lucide:sort-asc" class="text-primary"></iconify-icon>
                                    @elseif(request()->sort === '-lesson_count')
                                        <iconify-icon icon="lucide:sort-desc" class="text-primary"></iconify-icon>
                                    @else
                                        <iconify-icon icon="lucide:arrow-up-down" class="text-gray-400"></iconify-icon>
                                    @endif
                                </a>
                            </div>
                        </th>
                        <th width="25%" class="table-thead-th table-thead-th-last">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($courses as $course)
                        <tr class="{{ $loop->index + 1 != count($courses) ?  'table-tr' : '' }}">
                            <td class="table-td">
                                <input
                                    type="checkbox"
                                    class="course-checkbox form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    value="{{ $course->id }}"
                                    x-model="selectedCourses"
                                >
                            </td>
                            <td class="table-td">
                                <div class="flex gap-0.5 items-center">
                                    <div class="bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center mr-2 h-10 w-10">
                                        <iconify-icon icon="lucide:book" class="text-center text-gray-400"></iconify-icon>
                                    </div>
                                    <div>
                                        <a href="{{ route('courses.show', $course) }}" class="text-gray-700 dark:text-white font-medium hover:text-primary dark:hover:text-primary">
                                            {{ $course->title }}
                                        </a>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ Str::limit($course->description, 50) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td">
                                <span class="font-medium">${{ number_format($course->price, 2) }}</span>
                            </td>
                            <td class="table-td">
                                <span class="badge">{{ $course->lesson_count }}</span>
                            </td>
                            <td class="table-td flex justify-center">
                                <x-buttons.action-buttons :label="__('Actions')" :show-label="false" align="right">
                                    <x-buttons.action-item
                                        :href="route('courses.show', $course)"
                                        icon="eye"
                                        :label="__('View')"
                                    />

                                    @can('edit courses')
                                    <x-buttons.action-item
                                        :href="route('courses.edit', $course)"
                                        icon="pencil"
                                        :label="__('Edit')"
                                    />
                                    @endcan

                                    @can('delete courses')
                                    <div x-data="{ deleteModalOpen: false }">
                                        <x-buttons.action-item
                                            type="modal-trigger"
                                            modal-target="deleteModalOpen"
                                            icon="trash"
                                            :label="__('Delete')"
                                            class="text-red-600 dark:text-red-400"
                                        />

                                        <x-modals.confirm-delete
                                            id="delete-modal-{{ $course->id }}"
                                            title="{{ __('Delete Course') }}"
                                            content="{{ __('Are you sure you want to delete this course?') }}"
                                            formId="delete-form-{{ $course->id }}"
                                            formAction="{{ route('courses.destroy', $course) }}"
                                            modalTrigger="deleteModalOpen"
                                            cancelButtonText="{{ __('No, cancel') }}"
                                            confirmButtonText="{{ __('Yes, Confirm') }}"
                                        />
                                    </div>
                                    @endcan

                                    @role('student')
                                    <x-buttons.action-item
                                        :href="route('student.enroll', $course)"
                                        icon="bookmark"
                                        :label="__('Enroll')"
                                        class="text-green-600 dark:text-green-400"
                                    />
                                    @endrole
                                </x-buttons.action-buttons>
                            </td>
                        </tr>
                        @empty
                        <tr class="table-tr">
                            <td colspan="5" class="table-td text-center">
                                <span class="text-gray-500 dark:text-gray-300">{{ __('No courses found') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div
        x-cloak
        x-show="bulkDeleteModalOpen"
        x-transition.opacity.duration.200ms
        x-trap.inert.noscroll="bulkDeleteModalOpen"
        x-on:keydown.esc.window="bulkDeleteModalOpen = false"
        x-on:click.self="bulkDeleteModalOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4 backdrop-blur-md"
        role="dialog"
        aria-modal="true"
        aria-labelledby="bulk-delete-modal-title"
    >
        <div
            x-show="bulkDeleteModalOpen"
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex max-w-md flex-col gap-4 overflow-hidden rounded-md border border-outline border-gray-100 dark:border-gray-800 bg-white text-on-surface dark:border-outline-dark dark:bg-gray-700 dark:text-gray-300"
        >
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-gray-800">
                <div class="flex items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 p-1">
                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <h3 id="bulk-delete-modal-title" class="font-semibold tracking-wide text-gray-700 dark:text-white">
                    {{ __('Delete Selected Courses') }}
                </h3>
                <button
                    x-on:click="bulkDeleteModalOpen = false"
                    aria-label="close modal"
                    class="text-gray-400 hover:bg-gray-200 hover:text-gray-700 rounded-md p-1 dark:hover:bg-gray-600 dark:hover:text-white"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-4 text-center">
                <p class="text-gray-500 dark:text-gray-300">
                    {{ __('Are you sure you want to delete the selected courses?') }}
                    {{ __('This action cannot be undone.') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection