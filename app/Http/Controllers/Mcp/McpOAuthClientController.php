<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mcp\OAuth\RegisterMcpOAuthClientRequest;
use App\Services\Mcp\McpOAuthService;
use Illuminate\Http\JsonResponse;

/**
 * RFC 7591 dynamic client registration endpoint.
 */
class McpOAuthClientController extends Controller
{
    public function __construct(
        protected McpOAuthService $oauth,
    ) {
    }

    public function store(RegisterMcpOAuthClientRequest $request): JsonResponse
    {
        /** @var array<int, string> $redirectUris */
        $redirectUris = $request->validated('redirect_uris');

        $client = $this->oauth->registerClient(
            trim((string) $request->validated('client_name')) ?: __('MCP client'),
            $redirectUris,
        );

        return response()->json([
            'client_id' => $client->client_id,
            'client_id_issued_at' => $client->created_at?->getTimestamp(),
            'client_name' => $client->name,
            'redirect_uris' => $client->redirect_uris,
            'token_endpoint_auth_method' => 'none',
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
        ], 201);
    }
}
