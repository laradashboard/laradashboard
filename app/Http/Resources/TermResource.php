<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TermResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'taxonomy' => $this->whenLoaded('taxonomyModel', function () {
                return $this->taxonomyModel->name;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'posts_count' => $this->when(isset($this->posts_count), $this->posts_count),
            'children' => TermResource::collection($this->whenLoaded('children')),
            'parent' => new TermResource($this->whenLoaded('parent')),
        ];
    }
}
