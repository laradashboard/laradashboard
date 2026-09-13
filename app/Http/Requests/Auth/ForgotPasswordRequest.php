<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;
use App\Services\PublicFormGuardService;

class ForgotPasswordRequest extends FormRequest
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
                'email' => ['required', 'email'],
            ],
            emailFields: ['email'],
        );
    }
}
