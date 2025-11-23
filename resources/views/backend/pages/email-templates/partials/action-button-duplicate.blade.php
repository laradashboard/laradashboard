@php
    $emailTemplateId = $emailTemplate->id ?? null;
    $duplicateUrl = $emailTemplate ? route('admin.email-templates.duplicate', $emailTemplate->id) : null;
@endphp

<x-buttons.action-item
    type="button"
    onclick="openDuplicateEmailTemplateModal({{ $emailTemplateId }}, '{{ $duplicateUrl }}')"
    icon="lucide:copy"
    :label="__('Duplicate')"
/>
