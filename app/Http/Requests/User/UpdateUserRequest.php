<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->checkAuthorization(Auth::user(), ['user.edit']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return ld_apply_filters('user.update.validation.rules', [
            'name' => 'required|max:50',
            'email' => 'required|max:100|email|unique:users,email,' . $userId,
            'username' => 'required|max:100|unique:users,username,' . $userId,
            'password' => $userId ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
        ], $userId);
    }
}
