@extends('crm::layouts.master')

@section('crm-admin-content')
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <livewire:data-manager 
            :model="$model" 
            mode="import" 
            :modelNamespace="$modelNamespace"
            :filters="json_encode($filters ?? [])" 
        />
    </div>
@endsection
