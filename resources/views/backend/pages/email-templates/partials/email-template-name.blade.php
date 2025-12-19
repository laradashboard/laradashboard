<div class="flex items-center">
    <div>
        <div class="font-medium">
            <a href="{{ route('admin.email-templates.show', ['email_template' => $emailTemplate->id]) }}"
               class="hover:text-primary">
                {{ $emailTemplate->name }}
            </a>
        </div>
        @if($emailTemplate->description)
            <div class="text-sm text-muted-foreground">
                {{ Str::limit($emailTemplate->description, 50) }}
            </div>
        @endif
    </div>
</div>