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
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="btn-secondary flex items-center justify-center gap-2" type="button">
                                <iconify-icon icon="lucide:sliders"></iconify-icon>
                                {{ __('Filter') }}
                                <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute right-0 mt-2 w-56 rounded-md shadow bg-white dark:bg-gray-700 z-10">
                                <ul class="space-y-2 p-2">
                                    <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded"
                                        @click="open = false; handleSelect('')">
                                        {{ __('All') }}
                                    </li>
                                    @foreach (\App\Enums\ActionType::cases() as $type)
                                        <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded {{ $type->value === request('type') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                            @click="open = false; handleSelect('{{ $type->value }}')">
                                            {{ __(ucfirst($type->value)) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="actionLogsTable" class="table">
                        <thead class="table-thead">
                            <tr class="table-tr">
                                <th class="table-thead-th">{{ __('Sl') }}</th>
                                <th class="table-thead-th">{{ __('Type') }}</th>
                                <th class="table-thead-th">{{ __('Title') }}</th>
                                <th class="table-thead-th">{{ __('Action By') }}</th>
                                <th class="table-thead-th">{{ __('Data') }}</th>
                                <th class="table-thead-th table-thead-th-last text-right">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($actionLogs as $log)

                                <tr class="{{ $loop->index + 1 != count($actionLogs) ?  'table-tr' : '' }}">
                                    <td class="table-td table-td-checkbox">{{ $loop->index + 1 }}</td>
                                    <td class="table-td">{{ __(ucfirst($log->type)) }}</td>
                                    <td class="table-td">{{ $log->title }}</td>
                                    <td class="table-td">
                                        {{ $log->user->name . ' (' . $log->user->username . ')' ?? '' }}</td>
                                    <td class="table-td">
                                        <button id="expand-btn-{{ $log->id }}" class="text-primary text-sm mt-2"
                                            data-modal-target="json-modal-{{ $log->id }}"
                                            data-modal-toggle="json-modal-{{ $log->id }}">
                                            {{ __('Expand JSON') }}
                                        </button>

                                        <x-action-log-modal :log="$log" />
                                    </td>

                                    <td class="table-td text-right">
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
                                <tr class="table-tr">
                                    <td colspan="6" class="table-td text-center py-4">
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
