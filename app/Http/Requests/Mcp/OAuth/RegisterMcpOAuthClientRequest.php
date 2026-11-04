<?php

declare(strict_types=1);

namespace App\Http\Requests\Mcp\OAuth;

use App\Services\Mcp\McpOAuthService;
use Closure;
use Illuminate\Contracts\Validation\Validator;

/**
 * RFC 7591 dynamic client registration for public (PKCE) MCP clients.
 *
 * Requested auth method, grant and response types are not validated: per
 * RFC 7591 §3.2.1 the server replaces them with what it supports and returns
 * the registered values. Only redirect URIs can make registration fail.
 */
class RegisterMcpOAuthClientRequest extends McpOAuthRequest
{
    protected string $oauthError = 'invalid_client_metadata';

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'client_name' => ['nullable', 'string', 'max:100'],
            'redirect_uris' => ['required', 'array', 'min:1', 'max:10'],
            'redirect_uris.*' => [
                'required',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || ! app(McpOAuthService::class)->isAllowedRedirectUri($value)) {
                        $fail(__('Redirect URIs must use HTTPS (or http://localhost) and must not contain a fragment.'));
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'redirect_uris.required' => __('At least one redirect URI is required.'),
            'redirect_uris.max' => __('No more than :max redirect URIs may be registered.'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $this->oauthError = $validator->errors()->has('redirect_uris.*') || $validator->errors()->has('redirect_uris')
            ? 'invalid_redirect_uri'
            : 'invalid_client_metadata';

        parent::failedValidation($validator);
    }
}
