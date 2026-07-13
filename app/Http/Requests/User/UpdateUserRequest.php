<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Hooks\UserFilterHook;
use App\Http\Requests\FormRequest;
use App\Support\Facades\Hook;
use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->can('update', User::find(request()->route('user'))) ?? false;
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

        $targetUser = User::find($userId);

        if (
            $targetUser?->hasRole(Role::SUPERADMIN)
            && ! auth()->user()?->hasRole(Role::SUPERADMIN)
        ) {
            abort(403, __('You are not allowed to modify a Superadmin user.'));
        }

        return Hook::applyFilters(UserFilterHook::USER_UPDATE_VALIDATION_RULES, [
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
        ], $userId);
    }
}
