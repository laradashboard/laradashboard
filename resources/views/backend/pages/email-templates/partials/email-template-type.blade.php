<div>
    @if($emailTemplate->type)
        <span class="badge badge-info">
            {{ $emailTemplate->type_label }}
        </span>
    @else
        <span class="badge badge-secondary">{{ __('Unknown') }}</span>
    @endif
</div>