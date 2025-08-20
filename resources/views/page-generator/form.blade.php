@extends('backend.layouts.app')

@section('title')
    {{ $title }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />
    
    <x-page-generator.form 
        :form-action="$formAction"
        :form-method="$formMethod"
        :form-id="$formId"
        :form-classes="$formClasses"
        :enctype="$enctype"
        :model="$model"
        :fields="$fields"
        :sections="$sections"
        :submit-button-text="$submitButtonText"
        :cancel-button-text="$cancelButtonText"
        :cancel-route="$cancelRoute"
        :show-cancel-button="$showCancelButton"
        :is-ajax-form="$isAjaxForm"
    />
</div>
@endsection