@extends('backend.layouts.app')

@section('title')
{{ __('Action Logs - ' . config('app.name')) }}
@endsection

@php
    $isActionLogExist = false;
@endphp
@section('admin-content')
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! ld_apply_filters('action_logs_after_breadcrumbs', '') !!}

        <div class="space-y-6">
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col md:flex-row justify-between items-center gap-3">
                    @include('backend.partials.search-form', [
                        'placeholder' => __('Search by title or type'),
                    ])

                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center">
                            <button id="dropdownDefault" data-dropdown-toggle="dropdown" class="btn-secondary flex items-center justify-center gap-2" type="button">
                                <iconify-icon icon="lucide:sliders"></iconify-icon>
                                {{ __('Filter') }}
                                <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                            </button>

                            <!-- Dropdown menu -->
                            <div id="dropdown" class="z-10 hidden w-56 p-2 bg-white rounded-md shadow dark:bg-gray-700">
                                <ul class="space-y-2">
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded"
                                    onclick="handleSelect('')">
                                        {{ __('All') }}
                                    </li>
                                    @foreach (\App\Enums\ActionType::cases() as $type)
                                        <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded {{ $type->value === request('type') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                            onclick="handleSelect('{{ $type->value }}')">
                                            {{ __(ucfirst($type->value)) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                    <table id="actionLogsTable" class="w-full dark:text-gray-300">
                        <thead class="bg-light text-capitalize">
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    {{ __('Sl') }}</th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    {{ __('Type') }}</th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    {{ __('Title') }}</th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    {{ __('Action By') }}</th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    {{ __('Data') }}</th>
                                <th class="bg-gray-50 dark:bg-gray-800 dark:text-white px-5 p-2 sm:px-6 text-left">
                                    {{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($actionLogs as $log)
                                <tr class="{{ $loop->index + 1 != count($actionLogs) ?  'border-b border-gray-100 dark:border-gray-800' : '' }}">
                                    <td class="px-5 py-4 sm:px-6 text-left">{{ $loop->index + 1 }}</td>
                                <td class="px-5 py-4 sm:px-6 text-left">{{ __(ucfirst($log->type)) }}</td>
                                    <td class="px-5 py-4 sm:px-6 text-left">{{ $log->title }}</td>
                                    <td class="px-5 py-4 sm:px-6 text-left">
                                        {{ $log->user->name . ' (' . $log->user->username . ')' ?? '' }}</td>
                                    <td class="px-5 py-4 sm:px-6 text-left">
                                        <button id="expand-btn-{{ $log->id }}" class="text-primary text-sm mt-2"
                                            data-modal-target="json-modal-{{ $log->id }}"
                                            data-modal-toggle="json-modal-{{ $log->id }}">
                                            {{ __('Expand JSON') }}
                                        </button>

                                        <x-action-log-modal :log="$log" />
                                    </td>

                                    <td class="px-5 py-4 sm:px-6 text-left">
                                        {{ $log->created_at->format('d M Y H:i A') }}
                                    </td>
                                </tr>
                                @php
                                    $isActionLogExist = true;
                                @endphp
                            @empty
                                @php
                                    $isActionLogExist = false;
                                @endphp
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-gray-500 dark:text-gray-300">{{ __('No action logs found') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="my-4 px-4 sm:px-6">
                        {{ $actionLogs->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@if ($isActionLogExist)
    @push('scripts')
        <script>
            document.querySelector('[data-modal-toggle="json-modal-{{ $log->id }}"]').addEventListener('click',
                function() {
                    document.getElementById('json-modal-{{ $log->id }}').classList.remove('hidden');
                });

            document.querySelector('[data-modal-hide="json-modal-{{ $log->id }}"]').addEventListener('click', function() {
                document.getElementById('json-modal-{{ $log->id }}').classList.add('hidden');
            });
        </script>
    @endpush
@endif

@push('scripts')
    <script>
        function handleSelect(value) {
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('type', value);
            window.location.href = currentUrl.toString();
        }
    </script>
@endpush
