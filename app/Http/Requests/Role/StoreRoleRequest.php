<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use App\Http\Requests\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->checkAuthorization(Auth::user(), ['role.create']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ld_apply_filters('role.store.validation.rules', [
            'name' => 'required|max:100|unique:roles,name',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|exists:permissions,name',
        ]);
    }
}
