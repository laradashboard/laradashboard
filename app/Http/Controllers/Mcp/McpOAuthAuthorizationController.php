<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mcp\OAuth\AuthorizeMcpOAuthClientRequest;
use App\Models\User;
use App\Services\Mcp\McpOAuthService;
use App\Services\Mcp\McpTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * OAuth authorization endpoint: the signed-in user approves or denies
 * a remote MCP client (e.g. a Claude.ai custom connector).
 */
class McpOAuthAuthorizationController extends Controller
{
    public function __construct(
        protected McpOAuthService $oauth,
        protected McpTokenService $tokens,
    ) {
    }

    public function show(AuthorizeMcpOAuthClientRequest $request): Response
    {
        $client = $request->client();
        $user = $this->authorizedUser($request);

        // The consent screen must never be framed (clickjacking).
        return response()->view('mcp.oauth.authorize', [
            'client' => $client,
            'user' => $user,
            'redirectHost' => (string) parse_url((string) $request->validated('redirect_uri'), PHP_URL_HOST),
            'abilities' => $this->tokens->resolveAbilitiesForUser($user),
            'params' => $request->safe()->except('decision'),
        ])->withHeaders([
            'X-Frame-Options' => 'DENY',
            'Content-Security-Policy' => "frame-ancestors 'none'",
        ]);
    }

    public function store(AuthorizeMcpOAuthClientRequest $request): RedirectResponse
    {
        $client = $request->client();
        $user = $this->authorizedUser($request);

        if ($request->validated('decision') !== 'approve') {
            throw $request->errorRedirect('access_denied', 'The user denied the request.');
        }

        $redirectUri = (string) $request->validated('redirect_uri');

        $code = $this->oauth->issueAuthorizationCode(
            $client,
            $user,
            $redirectUri,
            (string) $request->validated('code_challenge'),
        );

        return redirect()->away($this->oauth->redirectUrl($redirectUri, [
            'code' => $code,
            'state' => $request->validated('state'),
            'iss' => url('/'),
        ]));
    }

    protected function authorizedUser(AuthorizeMcpOAuthClientRequest $request): User
    {
        /** @var User $user */
        $user = $request->user();

        $reason = $this->oauth->authorizationDenialReason($user);

        if ($reason !== null) {
            throw $request->errorRedirect('access_denied', $reason);
        }

        return $user;
    }
}
