<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email,' . $userId,
            'username' => 'required|string|max:100|unique:users,username,' . $userId,
            'password' => 'nullable|min:6|confirmed',
            'avatar_id' => 'nullable|exists:media,id',
        ];
    }
}
