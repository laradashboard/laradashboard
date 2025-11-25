<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class SendTestEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('manage', Setting::class);
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'email' => 'required|email',
            'type' => 'required|string',
        ];
    }
}
