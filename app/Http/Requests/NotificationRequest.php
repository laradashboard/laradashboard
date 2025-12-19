<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ReceiverType;
use App\Services\ReceiverTypeRegistry;
use App\Enums\NotificationType;
use App\Models\Setting;

class NotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('manage', Setting::class);
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'notification_type' => ['required', 'string', Rule::in(NotificationType::getValues())],
            'email_template_id' => 'required|exists:email_templates,id',
            'receiver_type' => ['required', 'string', Rule::in(array_merge(ReceiverType::getValues(), ReceiverTypeRegistry::all()))],
            'receiver_ids' => 'nullable|array',
            'receiver_ids.*' => 'integer',
            'receiver_emails' => 'nullable|array',
            'receiver_emails.*' => 'email',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'reply_to_email' => 'nullable|email|max:255',
            'reply_to_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $notificationId = $this->route('notification');
            if ($notificationId) {
                $rules['name'] = 'required|string|max:255|unique:notifications,name,' . $notificationId;
            }
        } else {
            $rules['name'] = 'required|string|max:255|unique:notifications,name';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Notification name is required.',
            'name.unique' => 'A notification with this name already exists.',
            'notification_type.required' => 'Notification type is required.',
            'notification_type.in' => 'Invalid notification type selected.',
            'email_template_id.required' => 'Email template is required.',
            'email_template_id.exists' => 'Selected email template does not exist.',
            'receiver_type.required' => 'Receiver type is required.',
            'receiver_type.in' => 'Invalid receiver type selected.',
            'receiver_emails.*.email' => 'Each receiver email must be a valid email address.',
            'from_email.email' => 'From email must be a valid email address.',
            'reply_to_email.email' => 'Reply-to email must be a valid email address.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $receiverIds = null;
        if ($this->has('receiver_ids_text') && ! empty($this->receiver_ids_text)) {
            $receiverIds = array_filter(
                array_map('trim', explode(',', $this->receiver_ids_text)),
                fn ($id) => is_numeric($id) && $id > 0
            );
            $receiverIds = array_values(array_map('intval', $receiverIds));
        }

        $receiverEmails = null;
        if ($this->has('receiver_emails_text') && ! empty($this->receiver_emails_text)) {
            $receiverEmails = array_filter(
                array_map('trim', preg_split('/[\r\n,]+/', $this->receiver_emails_text)),
                fn ($email) => ! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)
            );
            $receiverEmails = array_values($receiverEmails);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'from_email' => $this->from_email ?: null,
            'from_name' => $this->from_name ?: null,
            'reply_to_email' => $this->reply_to_email ?: null,
            'reply_to_name' => $this->reply_to_name ?: null,
            'receiver_ids' => $receiverIds,
            'receiver_emails' => $receiverEmails,
        ]);
    }
}
