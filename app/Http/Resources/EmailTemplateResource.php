<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
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
            'subject' => $this->subject,
            'body_html' => $this->body_html,
            'type' => $this->type,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'is_deleteable' => (bool) $this->is_deleteable,
            'header_template_id' => $this->header_template_id,
            'footer_template_id' => $this->footer_template_id,
            'header_template' => $this->whenLoaded('headerTemplate', function () {
                return new EmailTemplateResource($this->headerTemplate);
            }),
            'footer_template' => $this->whenLoaded('footerTemplate', function () {
                return new EmailTemplateResource($this->footerTemplate);
            }),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
