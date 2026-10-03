<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\Settings\SensitiveSettingValue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
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
            'option_name' => $this->option_name,
            'option_value' => SensitiveSettingValue::exposeForApi(
                (string) $this->option_name,
                $this->option_value
            ),
            'autoload' => (bool) $this->autoload,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
