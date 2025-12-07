{{--
    LaraBuilder Blade Component

    Usage:
    <x-builder.lara-builder
        context="email"
        :initial-data="$initialData"
        :template-data="$templateData"
        :save-url="route('admin.email-templates.store')"
        :list-url="route('admin.email-templates.index')"
        :upload-url="route('admin.email-templates.upload-image')"
        :video-upload-url="route('admin.email-templates.upload-video')"
    />
--}}

@props([
    'context' => 'email',
    'initialData' => null,
    'templateData' => null,
    'saveUrl' => null,
    'listUrl' => null,
    'uploadUrl' => null,
    'videoUploadUrl' => null,
    'showHeader' => true,
])

<div
    id="lara-builder-root"
    data-context="{{ $context }}"
    data-initial-data="{{ json_encode($initialData) }}"
    data-template-data="{{ json_encode($templateData) }}"
    @if($saveUrl) data-save-url="{{ $saveUrl }}" @endif
    @if($listUrl) data-list-url="{{ $listUrl }}" @endif
    @if($uploadUrl) data-upload-url="{{ $uploadUrl }}" @endif
    @if($videoUploadUrl) data-video-upload-url="{{ $videoUploadUrl }}" @endif
    data-show-header="{{ $showHeader ? 'true' : 'false' }}"
    data-translations='@json(__("*"))'
    {{ $attributes }}
></div>

{{-- Media Library Modal --}}
<x-media-modal
    id="laraBuilderMediaModal"
    :title="__('Select Media')"
    :multiple="false"
    allowedTypes="all"
    buttonClass="hidden"
/>
