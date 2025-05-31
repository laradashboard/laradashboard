<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->checkAuthorization(Auth::user(), ['user.create']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ld_apply_filters('user.store.validation.rules', [
            'name' => 'required|max:50',
            'email' => 'required|max:100|email|unique:users,email',
            'username' => 'required|max:100|unique:users,username',
            'password' => 'required|min:6|confirmed',
        ]);
    }
}
