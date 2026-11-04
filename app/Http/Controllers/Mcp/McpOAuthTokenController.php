<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mcp\OAuth\IssueMcpOAuthTokenRequest;
use App\Services\Mcp\McpOAuthService;
use Illuminate\Http\JsonResponse;

/**
 * OAuth token endpoint: exchanges authorization codes and refresh tokens
 * for MCP agent access tokens.
 */
class McpOAuthTokenController extends Controller
{
    public function __construct(
        protected McpOAuthService $oauth,
    ) {
    }

    public function store(IssueMcpOAuthTokenRequest $request): JsonResponse
    {
        $clientId = (string) $request->validated('client_id');

        $tokens = $request->validated('grant_type') === 'refresh_token'
            ? $this->oauth->refresh($clientId, (string) $request->validated('refresh_token'))
            : $this->oauth->exchangeAuthorizationCode(
                $clientId,
                (string) $request->validated('code'),
                (string) $request->validated('redirect_uri'),
                (string) $request->validated('code_verifier'),
            );

        return response()->json($tokens, 200, [
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
        ]);
    }
}
