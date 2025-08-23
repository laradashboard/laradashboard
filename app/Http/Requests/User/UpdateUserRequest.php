<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;

class UpdateUserRequest extends FormRequest
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
        // Get user ID from the request parameters
        $userId = request()->route('user');

        return ld_apply_filters('user.update.validation.rules', [
            /** @example "Jane" */
            'first_name' => 'required|max:50',

            /** @example "Smith" */
            'last_name' => 'required|max:50',

            /** @example "jane.smith@example.com" */
            'email' => 'required|max:100|email|unique:users,email,' . $userId,

            /** @example "janesmith456" */
            'username' => 'required|max:100|unique:users,username,' . $userId,

            /** @example "newPassword789" */
            'password' => $userId ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',

            /** @example "123" */
            'avatar_id' => 'nullable|exists:media,id',

            /** @example [1, 2, 3] */
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|exists:roles,name',
        ], $userId);
    }
}
