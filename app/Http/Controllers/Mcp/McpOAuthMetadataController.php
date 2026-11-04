<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Services\Mcp\McpOAuthService;
use Illuminate\Http\JsonResponse;

/**
 * OAuth discovery documents for remote MCP clients:
 * RFC 9728 (protected resource) and RFC 8414 (authorization server).
 */
class McpOAuthMetadataController extends Controller
{
    public function protectedResource(?string $path = null): JsonResponse
    {
        // Only the MCP endpoint is an OAuth-protected resource.
        abort_unless($path === null || $path === 'mcp', 404);

        return response()->json([
            'resource' => url('/mcp'),
            'authorization_servers' => [url('/')],
            'scopes_supported' => [McpOAuthService::SCOPE],
            'bearer_methods_supported' => ['header'],
        ]);
    }

    public function authorizationServer(): JsonResponse
    {
        return response()->json([
            'issuer' => url('/'),
            'authorization_endpoint' => route('mcp.oauth.authorize'),
            'token_endpoint' => route('mcp.oauth.token'),
            'registration_endpoint' => route('mcp.oauth.register'),
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'code_challenge_methods_supported' => ['S256'],
            'token_endpoint_auth_methods_supported' => ['none'],
            'scopes_supported' => [McpOAuthService::SCOPE],
            'authorization_response_iss_parameter_supported' => true,
        ]);
    }
}
