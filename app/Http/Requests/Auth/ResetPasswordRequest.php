<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;
use App\Services\PublicFormGuardService;
use Illuminate\Validation\Rules;
use App\Models\User;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return app(PublicFormGuardService::class)->mergeRules(
            [
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', 'max:'.User::PASSWORD_MAX_LENGTH, Rules\Password::defaults()],
            ],
            includeAdvancedEmailValidation: false,
        );
    }
}
