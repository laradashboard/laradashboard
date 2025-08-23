<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the controller using policies
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ld_apply_filters('user.store.validation.rules', [
            /** @example "John" */
            'first_name' => 'required|max:50',

            /** @example "Doe" */
            'last_name' => 'required|max:50',

            /** @example "john.doe@example.com" */
            'email' => 'required|max:100|email|unique:users,email',

            /** @example "johndoe123" */
            'username' => 'required|max:100|unique:users,username',

            'password' => 'required|min:6|confirmed',

            /** @example "123" */
            'avatar_id' => 'nullable|exists:media,id',

            /** @example [1, 2, 3] */
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|exists:roles,name',
        ]);
    }
}
