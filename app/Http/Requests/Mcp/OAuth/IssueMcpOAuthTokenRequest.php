<?php

declare(strict_types=1);

namespace App\Http\Requests\Mcp\OAuth;

use Illuminate\Contracts\Validation\Validator;

/**
 * Token endpoint: authorization_code (with PKCE) and refresh_token grants.
 */
class IssueMcpOAuthTokenRequest extends McpOAuthRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'grant_type' => ['required', 'string', 'in:authorization_code,refresh_token'],
            'client_id' => ['required', 'string', 'max:64'],
            'code' => ['required_if:grant_type,authorization_code', 'string', 'max:255'],
            'redirect_uri' => ['required_if:grant_type,authorization_code', 'string', 'max:2000'],
            'code_verifier' => ['required_if:grant_type,authorization_code', 'string', 'max:128'],
            'refresh_token' => ['required_if:grant_type,refresh_token', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'grant_type.in' => __('Only the authorization_code and refresh_token grant types are supported.'),
            'code_verifier.required_if' => __('A PKCE code_verifier is required.'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $this->oauthError = $validator->errors()->has('grant_type') && $this->filled('grant_type')
            ? 'unsupported_grant_type'
            : 'invalid_request';

        parent::failedValidation($validator);
    }
}
