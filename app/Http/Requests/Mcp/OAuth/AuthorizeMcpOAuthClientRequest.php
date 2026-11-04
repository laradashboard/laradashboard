<?php

declare(strict_types=1);

namespace App\Http\Requests\Mcp\OAuth;

use App\Models\McpOAuthClient;
use App\Services\Mcp\McpOAuthService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Authorization endpoint parameters (GET to show consent, POST to decide).
 *
 * Per RFC 6749 §4.1.2.1: if the client or redirect URI can't be verified the
 * error is shown to the user; any other error is redirected back to the
 * verified redirect URI so the client learns the flow failed.
 */
class AuthorizeMcpOAuthClientRequest extends FormRequest
{
    protected ?McpOAuthClient $resolvedClient = null;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'string', 'max:64'],
            'redirect_uri' => ['required', 'string', 'max:2000'],
            'response_type' => ['required', 'string', 'in:code'],
            'code_challenge' => ['required', 'string', 'regex:/^[A-Za-z0-9\-_]{43,128}$/'],
            'code_challenge_method' => ['required', 'string', 'in:S256'],
            'state' => ['nullable', 'string', 'max:1000'],
            'scope' => ['nullable', 'string', 'max:255'],
            'resource' => ['nullable', 'string', 'max:2000'],
            'decision' => ['sometimes', 'string', 'in:approve,deny'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'response_type.in' => __('Only the "code" response type is supported.'),
            'code_challenge.required' => __('A PKCE code_challenge is required.'),
            'code_challenge.regex' => __('The PKCE code_challenge is malformed.'),
            'code_challenge_method.in' => __('Only the S256 PKCE method is supported.'),
        ];
    }

    /**
     * The registered client, verified against the exact redirect URI.
     * Aborts with a user-facing 400 page when either can't be verified.
     */
    public function client(): McpOAuthClient
    {
        if ($this->resolvedClient !== null) {
            return $this->resolvedClient;
        }

        $clientId = $this->input('client_id');
        $redirectUri = $this->input('redirect_uri');

        $client = is_string($clientId) ? app(McpOAuthService::class)->findClient($clientId) : null;

        abort_if($client === null, 400, __('Unknown MCP client. Remove the connector and add it again.'));
        abort_unless(is_string($redirectUri) && $client->hasRedirectUri($redirectUri), 400, __('The redirect URI does not match this MCP client.'));

        return $this->resolvedClient = $client;
    }

    /**
     * Redirect an OAuth error back to the (verified) client.
     */
    public function errorRedirect(string $error, string $description): HttpResponseException
    {
        $this->client();

        $state = $this->input('state');

        return new HttpResponseException(redirect()->away(app(McpOAuthService::class)->redirectUrl(
            (string) $this->input('redirect_uri'),
            [
                'error' => $error,
                'error_description' => $description,
                'state' => is_string($state) ? $state : null,
            ],
        )));
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();

        abort_if($errors->hasAny(['client_id', 'redirect_uri']), 400, (string) $errors->first());

        throw $this->errorRedirect('invalid_request', (string) $errors->first());
    }
}
