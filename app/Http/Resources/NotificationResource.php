<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'notification_type' => $this->notification_type,
            'email_template_id' => $this->email_template_id,
            'receiver_type' => $this->receiver_type,
            'receiver_ids' => $this->receiver_ids,
            'receiver_emails' => $this->receiver_emails,
            'is_active' => (bool) $this->is_active,
            'settings' => $this->settings,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'email_template' => $this->whenLoaded('emailTemplate', function () {
                return new EmailTemplateResource($this->emailTemplate);
            }),
        ];
    }
}
