<?php

declare(strict_types=1);

namespace App\Http\Requests\InboundEmailConnection;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class StoreInboundEmailConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', Setting::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'imap_host' => ['required', 'string', 'max:255'],
            'imap_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'imap_encryption' => ['required', 'string', 'in:ssl,tls,none'],
            'imap_username' => ['required', 'string', 'max:255'],
            'imap_password' => ['required', 'string', 'max:500'],
            'imap_folder' => ['nullable', 'string', 'max:255'],
            'imap_validate_cert' => ['boolean'],
            'delete_after_processing' => ['boolean'],
            'mark_as_read' => ['boolean'],
            'fetch_limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'polling_interval' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'email_connection_id' => ['nullable', 'integer', 'exists:email_connections,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Connection name is required.'),
            'imap_host.required' => __('IMAP host is required.'),
            'imap_port.required' => __('IMAP port is required.'),
            'imap_encryption.required' => __('Encryption type is required.'),
            'imap_encryption.in' => __('Encryption must be ssl, tls, or none.'),
            'imap_username.required' => __('IMAP username is required.'),
            'imap_password.required' => __('IMAP password is required.'),
            'imap_port.min' => __('IMAP port must be at least 1.'),
            'imap_port.max' => __('IMAP port must be at most 65535.'),
            'fetch_limit.min' => __('Fetch limit must be at least 1.'),
            'fetch_limit.max' => __('Fetch limit must be at most 500.'),
            'polling_interval.min' => __('Polling interval must be at least 1 minute.'),
            'polling_interval.max' => __('Polling interval must be at most 1440 minutes (24 hours).'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'imap_folder' => $this->imap_folder ?: 'INBOX',
            'imap_validate_cert' => $this->boolean('imap_validate_cert', true),
            'delete_after_processing' => $this->boolean('delete_after_processing', false),
            'mark_as_read' => $this->boolean('mark_as_read', true),
            'is_active' => $this->boolean('is_active', true),
            'fetch_limit' => $this->fetch_limit ?: 50,
            'polling_interval' => $this->polling_interval ?: 5,
        ]);
    }
}
