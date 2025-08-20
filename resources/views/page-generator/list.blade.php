@extends('backend.layouts.app')

@section('title')
    {{ $title }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />
    
    <x-page-generator.list 
        :columns="$columns"
        :data="$data"
        :actions="$actions"
        :bulk-actions="$bulkActions"
        :filters="$filters"
        :show-checkboxes="$showCheckboxes"
        :show-search="$showSearch"
        :show-filters="$showFilters"
        :show-bulk-actions="$showBulkActions"
        :show-pagination="$showPagination"
        :show-actions="$showActions"
        :empty-message="$emptyMessage"
        :search-placeholder="$searchPlaceholder"
        :create-route="$createRoute"
        :create-button-text="$createButtonText"
        :show-create-button="$showCreateButton"
        :sortable-columns="$sortableColumns"
        :searchable-columns="$searchableColumns"
    />
</div>
@endsection