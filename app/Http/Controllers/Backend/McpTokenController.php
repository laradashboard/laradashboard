<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreMcpTokenRequest;
use App\Models\Setting;
use App\Services\Mcp\McpTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class McpTokenController extends Controller
{
    public function __construct(
        protected McpTokenService $mcpTokenService
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        if (config('app.demo_mode', false)) {
            return response()->json([
                'success' => false,
                'message' => __('MCP agent tokens cannot be created in demo mode.'),
            ], 403);
        }

        if (! filter_var(config('settings.'.Setting::MCP_ENABLED, false), FILTER_VALIDATE_BOOLEAN)) {
            return response()->json([
                'success' => false,
                'message' => __('Enable MCP access in settings before creating an agent token.'),
            ], 422);
        }

        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => __('Authentication required.'),
            ], 401);
        }

        $abilities = $this->mcpTokenService->resolveAbilitiesForUser($user);

        if (! in_array('mcp:posts.read', $abilities, true) && ! in_array('mcp:posts.write', $abilities, true)) {
            return response()->json([
                'success' => false,
                'message' => __('Your account does not have any MCP-compatible permissions (post.view or post.create).'),
            ], 403);
        }

        $validated = $request->validate(
            (new StoreMcpTokenRequest())->rules(),
            (new StoreMcpTokenRequest())->messages(),
        );

        $issued = $this->mcpTokenService->issueToken($user, $validated['label']);
        $tokenRecord = $issued['token'];

        return response()->json([
            'success' => true,
            'message' => __('MCP agent token created. Copy it now — it will not be shown again.'),
            'token' => $issued['plain_text'],
            'abilities' => $abilities,
            'token_record' => [
                'id' => $tokenRecord->id,
                'label' => $this->mcpTokenService->tokenLabel($tokenRecord),
                'created_at' => $tokenRecord->created_at?->format('M j, Y g:i A'),
                'last_used_at' => __('Never'),
                'abilities' => implode(', ', $tokenRecord->abilities ?? []),
            ],
        ], 201);
    }

    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => __('Authentication required.'),
            ], 401);
        }

        $revoked = $this->mcpTokenService->revokeToken($user, $tokenId);

        if (! $revoked) {
            return response()->json([
                'success' => false,
                'message' => __('MCP token not found.'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('MCP agent token revoked.'),
        ]);
    }
}
