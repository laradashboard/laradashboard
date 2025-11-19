<div>
    @if($emailTemplate->type)
        <span class="badge badge-info">
            {{ ucfirst($emailTemplate->type->value) }}
        </span>
    @else
        <span class="badge badge-secondary">{{ __('Unknown') }}</span>
    @endif
</div>