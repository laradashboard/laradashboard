<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class BulkDeleteRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('bulkDelete', Role::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /** @example [2] */
            'ids' => 'required|array|min:1',

            /** @example 2 */
            'ids.*' => 'integer|exists:roles,id',
        ];
    }
}
