<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Hooks\UserFilterHook;
use App\Http\Requests\FormRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\Facades\Hook;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->can('create', User::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Hook::applyFilters(UserFilterHook::USER_STORE_VALIDATION_RULES, [
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
            'roles.*' => [
                'nullable',
                Rule::exists('roles', 'name'),
                function ($attribute, $value, $fail) {
                    if (
                        $value === Role::SUPERADMIN
                        && ! auth()->user()?->hasRole(Role::SUPERADMIN)
                    ) {
                        $fail(__('You are not allowed to assign the Superadmin role.'));
                    }
                },
            ],
        ]);
    }
}
