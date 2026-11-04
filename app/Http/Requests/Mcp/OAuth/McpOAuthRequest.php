<?php

declare(strict_types=1);

namespace App\Http\Requests\Mcp\OAuth;

use App\Exceptions\McpOAuthException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base request for machine-to-machine OAuth endpoints: validation failures
 * are reported as RFC 6749 JSON errors instead of Laravel's default format.
 */
abstract class McpOAuthRequest extends FormRequest
{
    protected string $oauthError = 'invalid_request';

    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new McpOAuthException($this->oauthError, (string) $validator->errors()->first());
    }
}
