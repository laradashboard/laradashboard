<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use App\Http\Requests\FormRequest;
use Illuminate\Support\Facades\Auth;

class BulkDeleteRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->checkAuthorization(Auth::user(), ['role.delete']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /** @example [1, 2, 3] */
            'ids' => 'required|array|min:1',

            /** @example 1 */
            'ids.*' => 'integer|exists:roles,id',
        ];
    }
}
