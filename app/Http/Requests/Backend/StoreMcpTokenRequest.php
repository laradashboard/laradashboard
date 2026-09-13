<?php

declare(strict_types=1);

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreMcpTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'label.required' => __('Please enter a name for this MCP token.'),
            'label.min' => __('The token name must be at least :min characters.'),
            'label.max' => __('The token name may not exceed :max characters.'),
        ];
    }
}
